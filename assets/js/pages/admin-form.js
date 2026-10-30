// Checks a chosen image before upload and shows a preview. PHP checks the file again on the server.

(() => {
  const MAX_FILE_SIZE = 5 * 1024 * 1024;
  const ALLOWED_TYPES = ["image/jpeg", "image/png", "image/webp"];

  const imageInput = document.getElementById("image");
  if (!imageInput) return;

  const preview = document.getElementById("image-preview");
  const error = document.getElementById("image-error");
  const field = imageInput.closest(".field");

  function showError(text) {
    imageInput.value = "";
    field.classList.add("has-error");
    error.textContent = text;
    error.hidden = false;
  }

  imageInput.addEventListener("change", () => {
    const file = imageInput.files[0];
    if (!file) return;

    if (!ALLOWED_TYPES.includes(file.type)) {
      showError("Choose a JPG, PNG or WebP image.");
      return;
    }
    if (file.size > MAX_FILE_SIZE) {
      showError("This image is larger than 5 MB. Choose a smaller one.");
      return;
    }

    const picture = new Image();
    picture.alt = "Selected product image";
    picture.onerror = () => showError("This file could not be read as an image. Choose another.");
    picture.src = URL.createObjectURL(file);
    preview.replaceChildren(picture);
  });
})();
