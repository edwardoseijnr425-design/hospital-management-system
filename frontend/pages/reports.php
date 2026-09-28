<div class="card">
    <div class="card-header">
        <h2>Reports</h2>
    </div>
    <div class="card-body">
        <div class="form-row">
            <div class="form-group">
                <label for="report-type">Report Type</label>
                <select id="report-type">
                    <option value="">Select Report Type</option>
                    <option value="patient_registrations">Patient Registrations</option>
                    <option value="visits_summary">Visits Summary</option>
                    <option value="consultations_summary">Consultations Summary</option>
                    <option value="department_stats">Department Statistics</option>
                    <option value="revenue_summary">Revenue Summary</option>
                    <option value="bed_occupancy">Bed Occupancy</option>
                    <option value="lab_summary">Laboratory Summary</option>
                    <option value="audit_log">Audit Log</option>
                </select>
            </div>
            <div class="form-group">
                <label for="report-date-from">From Date</label>
                <input type="date" id="report-date-from">
            </div>
            <div class="form-group">
                <label for="report-date-to">To Date</label>
                <input type="date" id="report-date-to">
            </div>
        </div>
        
        <div class="form-actions">
            <button class="btn btn-primary" id="generate-report-btn">Generate Report</button>
            <button class="btn btn-secondary" id="export-report-btn">Export CSV</button>
        </div>
    </div>
</div>

<div class="card" id="report-results-card" style="display: none;">
    <div class="card-header">
        <h2 id="report-title">Report Results</h2>
    </div>
    <div class="card-body">
        <div id="report-summary"></div>
        <div class="table-container" id="report-table-container">
            <table id="report-table">
                <!-- Report data will be loaded here -->
            </table>
        </div>
    </div>
</div>

<script>
async function initReports() {
    setupEventListeners();
    
    // Set default date range (current month)
    const today = new Date();
    const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
    
    document.getElementById('report-date-from').value = firstDay.toISOString().split('T')[0];
    document.getElementById('report-date-to').value = today.toISOString().split('T')[0];
}

function setupEventListeners() {
    document.getElementById('generate-report-btn').addEventListener('click', generateReport);
    document.getElementById('export-report-btn').addEventListener('click', exportReport);
}

async function generateReport() {
    const reportType = document.getElementById('report-type').value;
    const dateFrom = document.getElementById('report-date-from').value;
    const dateTo = document.getElementById('report-date-to').value;
    
    if (!reportType) {
        showAlert('Please select a report type', 'error');
        return;
    }
    
    if (!dateFrom || !dateTo) {
        showAlert('Please select date range', 'error');
        return;
    }
    
    try {
        const response = await fetch(`/hms/backend/api/reports.php?type=${reportType}&date_from=${dateFrom}&date_to=${dateTo}`);
        const data = await response.json();
        
        if (data.success) {
            displayReport(data);
        } else {
            showAlert(data.error || 'Failed to generate report', 'error');
        }
    } catch (error) {
        console.error('Report generation error:', error);
        showAlert('Network error. Please try again.', 'error');
    }
}

function displayReport(data) {
    const reportCard = document.getElementById('report-results-card');
    const reportTitle = document.getElementById('report-title');
    const reportSummary = document.getElementById('report-summary');
    const reportTable = document.getElementById('report-table');
    
    reportCard.style.display = 'block';
    reportTitle.textContent = data.report_title || 'Report Results';
    
    // Display summary if available
    if (data.summary) {
        reportSummary.innerHTML = '<div class="stats-grid">' + 
            Object.entries(data.summary).map(([key, value]) => `
                <div class="stat-card">
                    <div class="stat-icon primary">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                            <polyline points="10 9 9 9 8 9"></polyline>
                        </svg>
                    </div>
                    <div class="stat-info">
                        <h3>${value}</h3>
                        <p>${formatKey(key)}</p>
                    </div>
                </div>
            `).join('') + 
        '</div>';
    } else {
        reportSummary.innerHTML = '';
    }
    
    // Display table data
    if (data.data && data.data.length > 0) {
        const headers = Object.keys(data.data[0]);
        
        reportTable.innerHTML = `
            <thead>
                <tr>
                    ${headers.map(header => `<th>${formatKey(header)}</th>`).join('')}
                </tr>
            </thead>
            <tbody>
                ${data.data.map(row => `
                    <tr>
                        ${headers.map(header => `<td>${row[header] || '-'}</td>`).join('')}
                    </tr>
                `).join('')}
            </tbody>
        `;
    } else {
        reportTable.innerHTML = '<tbody><tr><td colspan="100%" style="text-align: center;">No data available for this report</td></tr></tbody>';
    }
}

function formatKey(key) {
    return key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
}

function exportReport() {
    const reportTable = document.getElementById('report-table');
    const reportTitle = document.getElementById('report-title').textContent;
    
    if (!reportTable || reportTable.rows.length === 0) {
        showAlert('No report data to export', 'error');
        return;
    }
    
    let csv = [];
    
    // Add headers
    const headers = [];
    for (let i = 0; i < reportTable.rows[0].cells.length; i++) {
        headers.push(reportTable.rows[0].cells[i].textContent);
    }
    csv.push(headers.join(','));
    
    // Add data rows
    for (let i = 1; i < reportTable.rows.length; i++) {
        const row = [];
        for (let j = 0; j < reportTable.rows[i].cells.length; j++) {
            row.push(reportTable.rows[i].cells[j].textContent);
        }
        csv.push(row.join(','));
    }
    
    // Create download link
    const csvFile = new Blob([csv.join('\n')], { type: 'text/csv' });
    const downloadLink = document.createElement('a');
    downloadLink.download = `${reportTitle.replace(/\s+/g, '_')}_${new Date().toISOString().split('T')[0]}.csv`;
    downloadLink.href = window.URL.createObjectURL(csvFile);
    downloadLink.style.display = 'none';
    document.body.appendChild(downloadLink);
    downloadLink.click();
    document.body.removeChild(downloadLink);
}
</script>
