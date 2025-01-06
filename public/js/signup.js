const togglePassword = document.getElementById('togglePassword');
const password = document.getElementById('password');

togglePassword.addEventListener('click', function () {
    // Toggle the password field type between 'password' and 'text'
    const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
    password.setAttribute('type', type);

    // Change the icon/text of the button
    this.textContent = type === 'password' ? '👁' : '👁‍🗨';
});