document.addEventListener("DOMContentLoaded", function () {

    /* =========================================================
       ELEMENTS
       ========================================================= */

    const form = document.getElementById("signupForm");

    const password = document.getElementById("password");
    const confirm = document.getElementById("confirm");

    const recoveryPass = document.getElementById("recovery_pass");
    const recoveryPassConfirm =
        document.getElementById("recovery_pass_confirm");

    const username = document.getElementById("username");
    const usernameMessage =
        document.getElementById("usernameMessage");
    const usernameSuggestion =
        document.getElementById("usernameSuggestion");

    const email = document.getElementById("email");
    const emailMessage =
        document.getElementById("emailMessage");

    const mobile = document.getElementById("mobile");
    const mobileMessage =
        document.getElementById("mobileMessage");

    const showPassword =
        document.getElementById("showPassword");


    /* =========================================================
       PASSWORD SHOW / HIDE
       ========================================================= */

    function addPasswordToggle(input) {

        if (!input) return;

        const wrapper = input.parentElement;

        if (!wrapper) return;

        wrapper.style.position = "relative";

        /* Prevent duplicate eye icons */
        if (wrapper.querySelector(".password-toggle-icon")) {
            return;
        }

        input.style.paddingRight = "45px";

        const eye = document.createElement("i");

        eye.className =
            "bi bi-eye-slash password-toggle-icon";

        wrapper.appendChild(eye);

        eye.addEventListener("click", function () {

            if (input.type === "password") {

                input.type = "text";

                eye.className =
                    "bi bi-eye password-toggle-icon";

            } else {

                input.type = "password";

                eye.className =
                    "bi bi-eye-slash password-toggle-icon";

            }

        });

    }


    addPasswordToggle(password);
    addPasswordToggle(confirm);
    addPasswordToggle(recoveryPass);
    addPasswordToggle(recoveryPassConfirm);


    /* =========================================================
       PASSWORD STRENGTH MESSAGE
       ========================================================= */

    let passwordMessage =
        document.getElementById("passwordMessage");


    /*
     * If passwordMessage does not exist in HTML,
     * create it only ONCE.
     */

    if (password && !passwordMessage) {

        passwordMessage =
            document.createElement("div");

        passwordMessage.id =
            "passwordMessage";

        passwordMessage.className =
            "password-message";

        password.parentElement.parentElement
            .insertAdjacentElement(
                "afterend",
                passwordMessage
            );
    }


    function checkPasswordStrength() {

        if (!password || !passwordMessage) {
            return false;
        }

        const pass = password.value;


        /* Nothing typed */

        if (pass.length === 0) {

            passwordMessage.innerHTML = "";

            passwordMessage.className =
                "password-message d-none";

            password.classList.remove(
                "is-valid",
                "is-invalid"
            );

            return false;
        }


        /* Individual requirements */

        const hasLength =
            pass.length >= 8;

        const hasUpper =
            /[A-Z]/.test(pass);

        const hasLower =
            /[a-z]/.test(pass);

        const hasNumber =
            /[0-9]/.test(pass);

        const hasSpecial =
            /[!@#$%^&*]/.test(pass);


        const score =
            Number(hasLength) +
            Number(hasUpper) +
            Number(hasLower) +
            Number(hasNumber) +
            Number(hasSpecial);


        /* =====================================================
           WEAK
           ===================================================== */

        if (
            !hasLength ||
            score <= 2
        ) {

            password.classList.remove("is-valid");

            password.classList.add("is-invalid");

            passwordMessage.className =
                "password-message text-danger";

            passwordMessage.innerHTML =
                '<i class="bi bi-exclamation-circle-fill me-1"></i>' +
                'Weak Password — use at least 8 characters, ' +
                'uppercase, lowercase, number, and special character.';

            return false;
        }


        /* =====================================================
           GOOD
           ===================================================== */

        if (score < 5) {

            password.classList.remove("is-invalid");

            password.classList.add("is-valid");

            passwordMessage.className =
                "password-message text-warning";

            passwordMessage.innerHTML =
                '<i class="bi bi-check-circle-fill me-1"></i>' +
                'Good Password — add the missing requirements ' +
                'to make it Strong.';

            return false;
        }


        /* =====================================================
           STRONG
           ===================================================== */

        password.classList.remove("is-invalid");

        password.classList.add("is-valid");

        passwordMessage.className =
            "password-message text-success";

        passwordMessage.innerHTML =
            '<i class="bi bi-check-circle-fill me-1"></i>' +
            'Strong Password';

        return true;
    }


    if (password) {

        password.addEventListener(
            "input",
            checkPasswordStrength
        );

    }


    /* =========================================================
       CONFIRM PASSWORD
       ========================================================= */

    let confirmMessage =
        document.getElementById("confirmMessage");


    if (confirm && !confirmMessage) {

        confirmMessage =
            document.createElement("div");

        confirmMessage.id =
            "confirmMessage";

        confirmMessage.className =
            "password-message";

        confirm.parentElement.parentElement
            .insertAdjacentElement(
                "afterend",
                confirmMessage
            );
    }


    function checkPasswordMatch() {

        if (!confirm || !confirmMessage) {
            return false;
        }

        if (confirm.value === "") {

            confirm.classList.remove(
                "is-valid",
                "is-invalid"
            );

            confirmMessage.innerHTML = "";

            confirmMessage.className =
                "password-message d-none";

            return false;
        }


        if (password.value === confirm.value) {

            confirm.classList.remove("is-invalid");

            confirm.classList.add("is-valid");

            confirmMessage.className =
                "password-message text-success";

            confirmMessage.innerHTML =
                '<i class="bi bi-check-circle-fill me-1"></i>' +
                'Passwords match';

            return true;

        } else {

            confirm.classList.remove("is-valid");

            confirm.classList.add("is-invalid");

            confirmMessage.className =
                "password-message text-danger";

            confirmMessage.innerHTML =
                '<i class="bi bi-x-circle-fill me-1"></i>' +
                'Passwords do not match';

            return false;
        }

    }


    if (confirm) {

        confirm.addEventListener(
            "input",
            checkPasswordMatch
        );

    }


    if (password) {

        password.addEventListener(
            "input",
            function () {

                if (confirm && confirm.value !== "") {
                    checkPasswordMatch();
                }

            }
        );

    }


    /* =========================================================
       SHOW PASSWORD CHECKBOX
       ========================================================= */

    if (showPassword) {

        showPassword.addEventListener(
            "change",
            function () {

                const type =
                    this.checked
                        ? "text"
                        : "password";


                if (password) {
                    password.type = type;
                }

                if (confirm) {
                    confirm.type = type;
                }

            }
        );

    }


    /* =========================================================
       USERNAME VALIDATION
       ========================================================= */

    if (username) {

        username.addEventListener(
            "input",
            function () {

                this.value =
                    this.value
                        .replace(/\s/g, "")
                        .replace(
                            /[^a-zA-Z0-9_]/g,
                            ""
                        )
                        .toLowerCase();


                const value =
                    this.value.trim();


                if (value === "") {

                    username.classList.remove(
                        "is-valid",
                        "is-invalid"
                    );

                    if (usernameMessage) {

                        usernameMessage.classList.add(
                            "d-none"
                        );

                    }

                    if (usernameSuggestion) {

                        usernameSuggestion.classList.add(
                            "d-none"
                        );

                    }

                    return;
                }


                const validFormat =
                    /^[a-z0-9_]{4,20}$/.test(value);


                if (!validFormat) {

                    username.classList.remove(
                        "is-valid"
                    );

                    username.classList.add(
                        "is-invalid"
                    );


                    if (usernameMessage) {

                        usernameMessage.classList.remove(
                            "d-none"
                        );

                        usernameMessage.innerHTML =
                            "Username must be 4-20 characters " +
                            "and contain only letters, numbers, " +
                            "and underscore.";

                    }


                    if (usernameSuggestion) {

                        usernameSuggestion.classList.add(
                            "d-none"
                        );

                    }

                    return;
                }


                /*
                 * Demo taken username.
                 * Remove this condition when connecting
                 * the AJAX database availability check.
                 */

                if (value === "carmina24") {

                    username.classList.remove(
                        "is-valid"
                    );

                    username.classList.add(
                        "is-invalid"
                    );


                    if (usernameMessage) {

                        usernameMessage.classList.remove(
                            "d-none"
                        );

                        usernameMessage.innerHTML =
                            '<i class="bi bi-exclamation-circle-fill me-1"></i>' +
                            "That username is taken. Try another.";

                    }


                    if (usernameSuggestion) {

                        usernameSuggestion.classList.remove(
                            "d-none"
                        );

                        usernameSuggestion.innerHTML =
                            "Available: <strong>c60836353</strong>";

                    }

                    return;
                }


                /* Valid */

                username.classList.remove(
                    "is-invalid"
                );

                username.classList.add(
                    "is-valid"
                );


                if (usernameMessage) {

                    usernameMessage.classList.add(
                        "d-none"
                    );

                }


                if (usernameSuggestion) {

                    usernameSuggestion.classList.add(
                        "d-none"
                    );

                }

            }
        );

    }


    /* =========================================================
       EMAIL VALIDATION
       ========================================================= */

    const emailRegex =
        /^[A-Za-z0-9._%+-]+@(gmail\.com|yahoo\.com|outlook\.com|hotmail\.com|live\.com|isu\.edu\.ph)$/;


    if (email) {

        email.addEventListener(
            "input",
            function () {

                const value =
                    this.value.trim().toLowerCase();


                if (value === "") {

                    email.classList.remove(
                        "is-valid",
                        "is-invalid"
                    );

                    if (emailMessage) {

                        emailMessage.classList.add(
                            "d-none"
                        );

                    }

                    return;
                }


                if (emailRegex.test(value)) {

                    email.classList.remove(
                        "is-invalid"
                    );

                    email.classList.add(
                        "is-valid"
                    );

                    if (emailMessage) {

                        emailMessage.classList.add(
                            "d-none"
                        );

                    }

                } else {

                    email.classList.remove(
                        "is-valid"
                    );

                    email.classList.add(
                        "is-invalid"
                    );

                    if (emailMessage) {

                        emailMessage.classList.remove(
                            "d-none"
                        );

                        emailMessage.innerHTML =
                            "Please use a valid Gmail, Yahoo, " +
                            "Outlook, Hotmail, Live, or ISU email address.";

                    }

                }

            }
        );

    }


    /* =========================================================
       MOBILE NUMBER
       ========================================================= */

    if (mobile) {

        mobile.addEventListener(
            "input",
            function () {

                this.value =
                    this.value.replace(
                        /\D/g,
                        ""
                    );


                const value =
                    this.value;


                if (value === "") {

                    mobile.classList.remove(
                        "is-valid",
                        "is-invalid"
                    );

                    if (mobileMessage) {

                        mobileMessage.classList.add(
                            "d-none"
                        );

                    }

                    return;
                }


                const valid =
                    /^09\d{9}$/.test(value);


                if (valid) {

                    mobile.classList.remove(
                        "is-invalid"
                    );

                    mobile.classList.add(
                        "is-valid"
                    );

                    if (mobileMessage) {

                        mobileMessage.classList.add(
                            "d-none"
                        );

                    }

                } else {

                    mobile.classList.remove(
                        "is-valid"
                    );

                    mobile.classList.add(
                        "is-invalid"
                    );

                    if (mobileMessage) {

                        mobileMessage.classList.remove(
                            "d-none"
                        );

                        mobileMessage.innerHTML =
                            "Enter a valid Philippine mobile " +
                            "number (09XXXXXXXXX).";

                    }

                }

            }
        );

    }


    /* =========================================================
       RECOVERY PASSWORD MATCH
       ========================================================= */

    let recoveryMatch =
        document.getElementById("recoveryMatch");


    if (recoveryPassConfirm) {

        if (!recoveryMatch) {

            recoveryMatch =
                document.createElement("small");

            recoveryMatch.id =
                "recoveryMatch";

            recoveryMatch.className =
                "password-message";

            recoveryPassConfirm
                .parentElement.parentElement
                .insertAdjacentElement(
                    "afterend",
                    recoveryMatch
                );

        }


        function checkRecoveryMatch() {

            if (recoveryPassConfirm.value === "") {

                recoveryMatch.innerHTML = "";

                recoveryMatch.className =
                    "password-message d-none";

                return false;
            }


            if (
                recoveryPass.value ===
                recoveryPassConfirm.value
            ) {

                recoveryMatch.className =
                    "password-message text-success";

                recoveryMatch.innerHTML =
                    '<i class="bi bi-check-circle-fill me-1"></i>' +
                    "Recovery secrets match";

                return true;

            } else {

                recoveryMatch.className =
                    "password-message text-danger";

                recoveryMatch.innerHTML =
                    '<i class="bi bi-x-circle-fill me-1"></i>' +
                    "Recovery secrets do not match";

                return false;
            }

        }


        recoveryPassConfirm.addEventListener(
            "input",
            checkRecoveryMatch
        );


        if (recoveryPass) {

            recoveryPass.addEventListener(
                "input",
                function () {

                    if (
                        recoveryPassConfirm.value !== ""
                    ) {

                        checkRecoveryMatch();

                    }

                }
            );

        }

    }


    /* =========================================================
       FORM SUBMIT
       ========================================================= */

    if (form) {

        form.addEventListener(
            "submit",
            function (e) {

                let valid = true;


                /* Username */

                if (
                    username &&
                    !/^[a-z0-9_]{4,20}$/.test(
                        username.value
                    )
                ) {

                    e.preventDefault();

                    username.dispatchEvent(
                        new Event("input")
                    );

                    username.focus();

                    valid = false;
                }


                /* Email */

                if (
                    email &&
                    !emailRegex.test(
                        email.value.trim()
                    )
                ) {

                    e.preventDefault();

                    email.dispatchEvent(
                        new Event("input")
                    );

                    email.focus();

                    valid = false;
                }


                /* Mobile */

                if (
                    mobile &&
                    mobile.value !== "" &&
                    !/^09\d{9}$/.test(
                        mobile.value
                    )
                ) {

                    e.preventDefault();

                    mobile.dispatchEvent(
                        new Event("input")
                    );

                    mobile.focus();

                    valid = false;
                }


                /* Password */

                const passwordStrong =
                    checkPasswordStrength();


                if (!passwordStrong) {

                    e.preventDefault();

                    password.focus();

                    valid = false;
                }


                /* Confirm Password */

                const passwordsMatch =
                    checkPasswordMatch();


                if (!passwordsMatch) {

                    e.preventDefault();

                    confirm.focus();

                    valid = false;
                }


                /* Recovery Password */

                if (
                    recoveryPass &&
                    recoveryPassConfirm
                ) {

                    if (
                        recoveryPass.value !==
                        recoveryPassConfirm.value
                    ) {

                        e.preventDefault();

                        checkRecoveryMatch();

                        recoveryPassConfirm.focus();

                        valid = false;
                    }

                }


                return valid;

            }
        );

    }

});