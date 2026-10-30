// Saves a typed quantity as soon as the box loses focus or Enter is pressed.

document.querySelectorAll(".stepper__input").forEach((input) => {
  input.addEventListener("change", () => input.form.submit());
});
