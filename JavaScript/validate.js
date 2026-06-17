// ## Beginn Code von Linus generiert mit Unterstützung von CoPilot

document.addEventListener("DOMContentLoaded", function () {
    // Alle Formular-Elemente werden erst nach dem Laden des DOM geholt,
    // damit die Live-Validierung nur dort aktiv wird, wo die Felder wirklich existieren.
    const username = document.getElementById("username");
    const msgUser = document.getElementById("msg-user");
    const password = document.getElementById("password");
    const confirm = document.getElementById("confirm_password");
    const msgPw = document.getElementById("msg-pw");
    const submitButton = document.getElementById("register-submit") || document.getElementById("profile-submit");

    // Fügt bei Passwortfeldern einen kleinen Umschalter für "anzeigen/verbergen" ein,
    // damit Nutzer ihr Passwort bei Bedarf kontrollieren können.
    function attachPasswordToggle(input) {
        if (!input || input.dataset.toggleAttached === "true") return;

        const toggleButton = document.createElement("button");
        toggleButton.type = "button";
        toggleButton.className = "password-toggle";
        toggleButton.textContent = "Anzeigen";
        toggleButton.setAttribute("aria-label", "Passwort anzeigen");
        toggleButton.setAttribute("aria-pressed", "false");

        toggleButton.addEventListener("click", () => {
            const isHidden = input.type === "password";

            input.type = isHidden ? "text" : "password";
            toggleButton.textContent = isHidden ? "Verbergen" : "Anzeigen";
            toggleButton.setAttribute("aria-label", isHidden ? "Passwort verbergen" : "Passwort anzeigen");
            toggleButton.setAttribute("aria-pressed", String(isHidden));
        });

        input.insertAdjacentElement("afterend", toggleButton);
        input.dataset.toggleAttached = "true";
    }

    // Prüft alle benötigten Felder gemeinsam und schaltet den Submit-Button
    // nur frei, wenn Benutzername, Passwort und Bestätigung zusammen passen.
    function updateSubmitState() {
        if (!submitButton || !username || !password || !confirm) return;

        const usernameValue = username.value;
        const passwordValue = password.value;
        const confirmValue = confirm.value;

        const usernameValid = usernameValue.length >= 5
            && /[A-Z]/.test(usernameValue)
            && /[a-z]/.test(usernameValue);

        const passwordValid = passwordValue.length >= 10;
        const confirmValid = passwordValue === confirmValue;

        submitButton.disabled = !(usernameValid && passwordValid && confirmValid);
    }

    if (username && msgUser) {
        username.addEventListener("input", function () {

            const value = username.value;

            const hasLength = value.length >= 5;
            const hasUpper = /[A-Z]/.test(value);
            const hasLower = /[a-z]/.test(value);

            const isValid = hasLength && hasUpper && hasLower;

            // Das Eingabefeld bekommt direkt eine visuelle Rückmeldung.
            username.classList.remove("valid", "invalid");

            if (value.length > 0) {
                username.classList.add(isValid ? "valid" : "invalid");
            }

            // Bei leerem Feld wird die Hilfsmeldung komplett entfernt.
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

            updateSubmitState();
        });
    }

    // Prüft Passwortlänge und, falls vorhanden, die Übereinstimmung mit der Bestätigung.
    function validatePassword() {
        if (!password || !msgPw) return;

        const pw = password.value;
        const cpw = confirm ? confirm.value : "";

        const hasLength = pw.length >= 10;
        const matches = confirm ? pw === cpw : true;

        const isValid = hasLength && matches;

        // Das Passwortfeld selbst bekommt einen gültig/ungültig-Zustand.
        password.classList.remove("valid", "invalid");
        if (pw.length > 0) {
            password.classList.add(isValid ? "valid" : "invalid");
        }

        // Falls ein Bestätigungsfeld vorhanden ist, wird es mitgeprüft und markiert.
        if (confirm) {
            confirm.classList.remove("valid", "invalid");
            if (cpw.length > 0) {
                confirm.classList.add(matches ? "valid" : "invalid");
            }
        }

        // Wenn beide Felder leer sind, wird kein Hinweistext gezeigt.
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

        updateSubmitState();
    }

    if (password && msgPw) {
        password.addEventListener("input", validatePassword);

        if (confirm) {
            confirm.addEventListener("input", validatePassword);
        }
    }

    attachPasswordToggle(password);
    attachPasswordToggle(confirm);
    updateSubmitState();

});
// ## Schluss Code von Linus generiert mit Unterstützung von CoPilot