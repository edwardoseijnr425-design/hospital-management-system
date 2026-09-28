<div class="card">
    <div class="card-header">
        <h2>Messages &amp; Alerts</h2>
        <div style="display:flex;gap:10px;">
            <button class="btn btn-primary btn-sm" id="msg-compose-btn">Compose Message</button>
            <button class="btn btn-secondary btn-sm" id="msg-refresh-btn">Refresh</button>
        </div>
    </div>
    <div class="card-body">
        <div class="form-row">
            <div class="form-group">
                <input type="text" id="msg-search" placeholder="Search messages by subject or recipient...">
            </div>
            <div class="form-group">
                <select id="msg-filter-channel">
                    <option value="">All Channels</option>
                    <option value="internal">Internal</option>
                    <option value="sms">SMS</option>
                    <option value="email">Email</option>
                </select>
            </div>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Status</th>
                        <th>Subject</th>
                        <th>From</th>
                        <th>To</th>
                        <th>Channel</th>
                        <th>Sent At</th>
                    </tr>
                </thead>
                <tbody id="messages-table">
                    <tr><td colspan="6" style="text-align: center;">Loading...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
let messagesData = [];

async function initMessages() {
    setupEventListeners();
    await loadMessages();
}

async function loadMessages() {
    try {
        const response = await fetch('/hms/backend/api/messages.php?action=list&limit=200');
        const data = await response.json();
        if (data.success) {
            messagesData = data.messages || [];
            renderTable();
        } else {
            throw new Error(data.error || 'Failed to load messages');
        }
    } catch (error) {
        console.error('Messages load error:', error);
        const tbody = document.getElementById('messages-table');
        if (tbody) tbody.innerHTML = '<tr><td colspan="6" style="text-align: center;">Failed to load messages</td></tr>';
    }
}

function renderTable() {
    const tbody = document.getElementById('messages-table');
    if (!messagesData.length) {
        tbody.innerHTML = '<tr><td colspan="6" style="text-align: center;">No messages yet. Use "Compose Message" to send your first message.</td></tr>';
        return;
    }
    const search = (document.getElementById('msg-search').value || '').toLowerCase();
    const channel = document.getElementById('msg-filter-channel').value;

    const rows = messagesData.filter(m => {
        const matchesSearch = !search
            || (m.subject || '').toLowerCase().includes(search)
            || (m.recipient_name || '').toLowerCase().includes(search)
            || (m.sender_name || '').toLowerCase().includes(search);
        const matchesChannel = !channel || m.channel === channel;
        return matchesSearch && matchesChannel;
    });

    if (!rows.length) {
        tbody.innerHTML = '<tr><td colspan="6" style="text-align: center;">No messages match your filters</td></tr>';
        return;
    }

    tbody.innerHTML = rows.map(m => {
        const badge = m.is_read ? 'badge-secondary' : 'badge-info';
        const col = m.channel === 'sms' ? '#E67E22' : m.channel === 'email' ? '#2980B9' : '#2ECC71';
        return `
        <tr>
            <td><span class="badge ${badge}">${m.is_read ? 'Read' : 'New'}</span></td>
            <td><strong>${m.subject || '-'}</strong></td>
            <td>${m.sender_name || '-'}</td>
            <td>${m.recipient_name || '-'}</td>
            <td><span style="color:${col};font-weight:700;">${(m.channel || 'internal').toUpperCase()}</span></td>
            <td>${fmtDateTime(m.sent_at)}</td>
        </tr>`;
    }).join('');
}

function setupEventListeners() {
    document.getElementById('msg-compose-btn').addEventListener('click', () => {
        if (typeof window.openSendMessageModal === 'function') {
            window.openSendMessageModal();
        } else {
            showAlert('Message composer unavailable', 'error');
        }
    });
    document.getElementById('msg-refresh-btn').addEventListener('click', loadMessages);
    document.getElementById('msg-search').addEventListener('input', debounce(renderTable, 250));
    document.getElementById('msg-filter-channel').addEventListener('change', renderTable);
}

function debounce(fn, wait) {
    let t;
    return function (...args) {
        clearTimeout(t);
        t = setTimeout(() => fn.apply(this, args), wait);
    };
}
</script>