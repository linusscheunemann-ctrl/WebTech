document.addEventListener("DOMContentLoaded", function () {

    // =========================
    // USERNAME VALIDIERUNG
    // =========================
    const username = document.getElementById("username");
    const msgUser = document.getElementById("msg-user");

    if (username && msgUser) {
        username.addEventListener("input", function () {

            const value = username.value;

            const hasLength = value.length >= 5;
            const hasUpper = /[A-Z]/.test(value);
            const hasLower = /[a-z]/.test(value);

            const isValid = hasLength && hasUpper && hasLower;

            // Input Farbe
            username.classList.remove("valid", "invalid");

            if (value.length > 0) {
                username.classList.add(isValid ? "valid" : "invalid");
            }

            // Message reset
            if (value.length === 0) {
                msgUser.innerHTML = "";
                return;
            }

            msgUser.innerHTML = `
                <ul class="validation-list">
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
    }

    // =========================
    // PASSWORD VALIDIERUNG
    // =========================
    const password = document.getElementById("password");
    const confirm = document.getElementById("confirm_password");
    const msgPw = document.getElementById("msg-pw");

    function validatePassword() {
        if (!password || !msgPw) return;

        const pw = password.value;
        const cpw = confirm ? confirm.value : "";

        const hasLength = pw.length >= 10;
        const matches = confirm ? pw === cpw : true;

        const isValid = hasLength && matches;

        // Passwort Input Farbe
        password.classList.remove("valid", "invalid");
        if (pw.length > 0) {
            password.classList.add(isValid ? "valid" : "invalid");
        }

        // Confirm Input Farbe
        if (confirm) {
            confirm.classList.remove("valid", "invalid");
            if (cpw.length > 0) {
                confirm.classList.add(matches ? "valid" : "invalid");
            }
        }

        if (pw.length === 0 && (!confirm || cpw.length === 0)) {
            msgPw.innerHTML = "";
            return;
        }

        let message = `
            <ul class="validation-list">

                <li class="${hasLength ? 'ok' : 'fail'}">
                    <span class="icon">${hasLength ? '✓' : '✗'}</span>
                    Mindestens 10 Zeichen
                </li>
        `;

        if (confirm) {
            message += `
                <li class="${matches ? 'ok' : 'fail'}">
                    <span class="icon">${matches ? '✓' : '✗'}</span>
                    Passwörter stimmen überein
                </li>
            `;
        }

        message += `</ul>`;
        msgPw.innerHTML = message;
    }

    if (password && msgPw) {
        password.addEventListener("input", validatePassword);

        if (confirm) {
            confirm.addEventListener("input", validatePassword);
        }
    }

});