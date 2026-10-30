// Admin login: checks the form, logs in, then returns to the admin page the visitor wanted.

(() => {
  const form = document.getElementById("login-form");
  const message = document.getElementById("login-message");
  const requested = new URLSearchParams(location.search).get("next") ?? "";

  // Only ever redirects to a page inside admin/, never to an address from the URL
  const destination = /^admin\/[\w.-]+\.html(\?[\w=&-]*)?$/.test(requested) ? requested : "admin/index.html";

  function setFieldError(field, text) {
    form.querySelector(`[data-field="${field}"]`).classList.toggle("has-error", Boolean(text));
    const error = document.getElementById(`${field}-error`);
    error.textContent = text ?? "";
    error.hidden = !text;
  }

  if (Auth.isLoggedIn()) {
    location.replace(destination);
    return;
  }

  form.addEventListener("input", (event) => {
    setFieldError(event.target.name, null);
    message.innerHTML = "";
  });

  form.addEventListener("submit", (event) => {
    event.preventDefault();
    message.innerHTML = "";

    const username = form.elements.username.value.trim();
    const password = form.elements.password.value;
    setFieldError("username", username ? null : "Enter your username.");
    setFieldError("password", password ? null : "Enter your password.");

    if (!username || !password) {
      form.querySelector(".has-error input").focus();
      return;
    }

    if (Auth.login(username, password)) {
      location.href = destination;
      return;
    }

    UI.showAlert(message, "The username or password is incorrect.", "error");
    form.elements.password.value = "";
    form.elements.password.focus();
  });

  UI.showFlash(message);
})();
