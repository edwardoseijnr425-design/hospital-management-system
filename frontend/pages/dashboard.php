<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon primary">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                <circle cx="9" cy="7" r="4"></circle>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
            </svg>
        </div>
        <div class="stat-info">
            <h3 id="total-patients">-</h3>
            <p>Total Patients</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon success">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                <circle cx="8.5" cy="7" r="4"></circle>
                <line x1="20" y1="8" x2="20" y2="14"></line>
                <line x1="23" y1="11" x2="17" y2="11"></line>
            </svg>
        </div>
        <div class="stat-info">
            <h3 id="today-visits">-</h3>
            <p>Today's Visits</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon warning">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
            </svg>
        </div>
        <div class="stat-info">
            <h3 id="active-visits">-</h3>
            <p>Active Visits</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon danger">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                <line x1="12" y1="9" x2="12" y2="13"></line>
                <line x1="12" y1="17" x2="12.01" y2="17"></line>
            </svg>
        </div>
        <div class="stat-info">
            <h3 id="pending-labs">-</h3>
            <p>Pending Labs</p>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2>Recent Activity</h2>
    </div>
    <div class="card-body">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody id="audit-table">
                    <tr>
                        <td colspan="4" style="text-align: center;">Loading...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
async function initDashboard() {
    try {
        // Load dashboard stats
        const statsResponse = await fetch('/hms/backend/api/dashboard.php?action=stats');
        const statsData = await statsResponse.json();
        
        if (statsData.success) {
            document.getElementById('total-patients').textContent = statsData.stats.total_patients || 0;
            document.getElementById('today-visits').textContent = statsData.stats.today_visits || 0;
            document.getElementById('active-visits').textContent = statsData.stats.active_visits || 0;
            document.getElementById('pending-labs').textContent = statsData.stats.pending_labs || 0;
        }
        
        // Load recent audit trail
        const auditResponse = await fetch('/hms/backend/api/audit.php?action=recent&limit=10');
        const auditData = await auditResponse.json();
        
        if (auditData.success) {
            renderAuditTable(auditData.audit_logs);
        }
    } catch (error) {
        console.error('Dashboard load error:', error);
    }
}

function renderAuditTable(auditLogs) {
    const tbody = document.getElementById('audit-table');
    
    if (!auditLogs || auditLogs.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" style="text-align: center;">No recent activity</td></tr>';
        return;
    }
    
    tbody.innerHTML = auditLogs.map(log => `
        <tr>
            <td>${formatDate(log.created_at)}</td>
            <td>${log.full_name || 'Unknown'}</td>
            <td><span class="badge badge-info">${log.action}</span></td>
            <td>${log.table_name} ${log.record_id ? '(ID: ' + log.record_id + ')' : ''}</td>
        </tr>
    `).join('');
}

function formatDate(dateString) {
    return fmtDateTime(dateString);
}
</script>
