// ============================
// Password Show / Hide
// ============================

const password = document.getElementById("password");
const confirm = document.getElementById("confirm");
const recoveryPass = document.getElementById("recovery_pass");
const recoveryPassConfirm = document.getElementById("recovery_pass_confirm");

function addToggle(inputElement) {
    if (inputElement) {
        const wrapper = inputElement.parentElement;
        const eye = document.createElement("i");
        eye.className = "bi bi-eye-slash password-toggle-icon";
        
        wrapper.appendChild(eye);

        eye.onclick = function() {
            if (inputElement.type === "password") {
                inputElement.type = "text";
                eye.className = "bi bi-eye password-toggle-icon text-dark";
            } else {
                inputElement.type = "password";
                eye.className = "bi bi-eye-slash password-toggle-icon";
            }
        }
    }
}

addToggle(password);
addToggle(confirm);
addToggle(recoveryPass);
addToggle(recoveryPassConfirm);

// ============================
// Password Strength
// ============================

const strength = document.createElement("small");
strength.style.display = "block";
strength.style.marginTop = "4px";
strength.style.fontSize = "12px";
strength.style.fontWeight = "600";
strength.style.paddingLeft = "5px";

if (password) {
    // insert strength message after the input wrapper
    password.parentElement.insertAdjacentElement("afterend", strength);

    password.addEventListener("keyup", function() {
        let pass = password.value;
        let score = 0;

        if (pass.length >= 8) score++;
        if (/[A-Z]/.test(pass)) score++;
        if (/[a-z]/.test(pass)) score++;
        if (/[0-9]/.test(pass)) score++;
        if (/[!@#$%^&*]/.test(pass)) score++;

        if (pass.length === 0) {
            strength.innerHTML = "";
            return;
        }

        switch(score) {
            case 0:
            case 1:
                strength.innerHTML = "Weak Password";
                strength.style.color = "red";
                break;
            case 2:
                strength.innerHTML = "Fair Password";
                strength.style.color = "orange";
                break;
            case 3:
                strength.innerHTML = "Good Password";
                strength.style.color = "#d4a000";
                break;
            case 4:
                strength.innerHTML = "Strong Password";
                strength.style.color = "#198754";
                break;
            case 5:
                strength.innerHTML = "Very Strong Password";
                strength.style.color = "#065f2f";
                break;
        }
    });
}

// ============================
// Password Match
// ============================

const match = document.createElement("small");
match.style.display = "block";
match.style.marginTop = "4px";
match.style.fontSize = "12px";
match.style.fontWeight = "600";
match.style.paddingLeft = "5px";

if (confirm) {
    // insert match message after the confirm input wrapper
    confirm.parentElement.insertAdjacentElement("afterend", match);

    const validatePasswordMatch = () => {
        // Only show a message if the user has started typing in the confirm field.
        if (confirm.value !== "") {
            if (password.value === confirm.value) {
                match.innerHTML = "✔ Passwords match";
                match.style.color = "#198754";
            } else {
                match.innerHTML = "✖ Passwords do not match";
                match.style.color = "red";
            }
        } else {
            // If the confirm field is empty, don't show any message.
            match.innerHTML = "";
        }
    };

    if (password) {
        password.addEventListener("keyup", validatePasswordMatch);
    }
    confirm.addEventListener("keyup", validatePasswordMatch);
}

// ============================
// Recovery Secret Password Match
// ============================

const recoveryMatch = document.createElement("small");
recoveryMatch.style.display = "block";
recoveryMatch.style.marginTop = "4px";
recoveryMatch.style.fontSize = "12px";
recoveryMatch.style.fontWeight = "600";
recoveryMatch.style.paddingLeft = "5px";

if (recoveryPassConfirm) {
    recoveryPassConfirm.parentElement.insertAdjacentElement("afterend", recoveryMatch);

    const validateRecoveryMatch = () => {
        // Only show a message if the user has started typing in the confirm field.
        if (recoveryPassConfirm.value !== "") {
            if (recoveryPass.value === recoveryPassConfirm.value) {
                recoveryMatch.innerHTML = "✔ Recovery secrets match";
                recoveryMatch.style.color = "#198754";
            } else {
                recoveryMatch.innerHTML = "✖ Recovery secrets do not match";
                recoveryMatch.style.color = "red";
            }
        } else {
            recoveryMatch.innerHTML = "";
        }
    };

    if (recoveryPass) {
        recoveryPass.addEventListener("keyup", validateRecoveryMatch);
    }
    recoveryPassConfirm.addEventListener("keyup", validateRecoveryMatch);
}

// ============================
// Form Validation
// ============================

// ============================
// Mobile Number Validation
// ============================
const mobileInput = document.getElementById("mobile");
const mobileMatch = document.createElement("small");
mobileMatch.style.display = "block";
mobileMatch.style.marginTop = "4px";
mobileMatch.style.fontSize = "12px";
mobileMatch.style.fontWeight = "600";
mobileMatch.style.paddingLeft = "5px";

if (mobileInput) {
    // Insert the validation message below the input wrapper
    mobileInput.parentElement.insertAdjacentElement("afterend", mobileMatch);

    mobileInput.addEventListener("input", function() {
        // Automatically remove any letter or special character except '+'
        this.value = this.value.replace(/[^\d+]/g, '');

        let val = this.value;
        
        // If empty (since it's optional), remove validation text
        if (val === "") {
            mobileMatch.innerHTML = "";
            return;
        }

        // Regex pattern specifically for 09xxxxxxxxx or +639xxxxxxxxx
        const phRegex = /^(09|\+639)\d{9}$/;

        if (phRegex.test(val)) {
            mobileMatch.innerHTML = "✔ Valid format";
            mobileMatch.style.color = "#198754"; // Green
        } else {
            mobileMatch.innerHTML = "✖ Format must be 09... or +639...";
            mobileMatch.style.color = "red";
        }
    });
}

// ============================
// Form Validation
// ============================

const form = document.querySelector("form");
if (form) {
    form.addEventListener("submit", function(e) {
        // Mobile format check (only if they typed something)
        const mobileVal = mobileInput ? mobileInput.value : "";
        if (mobileVal !== "" && !/^(09|\+639)\d{9}$/.test(mobileVal)) {
            e.preventDefault();
            alert("Please enter a valid mobile format (e.g. 09066794925 or +639066794925).");
            mobileInput.focus();
            return; // Stop the script here
        }

        // Password match check
        if (password.value !== confirm.value) {
            e.preventDefault();
            alert("Passwords do not match. Please ensure both fields are identical.");
            confirm.focus();
            return;
        }

        // Recovery secret match check (if Super Admin)
        if (recoveryPass && recoveryPass.value !== "") {
            if (recoveryPass.value !== recoveryPassConfirm.value) {
                e.preventDefault();
                alert("Recovery secrets do not match. Please ensure both fields are identical.");
                recoveryPassConfirm.focus();
                return;
            }
        }
    });
}