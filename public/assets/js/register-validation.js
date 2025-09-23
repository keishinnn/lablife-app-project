// file path = public/assets/js/form-loading.js
import { setLoadingState } from "./utils.js";

document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("register-form");
    const emailInput = document.getElementById("email");
    const passwordInput = document.getElementById("password");
    const formError = document.getElementById("form-error");
    const signUpBtn = document.getElementById("sign-up-btn");

    form.addEventListener("submit", function (e) {
        e.preventDefault();

        let errorMsg = "";

        const captchaResponse = document.querySelector("[name='cf-turnstile-response']");

        if (!emailInput.value.trim()) {
            errorMsg = "Email is required.";
        } else if (passwordInput.value.length < 8) {
            errorMsg = "Password must be at least 8 characters long.";
        } else if (!captchaResponse || !captchaResponse.value.trim()) {
            errorMsg = "Please complete the captcha.";
        }

        if (errorMsg !== "") {
            formError.textContent = errorMsg;
            formError.style.display = "block";
            setLoadingState(signUpBtn, false, "Sign Up");
            return;
        }

        formError.style.display = "none";
        setLoadingState(signUpBtn, true);

        form.submit();
    });

});
