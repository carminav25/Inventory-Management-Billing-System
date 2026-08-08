/* =========================================================
   INVENTORY MANAGEMENT SYSTEM
   SIGNUP JAVASCRIPT
   ========================================================= */

document.addEventListener("DOMContentLoaded", function () {

    /* =====================================================
       ELEMENTS
       ===================================================== */

    const form = document.querySelector("form");

    const username = document.getElementById("username");
    const usernameMessage =
        document.getElementById("usernameMessage");

    const email = document.getElementById("email");
    const emailStatus =
        document.getElementById("emailStatus");
    const emailMessage =
        document.getElementById("emailMessage");

    const mobile = document.getElementById("mobile");
    const mobileStatus =
        document.getElementById("mobileStatus");
    const mobileMessage =
        document.getElementById("mobileMessage");

    const password = document.getElementById("password");
    const confirm = document.getElementById("confirm");

    const passwordMessage =
        document.getElementById("passwordMessage");

    const confirmMessage =
        document.getElementById("confirmMessage");

    const recoveryPass =
        document.getElementById("recovery_pass");

    const recoveryPassConfirm =
        document.getElementById("recovery_pass_confirm");


    /* =====================================================
       EMAIL VALIDATION
       ===================================================== */

    const emailRegex =
        /^[A-Za-z0-9._%+-]+@(?:gmail\.com|yahoo\.com|outlook\.com|hotmail\.com|live\.com|icloud\.com|aol\.com|proton\.me|protonmail\.com|zoho\.com|gmx\.com|mail\.com|yandex\.com|isu\.edu\.ph)$/i;


    /* =====================================================
       PASSWORD EYE TOGGLE
       IMPORTANT:
       Uses EXISTING eye icons from HTML.
       Does NOT create duplicate icons.
       ===================================================== */

    function setupPasswordToggle(inputId, toggleId) {

        const input = document.getElementById(inputId);
        const toggle = document.getElementById(toggleId);

        if (!input || !toggle) {
            return;
        }

        /* Prevent duplicate event listeners */
        if (toggle.dataset.toggleReady === "true") {
            return;
        }

        toggle.dataset.toggleReady = "true";

        /* Make sure eye is clickable */
        toggle.style.cursor = "pointer";
        toggle.style.pointerEvents = "auto";

        /* Initial icon */
        toggle.classList.remove("bi-eye");
        toggle.classList.add("bi-eye-slash");

        toggle.setAttribute(
            "role",
            "button"
        );

        toggle.setAttribute(
            "tabindex",
            "0"
        );

        toggle.setAttribute(
            "aria-label",
            "Show password"
        );

        toggle.setAttribute(
            "title",
            "Show password"
        );


        /* =================================================
           CLICK
           ================================================= */

        function togglePasswordVisibility() {

            if (input.type === "password") {

                /* SHOW */
                input.type = "text";

                toggle.classList.remove(
                    "bi-eye-slash"
                );

                toggle.classList.add(
                    "bi-eye"
                );

                toggle.setAttribute(
                    "aria-label",
                    "Hide password"
                );

                toggle.setAttribute(
                    "title",
                    "Hide password"
                );

            } else {

                /* HIDE */
                input.type = "password";

                toggle.classList.remove(
                    "bi-eye"
                );

                toggle.classList.add(
                    "bi-eye-slash"
                );

                toggle.setAttribute(
                    "aria-label",
                    "Show password"
                );

                toggle.setAttribute(
                    "title",
                    "Show password"
                );
            }
        }


        toggle.addEventListener(
            "click",
            togglePasswordVisibility
        );


        /* =================================================
           KEYBOARD SUPPORT
           ================================================= */

        toggle.addEventListener(
            "keydown",
            function (event) {

                if (
                    event.key === "Enter" ||
                    event.key === " "
                ) {

                    event.preventDefault();

                    togglePasswordVisibility();
                }
            }
        );
    }


    /* =====================================================
       CONNECT ALL FOUR EYES
       ===================================================== */

    setupPasswordToggle(
        "password",
        "togglePassword"
    );

    setupPasswordToggle(
        "confirm",
        "toggleConfirmPassword"
    );

    setupPasswordToggle(
        "recovery_pass",
        "toggleRecoveryPassword"
    );

    setupPasswordToggle(
        "recovery_pass_confirm",
        "toggleRecoveryConfirm"
    );


    /* =====================================================
       USERNAME VALIDATION
       ===================================================== */

    function validateUsername() {

        if (!username) {
            return true;
        }

        username.value =
            username.value.replace(
                /[^A-Za-z0-9_]/g,
                ""
            );

        const value =
            username.value.trim();

        if (value === "") {

            username.classList.remove(
                "is-valid",
                "is-invalid"
            );

            if (usernameMessage) {
                usernameMessage.className =
                    "password-message d-none";

                usernameMessage.innerHTML = "";
            }

            return false;
        }

        const valid =
            /^[A-Za-z0-9_]{4,20}$/.test(value);

        if (valid) {

            username.classList.remove(
                "is-invalid"
            );

            username.classList.add(
                "is-valid"
            );

            if (usernameMessage) {
                usernameMessage.className =
                    "password-message d-none";

                usernameMessage.innerHTML = "";
            }

            return true;
        }

        username.classList.remove(
            "is-valid"
        );

        username.classList.add(
            "is-invalid"
        );

        if (usernameMessage) {

            usernameMessage.className =
                "password-message text-danger";

            usernameMessage.innerHTML =
                '<i class="bi bi-exclamation-circle me-1"></i>' +
                "Username must contain 4–20 characters " +
                "using only letters, numbers, or underscore.";
        }

        return false;
    }


    if (username) {

        username.addEventListener(
            "input",
            validateUsername
        );
    }


    /* =====================================================
       EMAIL VALIDATION
       ===================================================== */

    function validateEmail() {

        if (!email) {
            return true;
        }

        const value =
            email.value.trim();

        if (value === "") {

            email.classList.remove(
                "is-valid",
                "is-invalid"
            );

            if (emailStatus) {
                emailStatus.className =
                    "bi validation-status d-none";
            }

            if (emailMessage) {
                emailMessage.className =
                    "password-message d-none";

                emailMessage.innerHTML = "";
            }

            return false;
        }

        const valid =
            emailRegex.test(value);

        if (valid) {

            email.classList.remove(
                "is-invalid"
            );

            email.classList.add(
                "is-valid"
            );

            if (emailStatus) {

                emailStatus.className =
                    "bi bi-check-circle-fill " +
                    "validation-status valid-status";
            }

            if (emailMessage) {

                emailMessage.className =
                    "password-message d-none";

                emailMessage.innerHTML = "";
            }

            return true;
        }

        email.classList.remove(
            "is-valid"
        );

        email.classList.add(
            "is-invalid"
        );

        if (emailStatus) {

            emailStatus.className =
                "bi bi-x-circle-fill " +
                "validation-status invalid-status";
        }

        if (emailMessage) {

            emailMessage.className =
                "password-message text-danger";

            emailMessage.innerHTML =
                '<i class="bi bi-exclamation-circle me-1"></i>' +
                "Enter a valid email address " +
                "(Gmail, Yahoo, Outlook, Hotmail, " +
                "Live, iCloud, Proton, or ISU).";
        }

        return false;
    }


    if (email) {

        email.addEventListener(
            "input",
            validateEmail
        );
    }


    /* =====================================================
       MOBILE VALIDATION
       ===================================================== */

    function validateMobile() {

        if (!mobile) {
            return true;
        }

        let value =
            mobile.value;

        value =
            value.replace(
                /[^\d+]/g,
                ""
            );

        if (value.includes("+")) {

            value =
                "+" +
                value.replace(
                    /\+/g,
                    ""
                );
        }

        mobile.value =
            value;

        if (value === "") {

            mobile.classList.remove(
                "is-valid",
                "is-invalid"
            );

            if (mobileStatus) {
                mobileStatus.className =
                    "bi validation-status d-none";
            }

            if (mobileMessage) {

                mobileMessage.className =
                    "password-message d-none";

                mobileMessage.innerHTML = "";
            }

            return false;
        }

        const localFormat =
            /^09\d{9}$/;

        const internationalFormat =
            /^\+639\d{9}$/;

        const valid =
            localFormat.test(value) ||
            internationalFormat.test(value);

        if (valid) {

            mobile.classList.remove(
                "is-invalid"
            );

            mobile.classList.add(
                "is-valid"
            );

            if (mobileStatus) {

                mobileStatus.className =
                    "bi bi-check-circle-fill " +
                    "validation-status valid-status";
            }

            if (mobileMessage) {

                mobileMessage.className =
                    "password-message d-none";

                mobileMessage.innerHTML = "";
            }

            return true;
        }

        mobile.classList.remove(
            "is-valid"
        );

        mobile.classList.add(
            "is-invalid"
        );

        if (mobileStatus) {

            mobileStatus.className =
                "bi bi-x-circle-fill " +
                "validation-status invalid-status";
        }

        if (mobileMessage) {

            mobileMessage.className =
                "password-message text-danger";

            mobileMessage.innerHTML =
                '<i class="bi bi-exclamation-circle me-1"></i>' +
                "Use 11 digits (09XXXXXXXXX) " +
                "or +63 format (+639XXXXXXXXX).";
        }

        return false;
    }


    if (mobile) {

        mobile.addEventListener(
            "input",
            validateMobile
        );
    }


    /* =====================================================
       PASSWORD STRENGTH
       ===================================================== */

    function checkPasswordStrength(
        value = null
    ) {

        if (!password) {
            return false;
        }

        if (value === null) {
            value = password.value;
        }

        if (!passwordMessage) {
            return false;
        }

        if (value.length === 0) {

            password.classList.remove(
                "is-valid",
                "is-invalid"
            );

            passwordMessage.className =
                "password-message d-none";

            passwordMessage.innerHTML = "";

            return false;
        }

        const hasLength =
            value.length >= 8;

        const hasUpper =
            /[A-Z]/.test(value);

        const hasLower =
            /[a-z]/.test(value);

        const hasNumber =
            /[0-9]/.test(value);

        const hasSpecial =
            /[^A-Za-z0-9]/.test(value);

        const score =
            Number(hasLength) +
            Number(hasUpper) +
            Number(hasLower) +
            Number(hasNumber) +
            Number(hasSpecial);

        passwordMessage.classList.remove(
            "d-none"
        );


        /* WEAK */

        if (
            !hasLength ||
            score <= 2
        ) {

            password.classList.remove(
                "is-valid"
            );

            password.classList.add(
                "is-invalid"
            );

            passwordMessage.className =
                "password-message text-danger";

            passwordMessage.innerHTML =
                '<i class="bi bi-exclamation-circle me-1"></i>' +
                "Weak Password — use at least 8 " +
                "characters, uppercase, lowercase, " +
                "number, and special character.";

            return false;
        }


        /* GOOD */

        if (score < 5) {

            password.classList.remove(
                "is-invalid"
            );

            password.classList.add(
                "is-valid"
            );

            passwordMessage.className =
                "password-message text-warning";

            passwordMessage.innerHTML =
                '<i class="bi bi-check-circle me-1"></i>' +
                "Good Password — add more variety " +
                "for a stronger password.";

            return false;
        }


        /* STRONG */

        password.classList.remove(
            "is-invalid"
        );

        password.classList.add(
            "is-valid"
        );

        passwordMessage.className =
            "password-message text-success";

        passwordMessage.innerHTML =
            '<i class="bi bi-check-circle-fill me-1"></i>' +
            "Strong Password";

        return true;
    }


    if (password) {

        password.addEventListener(
            "input",
            function () {

                checkPasswordStrength(
                    password.value
                );

                if (
                    confirm &&
                    confirm.value !== ""
                ) {

                    validatePasswordMatch();
                }
            }
        );
    }


    /* =====================================================
       CONFIRM PASSWORD
       ===================================================== */

    function validatePasswordMatch() {

        if (
            !password ||
            !confirm
        ) {

            return false;
        }

        if (
            confirm.value === ""
        ) {

            confirm.classList.remove(
                "is-valid",
                "is-invalid"
            );

            if (confirmMessage) {

                confirmMessage.className =
                    "password-message d-none";

                confirmMessage.innerHTML = "";
            }

            return false;
        }


        /* MATCH */

        if (
            confirm.value ===
            password.value
        ) {

            confirm.classList.remove(
                "is-invalid"
            );

            confirm.classList.add(
                "is-valid"
            );

            if (confirmMessage) {

                confirmMessage.className =
                    "password-message text-success";

                confirmMessage.innerHTML =
                    '<i class="bi bi-check-circle-fill me-1"></i>' +
                    "Passwords match";
            }

            return true;
        }


        /* NOT MATCHING */

        confirm.classList.remove(
            "is-valid"
        );

        confirm.classList.add(
            "is-invalid"
        );

        if (confirmMessage) {

            confirmMessage.className =
                "password-message text-danger";

            confirmMessage.innerHTML =
                '<i class="bi bi-x-circle-fill me-1"></i>' +
                "Passwords do not match";
        }

        return false;
    }


    if (confirm) {

        confirm.addEventListener(
            "input",
            validatePasswordMatch
        );
    }


    /* =====================================================
       RECOVERY PASSWORD MESSAGE
       ===================================================== */

    function getRecoveryMessage() {

        if (!recoveryPass) {
            return null;
        }

        let message =
            document.getElementById(
                "recoveryPasswordMessage"
            );

        if (!message) {

            message =
                document.createElement(
                    "div"
                );

            message.id =
                "recoveryPasswordMessage";

            message.className =
                "password-message d-none";

            const parent =
                recoveryPass.parentElement;

            if (parent) {

                parent.insertAdjacentElement(
                    "afterend",
                    message
                );
            }
        }

        return message;
    }


    /* =====================================================
       RECOVERY PASSWORD STRENGTH
       ===================================================== */

    function checkRecoveryPasswordStrength() {

        if (!recoveryPass) {
            return true;
        }

        const message =
            getRecoveryMessage();

        if (!message) {
            return true;
        }

        const value =
            recoveryPass.value;


        if (value === "") {

            recoveryPass.classList.remove(
                "is-valid",
                "is-invalid"
            );

            message.className =
                "password-message d-none";

            message.innerHTML = "";

            return true;
        }


        const hasLength =
            value.length >= 8;

        const hasUpper =
            /[A-Z]/.test(value);

        const hasLower =
            /[a-z]/.test(value);

        const hasNumber =
            /[0-9]/.test(value);

        const hasSpecial =
            /[^A-Za-z0-9]/.test(value);

        const score =
            Number(hasLength) +
            Number(hasUpper) +
            Number(hasLower) +
            Number(hasNumber) +
            Number(hasSpecial);


        message.classList.remove(
            "d-none"
        );


        /* WEAK */

        if (
            !hasLength ||
            score <= 2
        ) {

            recoveryPass.classList.remove(
                "is-valid"
            );

            recoveryPass.classList.add(
                "is-invalid"
            );

            message.className =
                "password-message text-danger";

            message.innerHTML =
                '<i class="bi bi-exclamation-circle me-1"></i>' +
                "Weak Password — use at least 8 " +
                "characters, uppercase, lowercase, " +
                "number, and special character.";

            return false;
        }


        /* GOOD */

        if (score < 5) {

            recoveryPass.classList.remove(
                "is-invalid"
            );

            recoveryPass.classList.add(
                "is-valid"
            );

            message.className =
                "password-message text-warning";

            message.innerHTML =
                '<i class="bi bi-check-circle me-1"></i>' +
                "Good Password — add more variety " +
                "for a stronger password.";

            return false;
        }


        /* STRONG */

        recoveryPass.classList.remove(
            "is-invalid"
        );

        recoveryPass.classList.add(
            "is-valid"
        );

        message.className =
            "password-message text-success";

        message.innerHTML =
            '<i class="bi bi-check-circle-fill me-1"></i>' +
            "Strong Password";

        return true;
    }


    if (recoveryPass) {

        recoveryPass.addEventListener(
            "input",
            function () {

                checkRecoveryPasswordStrength();

                if (
                    recoveryPassConfirm &&
                    recoveryPassConfirm.value !== ""
                ) {

                    validateRecoveryMatch();
                }
            }
        );
    }


    /* =====================================================
       RECOVERY CONFIRM MESSAGE
       ===================================================== */

    let recoveryConfirmMessage = null;


    function getRecoveryConfirmMessage() {

        if (!recoveryPassConfirm) {
            return null;
        }

        if (recoveryConfirmMessage) {
            return recoveryConfirmMessage;
        }

        recoveryConfirmMessage =
            document.getElementById(
                "recoveryConfirmMessage"
            );

        if (!recoveryConfirmMessage) {

            recoveryConfirmMessage =
                document.createElement(
                    "div"
                );

            recoveryConfirmMessage.id =
                "recoveryConfirmMessage";

            recoveryConfirmMessage.className =
                "password-message d-none";

            const parent =
                recoveryPassConfirm.parentElement;

            if (parent) {

                parent.insertAdjacentElement(
                    "afterend",
                    recoveryConfirmMessage
                );
            }
        }

        return recoveryConfirmMessage;
    }


    /* =====================================================
       RECOVERY CONFIRM PASSWORD
       ===================================================== */

    function validateRecoveryMatch() {

        if (
            !recoveryPass ||
            !recoveryPassConfirm
        ) {

            return true;
        }

        const message =
            getRecoveryConfirmMessage();

        if (!message) {
            return true;
        }


        /* EMPTY */

        if (
            recoveryPassConfirm.value === ""
        ) {

            recoveryPassConfirm.classList.remove(
                "is-valid",
                "is-invalid"
            );

            message.className =
                "password-message d-none";

            message.innerHTML = "";

            return true;
        }


        /* MATCH */

        if (
            recoveryPass.value ===
            recoveryPassConfirm.value
        ) {

            recoveryPassConfirm.classList.remove(
                "is-invalid"
            );

            recoveryPassConfirm.classList.add(
                "is-valid"
            );

            message.className =
                "password-message text-success";

            message.innerHTML =
                '<i class="bi bi-check-circle-fill me-1"></i>' +
                "Recovery passwords match";

            return true;
        }


        /* NOT MATCHING */

        recoveryPassConfirm.classList.remove(
            "is-valid"
        );

        recoveryPassConfirm.classList.add(
            "is-invalid"
        );

        message.className =
            "password-message text-danger";

        message.innerHTML =
            '<i class="bi bi-x-circle-fill me-1"></i>' +
            "Recovery passwords do not match";

        return false;
    }


    if (recoveryPassConfirm) {

        recoveryPassConfirm.addEventListener(
            "input",
            validateRecoveryMatch
        );
    }


    /* =====================================================
       FORM SUBMISSION
       ===================================================== */

    if (form) {

        form.addEventListener(
            "submit",
            function (e) {

                let valid = true;


                /* USERNAME */

                if (
                    username &&
                    !validateUsername()
                ) {

                    e.preventDefault();

                    username.focus();

                    valid = false;
                }


                /* EMAIL */

                if (
                    email &&
                    !validateEmail()
                ) {

                    e.preventDefault();

                    email.focus();

                    valid = false;
                }


                /* MOBILE */

                if (
                    mobile &&
                    mobile.value !== "" &&
                    !validateMobile()
                ) {

                    e.preventDefault();

                    mobile.focus();

                    valid = false;
                }


                /* PASSWORD */

                if (
                    password &&
                    !checkPasswordStrength(
                        password.value
                    )
                ) {

                    e.preventDefault();

                    password.focus();

                    valid = false;
                }


                /* CONFIRM PASSWORD */

                if (
                    password &&
                    confirm &&
                    !validatePasswordMatch()
                ) {

                    e.preventDefault();

                    confirm.focus();

                    valid = false;
                }


                /* RECOVERY PASSWORD */

                if (recoveryPass) {

                    if (
                        recoveryPass.value !== "" &&
                        !checkRecoveryPasswordStrength()
                    ) {

                        e.preventDefault();

                        recoveryPass.focus();

                        valid = false;
                    }
                }


                /* CONFIRM RECOVERY PASSWORD */

                if (
                    recoveryPass &&
                    recoveryPassConfirm &&
                    recoveryPassConfirm.value !== ""
                ) {

                    if (
                        !validateRecoveryMatch()
                    ) {

                        e.preventDefault();

                        recoveryPassConfirm.focus();

                        valid = false;
                    }
                }


                return valid;
            }
        );
    }

});