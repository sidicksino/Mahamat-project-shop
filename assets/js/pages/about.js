// Contact form: validation and a confirmation only. Nothing is sent or stored until a backend exists.

(() => {
  const form = document.getElementById("contact-form");
  const message = document.getElementById("contact-message");

  function setFieldError(field, text) {
    form.querySelector(`[data-field="${field}"]`).classList.toggle("has-error", Boolean(text));
    const error = document.getElementById(`${field}-error`);
    error.textContent = text ?? "";
    error.hidden = !text;
  }

  function validate({ name, email, body }) {
    return {
      name: name.length < 2 ? "Enter your name." : null,
      email: /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email) ? null : "Enter a valid email address.",
      body: body.length < 10 ? "Enter a message of at least 10 characters." : null,
    };
  }

  form.addEventListener("input", (event) => {
    setFieldError(event.target.name, null);
    message.innerHTML = "";
  });

  form.addEventListener("submit", (event) => {
    event.preventDefault();

    const values = {
      name: form.elements.name.value.trim(),
      email: form.elements.email.value.trim(),
      body: form.elements.body.value.trim(),
    };
    const errors = validate(values);
    Object.entries(errors).forEach(([field, text]) => setFieldError(field, text));

    if (Object.values(errors).some(Boolean)) {
      UI.showAlert(message, "Please fix the highlighted fields and try again.", "error");
      form.querySelector(".has-error input, .has-error textarea").focus();
      return;
    }

    form.reset();
    UI.showAlert(message, `Thanks, ${values.name}. We will reply to ${values.email} soon.`);
  });
})();
