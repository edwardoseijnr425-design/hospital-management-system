/* EHMS — custom date-picker widget (old EHMS dropdown calendar design).
   The dashboard shell loads this file and calls
   window.initCustomDatePickers(root) after every SPA page is injected.

   It upgrades each native <input type="date"> inside root into the
   dropdown-calendar widget. The native input is kept in the DOM (hidden):
   its value stays in sync as YYYY-MM-DD and a "change" event is dispatched
   on selection, so existing page code that reads input.value or attaches
   onchange handlers (saUpdateFormalDate, calculateAge, ...) keeps working
   unchanged. */
(function () {
    'use strict';

    var MONTHS = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    var WEEKDAYS = ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'];
    var openPopovers = [];

    function pad2(n) { return (n < 10 ? '0' : '') + n; }

    function parseIso(value) {
        if (!value) return null;
        var m = String(value).match(/^(\d{4})-(\d{2})-(\d{2})/);
        return m ? new Date(+m[1], +m[2] - 1, +m[3]) : null;
    }

    function toIso(d) {
        return d.getFullYear() + '-' + pad2(d.getMonth() + 1) + '-' + pad2(d.getDate());
    }

    // Build the widget around one native date input.
    function buildWidget(input) {
        if (input.getAttribute('data-ehms-dp')) return;
        input.setAttribute('data-ehms-dp', '1');

        // ---- Trigger (date input look-alike) ----
        var trigger = document.createElement('div');
        trigger.className = 'calendar-input-group';
        trigger.setAttribute('role', 'button');
        trigger.setAttribute('tabindex', '0');
        trigger.setAttribute('aria-haspopup', 'dialog');
        trigger.innerHTML =
            '<svg class="calendar-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>' +
            '<span class="dp-display" data-role="display">Select date...</span>' +
            '<svg class="chevron-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>';

        // ---- Popover calendar card ----
        var popover = document.createElement('div');
        popover.className = 'calendar-popover';
        popover.setAttribute('role', 'dialog');
        popover.innerHTML =
            '<div class="calendar-header">' +
                '<button type="button" class="nav-btn" data-role="prev" title="Previous Month">' +
                    '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>' +
                '</button>' +
                '<div class="month-year-display" data-role="month-year"></div>' +
                '<button type="button" class="nav-btn" data-role="next" title="Next Month">' +
                    '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>' +
                '</button>' +
            '</div>' +
            '<div class="weekdays-grid">' + WEEKDAYS.map(function (d) { return '<span>' + d + '</span>'; }).join('') + '</div>' +
            '<div class="days-grid" data-role="days"></div>' +
            '<div class="calendar-footer">' +
                '<button type="button" class="footer-action-btn clear-btn" data-role="clear">Clear</button>' +
                '<button type="button" class="footer-action-btn today-btn" data-role="today">Today</button>' +
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

        function closePopover() {
            popover.classList.remove('active');
            window.removeEventListener('scroll', onViewportChange, true);
            window.removeEventListener('resize', onViewportChange);
            var idx = openPopovers.indexOf(popover);
            if (idx >= 0) openPopovers.splice(idx, 1);
        }

        // Expose close so the global outside-click handler cleans up fully.
        popover._ehmsClose = closePopover;

        function closeOthers() {
            for (var i = openPopovers.length - 1; i >= 0; i--) {
                if (openPopovers[i] !== popover) {
                    var other = openPopovers[i];
                    if (other._ehmsClose) other._ehmsClose();
                    else {
                        other.classList.remove('active');
                        openPopovers.splice(i, 1);
                    }
                }
            }
        }

        function positionPopover() {
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

        function onViewportChange() {
            if (popover.classList.contains('active')) positionPopover();
        }

        function openPopover() {
            closeOthers();
            renderCalendar();
            popover.classList.add('active');
            positionPopover();
            window.addEventListener('scroll', onViewportChange, true);
            window.addEventListener('resize', onViewportChange);
            if (openPopovers.indexOf(popover) < 0) openPopovers.push(popover);
        }

        function togglePopover() {
            if (popover.classList.contains('active')) {
                closePopover();
            } else {
                openPopover();
            }
        }

        function selectDay(year, month, day) {
            selectedDate = new Date(year, month, day);
            updateDisplay();
            syncInput();
            closePopover();
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
            closePopover();
        });
        popover.querySelector('[data-role="today"]').addEventListener('click', function (e) {
            e.stopPropagation();
            var t = new Date();
            selectedDate = new Date(t.getFullYear(), t.getMonth(), t.getDate());
            updateDisplay();
            syncInput();
            closePopover();
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

    window.initCustomDatePickers = function (root) {
        root = root || document;
        var inputs = root.querySelectorAll('input[type="date"]');
        for (var i = 0; i < inputs.length; i++) buildWidget(inputs[i]);
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