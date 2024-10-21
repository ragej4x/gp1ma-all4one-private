const form = document.getElementById('reg-form');
const passwordInput = document.getElementById('pass-1');
const confirmPasswordInput = document.getElementById('con-pass');


// Add event listener to the form
form.addEventListener('submit', function(event) {
    const password = passwordInput.value;
    const confirmPassword = confirmPasswordInput.value;

    // Check if passwords match
    if (password !== confirmPassword) {
        // Prevent form submission if passwords don't match
        event.preventDefault();
        alert("Password Missmatch!")

    }
});