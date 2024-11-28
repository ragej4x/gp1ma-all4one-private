const form = document.getElementById('profile-form');
const passwordInput = document.getElementById('pass');
const confirmPasswordInput = document.getElementById('confirm-pass');


form.addEventListener('submit', function(event) {
    const password = passwordInput.value;
    const confirmPassword = confirmPasswordInput.value;

    if (password !== confirmPassword) {
        event.preventDefault();
        alert("Password Missmatch!")
    }
});