/* EHMS — shared import/export toolkit.
 *
 * The dashboard shell loads this file and calls window.initDataToolkits(root)
 * after every SPA page is injected. It adds a compact Export toolbar (CSV,
 * Excel, PDF, JSON, Print) above every data table plus one Import button, and
 * provides the bulk-import modal backed by backend/api/import.php.
 *
 * Exports read the rendered table, so they always match exactly what is on
 * screen, for every table, with no per-page wiring. Anything a page wants to
 * keep out of an export is marked with data-dt-ignore (header cell or cell);
 * a cell can report a raw value instead of its display text with
 * data-dt-value, and a table can supply a title with data-dt-title or column
 * names with data-dt-columns="A,B,C".
 */
(function () {
    'use strict';

    var API = '/hms/backend/api/import.php';
    var ORG = 'Eddie Healthcare Solutions · EHMS';
    var MAX_ROWS = 5000;

    /* ------------------------------------------------------------------ */
    /* Small helpers                                                       */
    /* ------------------------------------------------------------------ */

    function escHtml(v) {
        return String(v == null ? '' : v)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function slug(v) {
        return String(v || 'export').trim().toLowerCase()
            .replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '').slice(0, 60) || 'export';
    }

    function humanize(v) {
        return String(v || '').replace(/_/g, ' ').replace(/\b\w/g, function (c) { return c.toUpperCase(); });
    }

    function norm(v) {
        return String(v == null ? '' : v).toLowerCase().replace(/[^a-z0-9]+/g, '');
    }

    function notify(message, isError) {
        if (typeof window.showAlert === 'function') {
            window.showAlert(message, isError ? 'error' : 'success');
        } else {
            window.alert(message);
        }
    }

    function download(filename, contents, mime) {
        var blob = new Blob([contents], { type: mime });
        var url = URL.createObjectURL(blob);
        var a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        setTimeout(function () { URL.revokeObjectURL(url); }, 1000);
    }

    /* ------------------------------------------------------------------ */
    /* Read a rendered table into { title, headers[], rows[][] }           */
    /* ------------------------------------------------------------------ */

    function isControlOnly(cell) {
        if (!cell) return false;
        if (!cell.querySelector('button,input,select,textarea')) return false;
        return (cell.textContent || '').replace(/\s+/g, '').length === 0;
    }

    function cellValue(cell) {
        if (!cell) return '';
        var raw = cell.getAttribute('data-dt-value');
        if (raw !== null) return raw;
        var clone = cell.cloneNode(true);
        clone.querySelectorAll('button,input,select,textarea,svg,script,.dt-noexport,[data-dt-ignore]')
            .forEach(function (n) { n.remove(); });
        return (clone.textContent || '').replace(/\s+/g, ' ').trim();
    }

    function isIgnoredHeader(cell) {
        if (!cell) return true;
        if (cell.hasAttribute('data-dt-ignore')) return true;
        return /^(actions?|operations?|controls?|manage|options?)$/i.test((cell.textContent || '').trim());
    }

    function isPlaceholderRow(row) {
        if (!row.cells || row.cells.length === 0) return true;
        if (row.cells.length === 1 && row.cells[0].colSpan > 1) return true;
        return false;
    }

    function readTable(table) {
        if (typeof table === 'string') table = document.querySelector(table);
        if (!table) return null;

        var headerCells = null;
        var bodyRows = [];

        if (table.tHead && table.tHead.rows.length) {
            headerCells = Array.prototype.slice.call(table.tHead.rows[table.tHead.rows.length - 1].cells);
            Array.prototype.forEach.call(table.tBodies, function (tb) {
                Array.prototype.forEach.call(tb.rows, function (r) { bodyRows.push(r); });
            });
        } else {
            var all = Array.prototype.slice.call(table.rows);
            if (all.length) {
                var first = all[0];
                var allTh = first.cells.length > 0 && Array.prototype.every.call(first.cells, function (c) {
                    return c.tagName === 'TH';
                });
                if (allTh) { headerCells = Array.prototype.slice.call(first.cells); all.shift(); }
            }
            bodyRows = all;
        }

        bodyRows = bodyRows.filter(function (r) { return !isPlaceholderRow(r); });

        var width = headerCells ? headerCells.length : (bodyRows[0] ? bodyRows[0].cells.length : 0);
        var columns = [];
        for (var c = 0; c < width; c++) {
            if (headerCells && isIgnoredHeader(headerCells[c])) continue;
            columns.push(c);
        }

        // Headerless table: drop a trailing column that is only row controls.
        if (!headerCells && columns.length && bodyRows.length) {
            var last = columns[columns.length - 1];
            var onlyButtons = bodyRows.every(function (r) { return isControlOnly(r.cells[last]); });
            if (onlyButtons) {
                columns = columns.filter(function (ci) { return ci !== last; });
            }
        }

        var declared = table.getAttribute('data-dt-columns');
        var declaredNames = declared ? declared.split(',') : null;

        var headers = columns.map(function (ci, i) {
            if (headerCells) return cellValue(headerCells[ci]) || ('Column ' + (ci + 1));
            if (declaredNames && declaredNames[i]) return declaredNames[i].trim();
            return 'Column ' + (ci + 1);
        });

        var rows = bodyRows.map(function (r) {
            return columns.map(function (ci) { return cellValue(r.cells[ci]); });
        });

        return { title: tableTitle(table), headers: headers, rows: rows };
    }

    function tableTitle(table) {
        var t = table.getAttribute('data-dt-title');
        if (t) return t;
        var scopes = [
            table.closest('.table-container'),
            table.closest('section'),
            table.closest('.card'),
            document
        ];
        for (var s = 0; s < scopes.length; s++) {
            var scope = scopes[s];
            if (!scope) continue;
            var headings = scope.querySelectorAll('h1,h2,h3,h4,.card-title,.table-title,.section-title');
            var best = null;
            for (var i = 0; i < headings.length; i++) {
                // Heading precedes the table in document order.
                if (table.compareDocumentPosition(headings[i]) & Node.DOCUMENT_POSITION_PRECEDING) {
                    best = headings[i];
                }
            }
            if (best && best.textContent.trim()) return best.textContent.trim();
        }
        return (document.title || 'Export').replace(/\s*[-|].*$/, '').trim() || 'Export';
    }

    /* ------------------------------------------------------------------ */
    /* Formatters                                                          */
    /* ------------------------------------------------------------------ */

    function toCSV(data) {
        function cell(v) {
            var s = v == null ? '' : String(v);
            return /[",\r\n]/.test(s) ? '"' + s.replace(/"/g, '""') + '"' : s;
        }
        var lines = [data.headers.map(cell).join(',')];
        data.rows.forEach(function (r) { lines.push(r.map(cell).join(',')); });
        return '\uFEFF' + lines.join('\r\n');
    }

    function toObjects(data) {
        var seen = {};
        var keys = data.headers.map(function (h, i) {
            var key = String(h || ('Column ' + (i + 1))).trim() || ('Column ' + (i + 1));
            if (seen[key]) { seen[key]++; key = key + ' (' + seen[key] + ')'; } else { seen[key] = 1; }
            return key;
        });
        return data.rows.map(function (r) {
            var o = {};
            keys.forEach(function (k, i) { o[k] = r[i] == null ? '' : r[i]; });
            return o;
        });
    }

    function toSpreadsheetML(title, data) {
        function esc(v) {
            return String(v == null ? '' : v)
                .replace(/&/g, '&amp;').replace(/</g, '&lt;')
                .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }
        function cell(v) {
            var isNum = v !== '' && v != null && /^-?\d+(\.\d+)?$/.test(String(v).trim());
            return '<Cell><Data ss:Type="' + (isNum ? 'Number' : 'String') + '">' + esc(v) + '</Data></Cell>';
        }
        var rows = ['<Row>' + data.headers.map(cell).join('') + '</Row>'];
        data.rows.forEach(function (r) { rows.push('<Row>' + r.map(cell).join('') + '</Row>'); });
        var sheet = (title || 'Data').replace(/[\\\/\?\*\[\]:]/g, ' ').slice(0, 31) || 'Data';
        return '<?xml version="1.0"?>\n<?mso-application progid="Excel.Sheet"?>\n'
            + '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" '
            + 'xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">'
            + '<Worksheet ss:Name="' + esc(sheet) + '"><Table>'
            + rows.join('') + '</Table></Worksheet></Workbook>';
    }

    function printTable(title, data, asPdf) {
        var w = window.open('', '_blank', 'width=1000,height=720');
        if (!w) { notify('Pop-up blocked — allow pop-ups to export a PDF', true); return; }
        var landscape = data.headers.length > 6;
        var head = data.headers.map(function (h) { return '<th>' + escHtml(h) + '</th>'; }).join('');
        var body = data.rows.map(function (r) {
            return '<tr>' + r.map(function (v) { return '<td>' + escHtml(v) + '</td>'; }).join('') + '</tr>';
        }).join('');
        w.document.write('<!DOCTYPE html><html><head><meta charset="utf-8"><title>' + escHtml(title) + '</title><style>'
            + '@page{size:A4 ' + (landscape ? 'landscape' : 'portrait') + ';margin:12mm}'
            + 'body{font-family:"Segoe UI",Tahoma,sans-serif;color:#0f172a;margin:0}'
            + 'h1{font-size:16px;margin:0 0 3px}'
            + '.meta{font-size:11px;color:#64748b;margin-bottom:12px}'
            + 'table{border-collapse:collapse;width:100%;font-size:10.5px}'
            + 'th,td{border:1px solid #cbd5e1;padding:4px 6px;text-align:left}'
            + 'th{background:#eef2f7}thead{display:table-header-group}'
            + '</style></head><body>'
            + '<h1>' + escHtml(title) + '</h1>'
            + '<div class="meta">' + escHtml(ORG) + ' &middot; generated ' + escHtml(new Date().toLocaleString())
            + ' &middot; ' + data.rows.length + ' row(s)'
            + (asPdf ? ' &middot; choose &ldquo;Save as PDF&rdquo; in the print dialog' : '')
            + '</div>'
            + '<table><thead><tr>' + head + '</tr></thead><tbody>' + body + '</tbody></table>'
            + '<script>window.onload=function(){window.print();};<\/script></body></html>');
        w.document.close();
    }

    function exportTable(table, format) {
        var data = readTable(table);
        if (!data) return;
        if (!data.rows.length && !data.headers.length) {
            notify('Nothing to export', true);
            return;
        }
        var base = slug(data.title) + '_' + new Date().toISOString().slice(0, 10);
        if (format === 'csv') {
            download(base + '.csv', toCSV(data), 'text/csv;charset=utf-8;');
        } else if (format === 'xls') {
            download(base + '.xls', toSpreadsheetML(data.title, data), 'application/vnd.ms-excel;charset=utf-8;');
        } else if (format === 'json') {
            download(base + '.json', JSON.stringify(toObjects(data), null, 2), 'application/json;charset=utf-8;');
        } else {
            printTable(data.title, data, format === 'pdf');
            return;
        }
        notify('Exported "' + data.title + '" as ' + format.toUpperCase(), false);
    }

    /* ------------------------------------------------------------------ */
    /* Toolbar auto-attach                                                 */
    /* ------------------------------------------------------------------ */

    var FORMATS = [
        { f: 'csv', t: 'CSV' },
        { f: 'xls', t: 'Excel' },
        { f: 'pdf', t: 'PDF' },
        { f: 'json', t: 'JSON' },
        { f: 'print', t: 'Print' }
    ];

    function shouldAttach(table) {
        if (table.hasAttribute('data-ehms-dt')) return false;
        if (table.classList.contains('kv')) return false;
        if (table.hasAttribute('data-dt-ignore')) return false;
        if (table.closest('.dt-overlay, .calendar-popover, .modal, [role="dialog"]')) return false;
        if (!table.querySelector('thead, tbody, tr')) return false;
        return true;
    }

    function attachToolbar(table, isFirst) {
        var host = table.closest('.table-container') || table;
        if (!host || !host.parentNode) return;

        var bar = document.createElement('div');
        bar.className = 'dt-toolbar';

        var label = document.createElement('span');
        label.className = 'dt-toolbar-label';
        label.textContent = 'Export';
        bar.appendChild(label);

        FORMATS.forEach(function (fmt) {
            var b = document.createElement('button');
            b.type = 'button';
            b.className = 'dt-btn';
            b.textContent = fmt.t;
            b.addEventListener('click', function (e) { e.preventDefault(); exportTable(table, fmt.f); });
            bar.appendChild(b);
        });

        if (isFirst) {
            var imp = document.createElement('button');
            imp.type = 'button';
            imp.className = 'dt-btn dt-import';
            imp.textContent = 'Import';
            imp.addEventListener('click', function (e) { e.preventDefault(); openImport(); });
            bar.appendChild(imp);
        }

        host.parentNode.insertBefore(bar, host);
    }

    window.initDataToolkits = function (root) {
        root = root || document;
        var tables = root.querySelectorAll('table');
        var first = true;
        for (var i = 0; i < tables.length; i++) {
            var table = tables[i];
            if (!shouldAttach(table)) continue;
            table.setAttribute('data-ehms-dt', '1');
            attachToolbar(table, first);
            first = false;
        }
        return root;
    };

    /* ------------------------------------------------------------------ */
    /* Import modal                                                        */
    /* ------------------------------------------------------------------ */

    var schemaCache = null;

    function loadSchema() {
        if (schemaCache) return Promise.resolve(schemaCache);
        return fetch(API + '?action=schema', { credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (j) {
                if (!j.success) throw new Error(j.error || 'Could not load the dataset list');
                schemaCache = j.datasets;
                return schemaCache;
            });
    }

    function parseDelimited(text) {
        var sample = (text.split(/\r?\n/).find(function (l) { return l.trim() !== ''; }) || '');
        var delim = ',', best = 0;
        [',', ';', '\t', '|'].forEach(function (d) {
            var n = sample.split(d).length;
            if (n > best) { best = n; delim = d; }
        });
        var rows = [], row = [], field = '', inQuotes = false;
        for (var i = 0; i < text.length; i++) {
            var ch = text[i];
            if (inQuotes) {
                if (ch === '"') {
                    if (text[i + 1] === '"') { field += '"'; i++; } else { inQuotes = false; }
                } else { field += ch; }
            } else if (ch === '"') {
                inQuotes = true;
            } else if (ch === delim) {
                row.push(field); field = '';
            } else if (ch === '\n') {
                row.push(field); rows.push(row); row = []; field = '';
            } else if (ch === '\r') {
                /* skip */
            } else {
                field += ch;
            }
        }
        if (field !== '' || row.length) { row.push(field); rows.push(row); }
        return rows.filter(function (r) { return r.some(function (c) { return String(c).trim() !== ''; }); });
    }

    function parseSpreadsheetML(text) {
        var doc = new DOMParser().parseFromString(text, 'application/xml');
        if (doc.querySelector('parsererror')) throw new Error('Not a valid Excel XML file');
        var rowEls = doc.getElementsByTagName('Row');
        var grid = [];
        for (var i = 0; i < rowEls.length; i++) {
            var cells = rowEls[i].getElementsByTagName('Cell');
            var row = [];
            for (var j = 0; j < cells.length; j++) {
                var idx = cells[j].getAttribute('ss:Index');
                if (idx) { while (row.length < parseInt(idx, 10) - 1) row.push(''); }
                var dataEl = cells[j].getElementsByTagName('Data')[0];
                row.push(dataEl ? dataEl.textContent : '');
            }
            grid.push(row);
        }
        return grid.filter(function (r) { return r.some(function (c) { return String(c).trim() !== ''; }); });
    }

    function jsonToGrid(text) {
        var parsed = JSON.parse(text);
        if (!Array.isArray(parsed)) throw new Error('JSON must be an array of rows or objects');
        if (!parsed.length) return [];
        if (typeof parsed[0] === 'object' && parsed[0] !== null && !Array.isArray(parsed[0])) {
            var keys = [];
            parsed.forEach(function (o) {
                Object.keys(o).forEach(function (k) { if (keys.indexOf(k) < 0) keys.push(k); });
            });
            var grid = [keys];
            parsed.forEach(function (o) {
                grid.push(keys.map(function (k) { return o[k] == null ? '' : String(o[k]); }));
            });
            return grid;
        }
        return parsed.map(function (r) { return r.map(function (c) { return c == null ? '' : String(c); }); });
    }

    function parseFile(name, text) {
        if (/\.json$/i.test(name)) return jsonToGrid(text);
        if (/\.xlsx$/i.test(name)) {
            throw new Error('.xlsx is not supported — in Excel/Sheets choose Save as CSV, or export the .xls from this system.');
        }
        if (/\.(xls|xml)$/i.test(name)) {
            try { return parseSpreadsheetML(text); }
            catch (e) { return parseDelimited(text); }
        }
        return parseDelimited(text);
    }

    function buildGrid(raw, hasHeader) {
        if (!raw.length) return { headers: [], rows: [] };
        var headers, rows;
        if (hasHeader) {
            headers = raw[0].map(function (h, i) {
                var v = String(h == null ? '' : h).trim();
                return v || ('Column ' + (i + 1));
            });
            rows = raw.slice(1);
        } else {
            var width = raw.reduce(function (m, r) { return Math.max(m, r.length); }, 0);
            headers = [];
            for (var i = 0; i < width; i++) headers.push('Column ' + (i + 1));
            rows = raw;
        }
        return { headers: headers, rows: rows };
    }

    function autoMap(headers, cols, keyField) {
        var used = {};
        return headers.map(function (h) {
            var nh = norm(h);
            var hit = null;
            if (keyField && norm(keyField) === nh) hit = cols.find(function (c) { return c.name === keyField; });
            if (!hit) hit = cols.find(function (c) { return norm(c.name) === nh && !used[c.name]; });
            if (!hit) hit = cols.find(function (c) { return norm(humanize(c.name)) === nh && !used[c.name]; });
            if (!hit) hit = cols.find(function (c) {
                if (used[c.name]) return false;
                var nc = norm(humanize(c.name));
                return nc && nh && (nc.indexOf(nh) === 0 || nh.indexOf(nc) === 0);
            });
            if (hit) { used[hit.name] = true; return hit.name; }
            return '';
        });
    }

    function openImport(preselect) {
        loadSchema().then(function (datasets) {
            buildImportModal(datasets, preselect);
        }).catch(function (e) {
            notify('Import unavailable: ' + e.message, true);
        });
    }

    function buildImportModal(datasets, preselect) {
        var state = {
            datasets: datasets,
            datasetKey: preselect && datasets.some(function (d) { return d.key === preselect; }) ? preselect : datasets[0].key,
            raw: null,
            grid: null
        };

        var overlay = document.createElement('div');
        overlay.className = 'dt-overlay';
        overlay.innerHTML =
            '<div class="dt-modal" role="dialog" aria-modal="true" aria-label="Import data">' +
                '<div class="dt-modal-head"><h3>Import data</h3>' +
                    '<button class="dt-close" type="button" aria-label="Close">&times;</button></div>' +
                '<div class="dt-modal-body">' +
                    '<div class="dt-row">' +
                        '<div class="dt-field"><label for="dt-dataset">Dataset</label><select id="dt-dataset"></select></div>' +
                        '<div class="dt-field"><label for="dt-mode">Mode</label>' +
                            '<select id="dt-mode"><option value="insert">Insert new rows</option>' +
                            '<option value="upsert">Insert or update existing</option></select></div>' +
                    '</div>' +
                    '<p class="dt-hint" id="dt-keyhint"></p>' +
                    '<div class="dt-field"><label for="dt-file">Data file</label>' +
                        '<input type="file" id="dt-file" accept=".csv,.tsv,.txt,.json,.xls,.xml"></div>' +
                    '<p class="dt-hint">CSV, TSV, JSON, or an .xls saved by this system. ' +
                        'For Excel/Sheets .xlsx, download it as CSV first.</p>' +
                    '<label class="dt-check"><input type="checkbox" id="dt-header" checked> ' +
                        'The first row holds column names</label>' +
                    '<div id="dt-preview"></div>' +
                    '<div id="dt-result"></div>' +
                '</div>' +
                '<div class="dt-modal-foot">' +
                    '<span class="dt-foot-note" id="dt-foot-note"></span>' +
                    '<div><button class="dt-btn" id="dt-export" type="button">Download current data</button>' +
                    '<button class="dt-btn" id="dt-cancel" type="button">Cancel</button>' +
                    '<button class="dt-btn dt-import" id="dt-run" type="button" disabled>Import</button></div>' +
                '</div>' +
            '</div>';
        document.body.appendChild(overlay);

        var $ = function (id) { return overlay.querySelector('#' + id); };
        var datasetSel = $('dt-dataset');
        var modeSel = $('dt-mode');
        var keyHint = $('dt-keyhint');
        var fileInput = $('dt-file');
        var headerCheck = $('dt-header');
        var preview = $('dt-preview');
        var result = $('dt-result');
        var footNote = $('dt-foot-note');
        var runBtn = $('dt-run');

        function dataset() {
            return state.datasets.find(function (d) { return d.key === state.datasetKey; });
        }

        datasets.forEach(function (d) {
            var o = document.createElement('option');
            o.value = d.key;
            o.textContent = d.label;
            datasetSel.appendChild(o);
        });
        datasetSel.value = state.datasetKey;

        function close() {
            document.removeEventListener('keydown', onKey);
            overlay.remove();
        }
        function onKey(e) { if (e.key === 'Escape') close(); }

        overlay.querySelector('.dt-close').addEventListener('click', close);
        $('dt-cancel').addEventListener('click', close);
        $('dt-export').addEventListener('click', function (e) {
            e.preventDefault();
            // Streams the current contents of the selected dataset, including
            // datasets that have no rendered table on a page.
            window.location.href = API + '?action=export&dataset=' + encodeURIComponent(state.datasetKey);
        });
        overlay.addEventListener('click', function (e) { if (e.target === overlay) close(); });
        document.addEventListener('keydown', onKey);

        function updateHint() {
            var d = dataset();
            keyHint.textContent = d.key_field
                ? 'Upsert matches rows on "' + humanize(d.key_field) + '".'
                : 'This dataset has no unique key, so rows are always inserted.';
            if (!d.key_field) modeSel.value = 'insert';
            modeSel.querySelector('option[value="upsert"]').disabled = !d.key_field;
        }

        function renderPreview() {
            preview.innerHTML = '';
            result.innerHTML = '';
            runBtn.disabled = true;
            if (!state.grid || !state.grid.rows.length) {
                footNote.textContent = '';
                if (state.grid && !state.grid.rows.length) {
                    preview.innerHTML = '<p class="dt-hint">No data rows found in the file.</p>';
                }
                return;
            }
            var d = dataset();
            state.mapping = autoMap(state.grid.headers, d.columns, d.key_field);

            var head = state.grid.headers.map(function (h) { return '<th>' + escHtml(h) + '</th>'; }).join('');
            var sample = state.grid.rows.slice(0, 8).map(function (r) {
                return '<tr>' + state.grid.headers.map(function (_, i) {
                    return '<td>' + escHtml(r[i] == null ? '' : r[i]) + '</td>';
                }).join('') + '</tr>';
            }).join('');
            var more = state.grid.rows.length > 8
                ? '<p class="dt-hint" style="margin:6px 0 0">Showing 8 of ' + state.grid.rows.length + ' rows.</p>'
                : '';

            preview.innerHTML = '<div class="dt-preview-wrap"><table class="dt-preview"><thead><tr>' + head +
                '</tr></thead><tbody>' + sample + '</tbody></table></div>' + more +
                '<div class="dt-map-grid" id="dt-map"></div>';

            var mapHost = preview.querySelector('#dt-map');
            state.grid.headers.forEach(function (h, i) {
                var item = document.createElement('div');
                item.className = 'dt-map-item';

                var src = document.createElement('span');
                src.className = 'dt-map-src';
                src.textContent = h;
                src.title = h;
                item.appendChild(src);

                var sel = document.createElement('select');
                var skip = document.createElement('option');
                skip.value = '';
                skip.textContent = '— skip —';
                sel.appendChild(skip);
                d.columns.forEach(function (col) {
                    var o = document.createElement('option');
                    o.value = col.name;
                    o.textContent = humanize(col.name) + (col.required ? ' *' : '');
                    sel.appendChild(o);
                });
                sel.value = state.mapping[i] || '';
                sel.className = sel.value ? '' : 'dt-unmapped';
                sel.addEventListener('change', function () {
                    state.mapping[i] = sel.value;
                    sel.className = sel.value ? '' : 'dt-unmapped';
                });
                item.appendChild(sel);
                mapHost.appendChild(item);
            });

            var mapped = state.mapping.filter(Boolean).length;
            footNote.textContent = mapped + ' of ' + state.grid.headers.length + ' columns mapped';
            runBtn.disabled = mapped === 0;
        }

        function parseInto(rawText, name) {
            result.innerHTML = '';
            try {
                state.raw = rawText;
                state.grid = buildGrid(parseFile(name, rawText), headerCheck.checked);
                renderPreview();
            } catch (e) {
                state.grid = null;
                preview.innerHTML = '';
                runBtn.disabled = true;
                result.innerHTML = '<div class="dt-result err">Could not read the file: ' + escHtml(e.message) + '</div>';
            }
        }

        fileInput.addEventListener('change', function () {
            var file = fileInput.files && fileInput.files[0];
            if (!file) return;
            var reader = new FileReader();
            reader.onload = function () { parseInto(String(reader.result), file.name); };
            reader.readAsText(file);
        });

        headerCheck.addEventListener('change', function () {
            if (state.raw != null && fileInput.files[0]) parseInto(state.raw, fileInput.files[0].name);
        });

        datasetSel.addEventListener('change', function () {
            state.datasetKey = datasetSel.value;
            updateHint();
            renderPreview();
        });

        runBtn.addEventListener('click', function () {
            var d = dataset();
            var rows = [];
            state.grid.rows.forEach(function (r) {
                var obj = {};
                state.grid.headers.forEach(function (h, i) {
                    var col = state.mapping[i];
                    if (!col) return;
                    var v = r[i];
                    if (v === undefined || v === null || String(v).trim() === '') return;
                    obj[col] = String(v).trim();
                });
                if (Object.keys(obj).length) rows.push(obj);
            });
            if (!rows.length) {
                result.innerHTML = '<div class="dt-result err">No rows to import after mapping.</div>';
                return;
            }
            if (rows.length > MAX_ROWS) {
                result.innerHTML = '<div class="dt-result err">' + rows.length +
                    ' rows exceed the ' + MAX_ROWS + '-row limit. Split the file.</div>';
                return;
            }

            runBtn.disabled = true;
            runBtn.innerHTML = '<span class="dt-spin"></span> Importing…';
            result.innerHTML = '';

            fetch(API + '?action=import', {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ dataset: state.datasetKey, mode: modeSel.value, rows: rows })
            }).then(function (r) {
                return r.json().then(function (j) { return { ok: r.ok, body: j }; });
            }).then(function (res) {
                runBtn.disabled = false;
                runBtn.textContent = 'Import';
                var body = res.body || {};
                if (!res.ok || !body.success) {
                    result.innerHTML = '<div class="dt-result err">Import failed: ' +
                        escHtml(body.error || 'Unexpected server response') + '</div>';
                    return;
                }
                var html = '<div class="dt-result ok"><strong>Import complete.</strong> ' +
                    body.inserted + ' inserted, ' + body.updated + ' updated, ' + body.skipped + ' skipped.';
                if (body.errors && body.errors.length) {
                    html += '<div style="margin-top:6px">' + body.errors.length + ' row(s) skipped:</div><ul>';
                    body.errors.slice(0, 100).forEach(function (e) {
                        html += '<li>Line ' + e.row + ': ' + escHtml(e.message) + '</li>';
                    });
                    html += '</ul>';
                }
                html += '<div style="margin-top:8px">Reload the page to see the imported rows.</div></div>';
                result.innerHTML = html;
                notify('Import complete — ' + body.inserted + ' inserted, ' + body.updated + ' updated.', false);
            }).catch(function (e) {
                runBtn.disabled = false;
                runBtn.textContent = 'Import';
                result.innerHTML = '<div class="dt-result err">Network error: ' + escHtml(e.message) + '</div>';
            });
        });

        updateHint();
        fileInput.focus();
    }

    /* ------------------------------------------------------------------ */
    /* Public surface + auto-init outside the SPA shell                    */
    /* ------------------------------------------------------------------ */

    window.HMSData = {
        exportTable: exportTable,
        readTable: readTable,
        openImport: openImport,
        attach: window.initDataToolkits
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () { window.initDataToolkits(document); });
    } else {
        window.initDataToolkits(document);
    }
})();
