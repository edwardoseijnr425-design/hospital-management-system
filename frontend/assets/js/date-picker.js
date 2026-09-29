/* EHMS — custom date-picker widget (old EHMS dropdown calendar design).
   The dashboard shell loads this file and calls
   window.initCustomDatePickers(root) after every SPA page is injected.

   It upgrades each native <input type="date"> into the dropdown-calendar
   day widget, and each <input type="month"> into the month variant of the
   same widget. The native input is kept in the DOM (hidden): its value
   stays in sync as YYYY-MM-DD (or YYYY-MM) and a "change" event is
   dispatched on selection, so existing page code that reads input.value or
   attaches onchange handlers (saUpdateFormalDate, calculateAge,
   dhiLoad, ...) keeps working unchanged. */
(function () {
    'use strict';

    var MONTHS = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    var MONTHS_SHORT = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    var WEEKDAYS = ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'];
    var openPopovers = [];

    // Shared trigger/popover icons, so both modes are pixel-identical.
    var ICON_CAL = '<svg class="calendar-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>';
    var ICON_CHEV = '<svg class="chevron-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>';
    var ICON_PREV = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>';
    var ICON_NEXT = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>';

    function pad2(n) { return (n < 10 ? '0' : '') + n; }

    function parseIso(value) {
        if (!value) return null;
        var m = String(value).match(/^(\d{4})-(\d{2})-(\d{2})/);
        return m ? new Date(+m[1], +m[2] - 1, +m[3]) : null;
    }

    function toIso(d) {
        return d.getFullYear() + '-' + pad2(d.getMonth() + 1) + '-' + pad2(d.getDate());
    }

    /* A month field carries YYYY-MM. It is held as {y, m} with a 1-based
       month, matching the native input's value format exactly. */
    function parseYm(value) {
        if (!value) return null;
        var m = String(value).match(/^(\d{4})-(\d{2})$/);
        if (!m) return null;
        var mo = parseInt(m[2], 10);
        if (mo < 1 || mo > 12) return null;
        return { y: parseInt(m[1], 10), m: mo };
    }

    function toYm(sel) {
        return sel ? sel.y + '-' + pad2(sel.m) : '';
    }

    /* ---- Popover plumbing shared by both modes ---- */

    function positionPopover(trigger, popover) {
        // Fixed positioning so overflow:hidden page cards cannot clip it.
        var rect = trigger.getBoundingClientRect();
        var popWidth = popover.offsetWidth || 300;
        var popHeight = popover.offsetHeight || 320;
        var gap = 6;
        var margin = 8;
        var left = Math.max(margin, Math.min(rect.left, window.innerWidth - popWidth - margin));
        var top;
        if (rect.bottom + gap + popHeight <= window.innerHeight - margin) {
            top = rect.bottom + gap;
        } else {
            top = Math.max(margin, rect.top - popHeight - gap);
        }
        popover.style.position = 'fixed';
        popover.style.left = left + 'px';
        popover.style.top = top + 'px';
    }

    function closeOthers(self) {
        for (var i = openPopovers.length - 1; i >= 0; i--) {
            var other = openPopovers[i];
            if (other !== self) {
                if (other._ehmsClose) other._ehmsClose();
                else {
                    other.classList.remove('active');
                    openPopovers.splice(i, 1);
                }
            }
        }
    }

    /* Builds the trigger + popover shell used by both modes. The caller
       supplies the body markup and the aria/title text of the arrows. */
    function buildShell(input, popClass, bodyHtml, prevTitle, nextTitle) {
        var trigger = document.createElement('div');
        trigger.className = 'calendar-input-group';
        trigger.setAttribute('role', 'button');
        trigger.setAttribute('tabindex', '0');
        trigger.setAttribute('aria-haspopup', 'dialog');
        trigger.innerHTML = ICON_CAL
            + '<span class="dp-display" data-role="display"></span>'
            + ICON_CHEV;

        var popover = document.createElement('div');
        popover.className = 'calendar-popover' + (popClass ? ' ' + popClass : '');
        popover.setAttribute('role', 'dialog');
        popover.innerHTML =
            '<div class="calendar-header">' +
                '<button type="button" class="nav-btn" data-role="prev" title="' + prevTitle + '" aria-label="' + prevTitle + '">' + ICON_PREV + '</button>' +
                '<div class="month-year-display" data-role="month-year"></div>' +
                '<button type="button" class="nav-btn" data-role="next" title="' + nextTitle + '" aria-label="' + nextTitle + '">' + ICON_NEXT + '</button>' +
            '</div>' +
            bodyHtml +
            '<div class="calendar-footer">' +
                '<button type="button" class="footer-action-btn clear-btn" data-role="clear">Clear</button>' +
                '<button type="button" class="footer-action-btn today-btn" data-role="today"></button>' +
            '</div>';

        var wrapper = document.createElement('div');
        wrapper.className = 'calendar-wrapper';
        wrapper.appendChild(trigger);
        wrapper.appendChild(popover);

        // Keep the native input in the DOM (hidden) so form values and
        // page JS reads of input.value keep working.
        input.classList.add('dp-native-input');
        input.parentNode.insertBefore(wrapper, input);
        wrapper.appendChild(input);

        // If the page already drew its own calendar icon right before the
        // input (e.g. .date-input-wrapper in schedule/records pages), hide it.
        var sib = input.previousElementSibling;
        if (sib && sib.tagName && sib.tagName.toLowerCase() === 'svg') {
            sib.style.display = 'none';
        }

        return { wrapper: wrapper, trigger: trigger, popover: popover };
    }

    /* Open/close/reposition wiring shared by both modes. `render` is called
       every time the popover opens so the grid is current. */
    function wireShell(parts, input, render) {
        var trigger = parts.trigger;
        var popover = parts.popover;

        function onViewportChange() {
            if (popover.classList.contains('active')) positionPopover(trigger, popover);
        }

        function closePopover() {
            popover.classList.remove('active');
            window.removeEventListener('scroll', onViewportChange, true);
            window.removeEventListener('resize', onViewportChange);
            var idx = openPopovers.indexOf(popover);
            if (idx >= 0) openPopovers.splice(idx, 1);
        }

        // Expose close so the global outside-click handler cleans up fully.
        popover._ehmsClose = closePopover;

        function openPopover() {
            closeOthers(popover);
            render();
            popover.classList.add('active');
            positionPopover(trigger, popover);
            window.addEventListener('scroll', onViewportChange, true);
            window.addEventListener('resize', onViewportChange);
            if (openPopovers.indexOf(popover) < 0) openPopovers.push(popover);
        }

        function togglePopover() {
            if (popover.classList.contains('active')) closePopover();
            else openPopover();
        }

        trigger.addEventListener('click', function (e) {
            e.stopPropagation();
            togglePopover();
        });
        trigger.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                togglePopover();
            }
        });

        popover.querySelectorAll('.nav-btn, .footer-action-btn').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.stopPropagation();
            });
        });

        return { closePopover: closePopover, openPopover: openPopover, onViewportChange: onViewportChange };
    }

    // Build the day widget around one native date input.
    function buildWidget(input) {
        if (input.getAttribute('data-ehms-dp')) return;
        input.setAttribute('data-ehms-dp', '1');

        var parts = buildShell(
            input,
            '',
            '<div class="weekdays-grid">' + WEEKDAYS.map(function (d) { return '<span>' + d + '</span>'; }).join('') + '</div>'
                + '<div class="days-grid" data-role="days"></div>',
            'Previous Month',
            'Next Month'
        );
        parts.popover.querySelector('[data-role="today"]').textContent = 'Today';

        var trigger = parts.trigger;
        var popover = parts.popover;
        var displayEl = trigger.querySelector('[data-role="display"]');
        var monthYearEl = popover.querySelector('[data-role="month-year"]');
        var daysGridEl = popover.querySelector('[data-role="days"]');

        // ---- State ----
        var currentDate = new Date();
        var selectedDate = parseIso(input.value);
        currentDate = selectedDate
            ? new Date(selectedDate.getFullYear(), selectedDate.getMonth(), 1)
            : new Date();

        function updateDisplay() {
            if (selectedDate) {
                displayEl.textContent = selectedDate
                    .toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })
                    .replace(/ /g, '-');
                displayEl.classList.remove('placeholder');
            } else {
                displayEl.textContent = 'Select date...';
                displayEl.classList.add('placeholder');
            }
        }
        updateDisplay();

        function syncInput() {
            input.value = selectedDate ? toIso(selectedDate) : '';
            input.dispatchEvent(new Event('change', { bubbles: true }));
        }

        var shell = wireShell(parts, input, renderCalendar);

        function selectDay(year, month, day) {
            selectedDate = new Date(year, month, day);
            updateDisplay();
            syncInput();
            shell.closePopover();
        }

        function renderCalendar() {
            var year = currentDate.getFullYear();
            var month = currentDate.getMonth();
            monthYearEl.textContent = MONTHS[month] + ' ' + year;

            daysGridEl.innerHTML = '';

            var firstDayIndex = new Date(year, month, 1).getDay();
            var totalDays = new Date(year, month + 1, 0).getDate();
            var prevTotalDays = new Date(year, month, 0).getDate();
            var today = new Date();

            // Previous month trailing days filler
            for (var i = firstDayIndex; i > 0; i--) {
                var fill = document.createElement('div');
                fill.className = 'day-cell outside-month';
                fill.textContent = prevTotalDays - i + 1;
                daysGridEl.appendChild(fill);
            }

            // Current month active days
            for (var day = 1; day <= totalDays; day++) {
                var cell = document.createElement('div');
                cell.className = 'day-cell';
                cell.textContent = day;

                var isToday = day === today.getDate() && month === today.getMonth() && year === today.getFullYear();
                var isSelected = selectedDate && day === selectedDate.getDate() && month === selectedDate.getMonth() && year === selectedDate.getFullYear();

                if (isToday) cell.classList.add('today');
                if (isSelected) cell.classList.add('selected');

                cell.addEventListener('click', function (d) {
                    return function () { selectDay(year, month, d); };
                }(day));

                daysGridEl.appendChild(cell);
            }

            // Next month leading days filler to balance rows (35 or 42 cells)
            var totalCellsRendered = firstDayIndex + totalDays;
            var nextDaysCount = totalCellsRendered <= 35 ? 35 - totalCellsRendered : 42 - totalCellsRendered;

            for (var j = 1; j <= nextDaysCount; j++) {
                var fillNext = document.createElement('div');
                fillNext.className = 'day-cell outside-month';
                fillNext.textContent = j;
                daysGridEl.appendChild(fillNext);
            }
        }

        // ---- Events ----
        popover.querySelector('[data-role="prev"]').addEventListener('click', function (e) {
            e.stopPropagation();
            currentDate.setMonth(currentDate.getMonth() - 1);
            renderCalendar();
        });
        popover.querySelector('[data-role="next"]').addEventListener('click', function (e) {
            e.stopPropagation();
            currentDate.setMonth(currentDate.getMonth() + 1);
            renderCalendar();
        });
        popover.querySelector('[data-role="clear"]').addEventListener('click', function (e) {
            e.stopPropagation();
            selectedDate = null;
            updateDisplay();
            syncInput();
            shell.closePopover();
        });
        popover.querySelector('[data-role="today"]').addEventListener('click', function (e) {
            e.stopPropagation();
            var t = new Date();
            selectedDate = new Date(t.getFullYear(), t.getMonth(), t.getDate());
            updateDisplay();
            syncInput();
            shell.closePopover();
        });

        // Keep the widget in sync when page JS sets the native input directly.
        input.addEventListener('change', function () {
            var parsed = parseIso(input.value);
            if (parsed && (!selectedDate || selectedDate.getTime() !== parsed.getTime())) {
                selectedDate = parsed;
                currentDate = new Date(parsed.getFullYear(), parsed.getMonth(), 1);
                updateDisplay();
            } else if (!parsed) {
                selectedDate = null;
                updateDisplay();
            }
        });
    }

    /* Build the month widget around one native month input.
       A month is the smallest selectable unit here, so the header arrows step
       a whole year and the grid lists the 12 months of that year. */
    function buildMonthWidget(input) {
        if (input.getAttribute('data-ehms-dp')) return;
        input.setAttribute('data-ehms-dp', '1');

        // A month that has not happened yet cannot hold records, so it is
        // disabled unless the field explicitly opts in with data-dp-allow-future
        // (a scheduling field legitimately needs to look ahead).
        var allowFuture = input.hasAttribute('data-dp-allow-future');

        var parts = buildShell(
            input,
            'month-mode',
            '<div class="months-grid" data-role="months"></div>',
            'Previous year',
            'Next year'
        );
        parts.popover.querySelector('[data-role="today"]').textContent = 'This Month';

        var trigger = parts.trigger;
        var popover = parts.popover;
        var displayEl = trigger.querySelector('[data-role="display"]');
        var monthYearEl = popover.querySelector('[data-role="month-year"]');
        var monthsGridEl = popover.querySelector('[data-role="months"]');

        // ---- State ----
        var selected = parseYm(input.value);
        var today = new Date();
        var viewYear = selected ? selected.y : today.getFullYear();

        function updateDisplay() {
            if (selected) {
                displayEl.textContent = MONTHS[selected.m - 1] + ' ' + selected.y;
                displayEl.classList.remove('placeholder');
            } else {
                displayEl.textContent = 'Select month...';
                displayEl.classList.add('placeholder');
            }
        }
        updateDisplay();

        function syncInput() {
            input.value = toYm(selected);
            input.dispatchEvent(new Event('change', { bubbles: true }));
        }

        var shell = wireShell(parts, input, renderMonths);

        function selectMonth(year, month) {
            selected = { y: year, m: month };
            viewYear = year;
            updateDisplay();
            syncInput();
            shell.closePopover();
        }

        function renderMonths() {
            monthYearEl.textContent = viewYear;
            monthsGridEl.innerHTML = '';

            var nowY = today.getFullYear();
            var nowM = today.getMonth() + 1;

            for (var m = 1; m <= 12; m++) {
                var cell = document.createElement('button');
                cell.type = 'button';
                cell.className = 'month-cell';
                cell.textContent = MONTHS_SHORT[m - 1];

                // Future months hold no records, so they are not offered.
                var future = !allowFuture && (viewYear > nowY || (viewYear === nowY && m > nowM));
                if (future) {
                    cell.classList.add('disabled');
                    cell.disabled = true;
                    cell.title = 'This month has not happened yet';
                } else {
                    cell.addEventListener('click', function (y, mm) {
                        return function () { selectMonth(y, mm); };
                    }(viewYear, m));
                }

                if (viewYear === nowY && m === nowM) cell.classList.add('today');
                if (selected && selected.y === viewYear && selected.m === m) cell.classList.add('selected');

                monthsGridEl.appendChild(cell);
            }
        }

        // ---- Events ----
        popover.querySelector('[data-role="prev"]').addEventListener('click', function (e) {
            e.stopPropagation();
            viewYear -= 1;
            renderMonths();
        });
        popover.querySelector('[data-role="next"]').addEventListener('click', function (e) {
            e.stopPropagation();
            viewYear += 1;
            renderMonths();
        });
        popover.querySelector('[data-role="clear"]').addEventListener('click', function (e) {
            e.stopPropagation();
            selected = null;
            viewYear = today.getFullYear();
            updateDisplay();
            syncInput();
            shell.closePopover();
        });
        popover.querySelector('[data-role="today"]').addEventListener('click', function (e) {
            e.stopPropagation();
            selected = { y: today.getFullYear(), m: today.getMonth() + 1 };
            viewYear = selected.y;
            updateDisplay();
            syncInput();
            shell.closePopover();
        });

        // Keep the widget in sync when page JS sets the native input directly
        // (dhiInit() assigns the default reporting month this way).
        input.addEventListener('change', function () {
            var parsed = parseYm(input.value);
            if (parsed) {
                if (!selected || selected.y !== parsed.y || selected.m !== parsed.m) {
                    selected = parsed;
                    viewYear = parsed.y;
                    updateDisplay();
                }
            } else if (selected) {
                selected = null;
                updateDisplay();
            }
        });
    }

    // Close any open popover when clicking outside its wrapper.
    document.addEventListener('click', function (e) {
        for (var i = openPopovers.length - 1; i >= 0; i--) {
            var pop = openPopovers[i];
            var wrapper = pop.closest ? pop.closest('.calendar-wrapper') : null;
            if (!wrapper || !wrapper.contains(e.target)) {
                if (pop._ehmsClose) pop._ehmsClose();
                else {
                    pop.classList.remove('active');
                    openPopovers.splice(i, 1);
                }
            }
        }
    });

    // Escape closes the topmost open popover.
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' || e.key === 'Esc') {
            var last = openPopovers[openPopovers.length - 1];
            if (last) {
                if (last._ehmsClose) last._ehmsClose();
                else {
                    last.classList.remove('active');
                    openPopovers.pop();
                }
            }
        }
    });

    window.initCustomDatePickers = function (root) {
        root = root || document;
        var dateInputs = root.querySelectorAll('input[type="date"]');
        for (var i = 0; i < dateInputs.length; i++) buildWidget(dateInputs[i]);
        var monthInputs = root.querySelectorAll('input[type="month"]');
        for (var j = 0; j < monthInputs.length; j++) buildMonthWidget(monthInputs[j]);
        return root;
    };

    // Auto-init for pages that include this file outside the SPA shell
    // (the shell also calls initCustomDatePickers(pageContent) after each
    // page load, so already-upgraded inputs are skipped via data-ehms-dp).
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            window.initCustomDatePickers(document);
        });
    } else {
        window.initCustomDatePickers(document);
    }
})();
