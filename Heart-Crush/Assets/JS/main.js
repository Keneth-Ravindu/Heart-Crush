// Simple front-end confirm password check
document.addEventListener('DOMContentLoaded', () => {
  const form = document.querySelector('form');
  const pwd  = document.getElementById('password');
  const cpw  = document.getElementById('confirmPassword');

  // Only run on pages that actually have both password + confirm fields (register page)
  if (!form || !pwd || !cpw) return;

  form.addEventListener('submit', (e) => {
    if (pwd.value !== cpw.value) {
      e.preventDefault();
      cpw.focus();
      cpw.setCustomValidity("Passwords do not match");
      cpw.reportValidity();
      setTimeout(() => cpw.setCustomValidity(""), 2000);
    }
  });
});
