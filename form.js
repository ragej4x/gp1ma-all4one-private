const form = document.getElementById('reg-form');
const passwordInput = document.getElementById('pass-1');
const confirmPasswordInput = document.getElementById('con-pass');


form.addEventListener('submit', function(event) {
    const password = passwordInput.value;
    const confirmPassword = confirmPasswordInput.value;

    if (password !== confirmPassword) {
        event.preventDefault();
        alert("Password Missmatch!")

    }
});