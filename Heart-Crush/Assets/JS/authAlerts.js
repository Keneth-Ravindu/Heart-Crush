document.addEventListener("DOMContentLoaded", function () {

if (typeof window.hcAlertType === "undefined") return;

const alertType = window.hcAlertType;
const alertHtml = window.hcAlertHtml;
const isRegister = window.hcIsRegister === true;
const redirectToLogin = window.hcRedirectToLogin === true;

const customClass = isRegister
    ? {
        popup: 'swal2-hc-register',
        title: 'swal2-hc-register-title',
        htmlContainer: 'swal2-hc-register-text'
    }
    : {
        popup: 'swal2-hc-login',
        title: 'swal2-hc-login-title',
        htmlContainer: 'swal2-hc-login-text'
    };

Swal.fire({
    title: isRegister
        ? (alertType === "success" ? "Welcome to HeartCrush ♥" : "Oops...")
        : "Oops...",
    html: alertHtml,
    icon: alertType,
    confirmButtonText: "OK",
    background: "transparent",
    color: "#f9fafb",
    customClass,
    allowOutsideClick: false,
    allowEscapeKey: false
}).then(() => {

    
    if (isRegister && redirectToLogin) {
        window.location.href = "login.php";
        return;
    }

    
    if (!isRegister) {
        document.body.classList.remove("swal2-shown", "swal2-height-auto");
        const containers = document.querySelectorAll(".swal2-container");
        containers.forEach(c => c.remove());
    }
});
});
