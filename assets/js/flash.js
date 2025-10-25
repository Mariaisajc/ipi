// Mostrar mensaje flash
document.addEventListener('DOMContentLoaded', function() {
    const messageDiv = document.getElementById('message');
    
    if (messageDiv && messageDiv.textContent.trim() !== '') {
        messageDiv.style.display = 'block';
        
        // Auto-ocultar después de 5 segundos
        setTimeout(function() {
            messageDiv.style.display = 'none';
        }, 5000);
    }
    
    // Ocultar mensaje cuando se escribe en los campos
    const loginInput = document.getElementById('login');
    const passwordInput = document.getElementById('password');
    
    if (loginInput) {
        loginInput.addEventListener('input', function() {
            if (messageDiv) {
                messageDiv.style.display = 'none';
            }
        });
    }
    
    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            if (messageDiv) {
                messageDiv.style.display = 'none';
            }
        });
    }
});