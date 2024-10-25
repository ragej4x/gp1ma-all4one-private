
function validatePasswords() {

    const password = document.getElementById('signup-password').value;
    const confirmPassword = document.getElementById('signup-password-confirm').value;
    const errorLabel = document.getElementById('password-error');
    const passwordLabel = document.querySelector('label[for="signup-password"]');
    const confirmPasswordLabel = document.querySelector('label[for="signup-password-confirm"]');

    errorLabel.style.display = 'none';
    passwordLabel.classList.remove('error');
    confirmPasswordLabel.classList.remove('error');

    if (password !== confirmPassword) {
        errorLabel.style.display = 'block';
        passwordLabel.classList.add('error');
        confirmPasswordLabel.classList.add('error');
        return false; 
    }
    return true; 
}