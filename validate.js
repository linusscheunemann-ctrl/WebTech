document.addEventListener("DOMContentLoaded", function () {

    // ===== BENUTZERNAME =====
    const username = document.getElementById("username");
    const msgUser = document.getElementById("msg-user");

    username.addEventListener("input", function () {
        const value = username.value;

        const hasLength = value.length >= 5;
        const hasUpper = /[A-Z]/.test(value);
        const hasLower = /[a-z]/.test(value);

        if (value.length === 0) {
            msgUser.innerHTML = "";
            return;
        }

        msgUser.innerHTML = `
            <ul>
                <li class="${hasLength ? 'ok' : 'fail'}">
                    <span class="icon">${hasLength ? '✓' : '✗'}</span>
                    Mindestens 5 Zeichen
                </li>
                <li class="${hasUpper ? 'ok' : 'fail'}">
                    <span class="icon">${hasUpper ? '✓' : '✗'}</span>
                    Mindestens ein Großbuchstabe
                </li>
                <li class="${hasLower ? 'ok' : 'fail'}">
                    <span class="icon">${hasLower ? '✓' : '✗'}</span>
                    Mindestens ein Kleinbuchstabe
                </li>
            </ul>
        `;
    });


    // ===== PASSWORT =====
    const password = document.getElementById("password");
    const confirm = document.getElementById("confirm_password");
    const msgPw = document.getElementById("msg-pw");

    function validatePassword() {
        const pw = password.value;
        const cpw = confirm.value;

        const hasLength = pw.length >= 10;
        const matches = pw === cpw && pw.length > 0;

        if (pw.length === 0 && cpw.length === 0) {
            msgPw.innerHTML = "";
            return;
        }

        msgPw.innerHTML = `
            <ul>
                <li class="${hasLength ? 'ok' : 'fail'}">
                    <span class="icon">${hasLength ? '✓' : '✗'}</span>
                    Mindestens 10 Zeichen
                </li>
                <li class="${matches ? 'ok' : 'fail'}">
                    <span class="icon">${matches ? '✓' : '✗'}</span>
                    Passwörter stimmen überein
                </li>
            </ul>
        `;
    }

    password.addEventListener("input", validatePassword);
    confirm.addEventListener("input", validatePassword);
});