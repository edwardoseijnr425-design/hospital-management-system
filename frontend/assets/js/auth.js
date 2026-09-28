document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('login-form');
    const loginError = document.getElementById('login-error');
    
    if (loginForm) {
        loginForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(loginForm);
            const data = {
                username: formData.get('username'),
                password: formData.get('password')
            };
            
            try {
                const response = await fetch('/hms/backend/api/auth.php?action=login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    window.location.href = '/hms/frontend/dashboard.php';
                } else {
                    showError(result.error || 'Login failed');
                }
            } catch (error) {
                showError('Network error. Please try again.');
            }
        });
    }
    
    function showError(message) {
        if (loginError) {
            loginError.textContent = message;
            loginError.classList.remove('hidden');
            loginError.classList.add('show');
        }
    }
});
