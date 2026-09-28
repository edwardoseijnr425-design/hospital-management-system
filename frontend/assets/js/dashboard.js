document.addEventListener('DOMContentLoaded', function() {
    const menuToggle = document.getElementById('menu-toggle');
    const sidebar = document.querySelector('.sidebar');
    const logoutBtn = document.getElementById('logout-btn');
    const navLinks = document.querySelectorAll('.nav-link');
    const pageTitle = document.getElementById('page-title');
    const pageContent = document.getElementById('page-content');
    
    // Mobile menu toggle
    if (menuToggle && sidebar) {
        menuToggle.addEventListener('click', function() {
            sidebar.classList.toggle('open');
        });
    }
    
    // Logout
    if (logoutBtn) {
        logoutBtn.addEventListener('click', async function() {
            try {
                await fetch('/hms/backend/api/auth.php?action=logout', {
                    method: 'POST'
                });
                window.location.href = '/hms/frontend/index.php';
            } catch (error) {
                console.error('Logout error:', error);
                window.location.href = '/hms/frontend/index.php';
            }
        });
    }
    
    // Navigation
    navLinks.forEach(link => {
        link.addEventListener('click', async function(e) {
            e.preventDefault();
            
            const page = this.getAttribute('data-page');
            
            // Update active state
            navLinks.forEach(l => l.classList.remove('active'));
            this.classList.add('active');
            
            // Close mobile menu
            if (sidebar) {
                sidebar.classList.remove('open');
            }
            
            // Load page content
            await loadPage(page);
        });
    });
    
    // Load initial page
    loadPage('dashboard');
    
    async function loadPage(page) {
        if (!pageTitle || !pageContent) return;
        
        // Update page title
        pageTitle.textContent = formatPageTitle(page);
        
        // Show loading state
        pageContent.innerHTML = '<div class="card"><div class="card-body" style="display:flex;justify-content:center;align-items:center;"><div class="spinner"></div></div></div>';
        
        try {
            const response = await fetch(`/hms/frontend/pages/${page}.php`);
            
            if (response.ok) {
                const html = await response.text();
                pageContent.innerHTML = html;
                
                // Re-execute inline scripts injected via innerHTML so page
                // init functions (initPatients, initWards, ...) are defined.
                pageContent.querySelectorAll('script').forEach(oldScript => {
                    const s = document.createElement('script');
                    if (oldScript.src) { s.src = oldScript.src; }
                    else { s.textContent = oldScript.textContent; }
                    oldScript.parentNode.replaceChild(s, oldScript);
                });
                
                // Initialize page-specific scripts
                if (window[`init${capitalize(page)}`]) {
                    window[`init${capitalize(page)}`]();
                }
            } else {
                pageContent.innerHTML = '<div class="card"><div class="card-body"><p class="alert alert-error">Failed to load page content.</p></div></div>';
            }
        } catch (error) {
            console.error('Page load error:', error);
            pageContent.innerHTML = '<div class="card"><div class="card-body"><p class="alert alert-error">Network error. Please try again.</p></div></div>';
        }
    }
    
    function formatPageTitle(page) {
        return page.split('-').map(word => capitalize(word)).join(' ');
    }
    
    function capitalize(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }
});

// Global API helper
async function apiRequest(url, options = {}) {
    const defaultOptions = {
        headers: {
            'Content-Type': 'application/json',
        },
    };
    
    const response = await fetch(url, { ...defaultOptions, ...options });
    const data = await response.json();
    
    if (!response.ok) {
        throw new Error(data.error || 'Request failed');
    }
    
    return data;
}

// Show alert message
function showAlert(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type}`;
    alertDiv.textContent = message;
    
    const pageContent = document.getElementById('page-content');
    if (pageContent) {
        pageContent.insertBefore(alertDiv, pageContent.firstChild);
        
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }
}
