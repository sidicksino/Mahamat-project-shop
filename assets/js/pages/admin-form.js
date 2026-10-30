// Add and edit form. ?id=3 means edit. Validation rules live in Store, so they apply to both modes.

(() => {
  const MAX_FILE_SIZE = 5 * 1024 * 1024;
  const MAX_IMAGE_WIDTH = 800;
  const ALLOWED_TYPES = ["image/jpeg", "image/png", "image/webp"];

  const form = document.getElementById("product-form");
  const message = document.getElementById("form-message");
  const submit = document.getElementById("form-submit");
  const imageInput = document.getElementById("image");
  const preview = document.getElementById("image-preview");
  const categorySelect = document.getElementById("category");

  const id = new URLSearchParams(location.search).get("id");
  const isEdit = id !== null;
  const product = isEdit ? Store.getById(id) : null;
  let image = product ? product.image : "";

  function setFieldError(field, text) {
    const wrapper = form.querySelector(`[data-field="${field}"]`);
    const error = document.getElementById(`${field}-error`);
    wrapper.classList.toggle("has-error", Boolean(text));
    error.textContent = text ?? "";
    error.hidden = !text;
  }

  function showPreview(src) {
    preview.innerHTML = `<img src="${UI.escapeHtml(UI.url(src))}" alt="Selected product image">`;
  }

  // Shrinks uploads so they fit in browser storage. Phase 2 saves the real file on the server.
  function resizeImage(file) {
    return new Promise((resolve, reject) => {
      const reader = new FileReader();
      reader.onerror = reject;
      reader.onload = () => {
        const picture = new Image();
        picture.onerror = reject;
        picture.onload = () => {
          const scale = Math.min(1, MAX_IMAGE_WIDTH / picture.width);
          const canvas = document.createElement("canvas");
          canvas.width = Math.round(picture.width * scale);
          canvas.height = Math.round(picture.height * scale);
          const context = canvas.getContext("2d");
          context.fillStyle = "#e6e0d7";
          context.fillRect(0, 0, canvas.width, canvas.height);
          context.drawImage(picture, 0, 0, canvas.width, canvas.height);
          resolve(canvas.toDataURL("image/jpeg", 0.82));
        };
        picture.src = reader.result;
      };
      reader.readAsDataURL(file);
    });
  }

  imageInput.addEventListener("change", async () => {
    const file = imageInput.files[0];
    if (!file) return;

    if (!ALLOWED_TYPES.includes(file.type)) {
      imageInput.value = "";
      setFieldError("image", "Choose a JPG, PNG or WebP image.");
      return;
    }
    if (file.size > MAX_FILE_SIZE) {
      imageInput.value = "";
      setFieldError("image", "This image is larger than 5 MB. Choose a smaller one.");
      return;
    }

    submit.disabled = true;
    try {
      image = await resizeImage(file);
      showPreview(image);
      setFieldError("image", null);
      message.innerHTML = "";
    } catch {
      imageInput.value = "";
      setFieldError("image", "This file could not be read as an image. Choose another.");
    } finally {
      submit.disabled = false;
    }
  });

  form.addEventListener("input", (event) => {
    if (event.target.name) setFieldError(event.target.name, null);
    message.innerHTML = "";
  });

  form.addEventListener("submit", (event) => {
    event.preventDefault();
    message.innerHTML = "";

    const input = { ...Object.fromEntries(new FormData(form)), image };
    const result = isEdit ? Store.update(id, input) : Store.create(input);

    for (const field of ["name", "category", "price", "stock", "description", "image"]) {
      setFieldError(field, result.ok ? null : result.errors[field]);
    }

    if (result.ok) {
      UI.setFlash(`${result.product.name} was ${isEdit ? "updated" : "added"}.`);
      location.href = "index.html";
      return;
    }

    UI.showAlert(message, result.errors.form ?? "Please fix the highlighted fields and try again.", "error");
    form.querySelector(".has-error input, .has-error select, .has-error textarea")?.focus();
  });

  categorySelect.insertAdjacentHTML(
    "beforeend",
    Store.getCategories()
      .map((category) => `<option value="${UI.escapeHtml(category.name)}">${UI.escapeHtml(category.name)}</option>`)
      .join("")
  );

  if (isEdit && !product) {
    document.title = "Product Not Found | FocalPoint";
    document.getElementById("form-title").textContent = "Edit product";
    form.hidden = true;
    document.getElementById("form-missing").hidden = false;
  } else if (isEdit) {
    document.title = `Edit ${product.name} | FocalPoint`;
    document.getElementById("form-title").textContent = "Edit product";
    submit.textContent = "Save changes";
    for (const field of ["name", "category", "price", "stock", "description"]) {
      form.elements[field].value = product[field];
    }
    showPreview(product.image);
  } else {
    document.title = "Add Product | FocalPoint";
  }
})();
