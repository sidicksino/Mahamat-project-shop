// Shared helpers: paths, formatting, toasts and the product card.

const UI = (() => {
  // Pages in admin/ set data-root="../" so links and images resolve from any folder
  const root = document.body.dataset.root ?? "";

  function url(path) {
    return /^(data:|https?:)/.test(path) ? path : root + path;
  }

  function escapeHtml(value) {
    const escapes = { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" };
    return String(value).replace(/[&<>"']/g, (char) => escapes[char]);
  }

  function formatPrice(amount) {
    return `RWF ${Number(amount).toLocaleString("en-US")}`;
  }

  function toast(message, type = "success") {
    let region = document.querySelector(".toast-region");
    if (!region) {
      region = document.createElement("div");
      region.className = "toast-region";
      region.setAttribute("role", "status");
      region.setAttribute("aria-live", "polite");
      document.body.append(region);
    }

    const item = document.createElement("div");
    item.className = `toast toast--${type}`;
    item.textContent = message;
    region.append(item);
    setTimeout(() => item.remove(), 3500);
  }

  // A message that survives a redirect, then shows once on the next page
  function setFlash(message, type = "success") {
    sessionStorage.setItem("focalpoint.flash", JSON.stringify({ message, type }));
  }

  function showAlert(container, message, type = "success") {
    const role = type === "error" ? "alert" : "status";
    container.innerHTML = `<div class="alert alert--${type}" role="${role}">${escapeHtml(message)}</div>`;
  }

  function showFlash(container) {
    const saved = sessionStorage.getItem("focalpoint.flash");
    if (!saved) return;
    sessionStorage.removeItem("focalpoint.flash");
    const { message, type } = JSON.parse(saved);
    showAlert(container, message, type);
  }

  function productCard(product) {
    const soldOut = product.stock === 0;
    const link = url(`product.html?id=${product.id}`);

    return `
      <article class="product-card">
        <a class="product-card__link" href="${link}">
          <div class="product-card__media">
            <img src="${escapeHtml(url(product.image))}" alt="${escapeHtml(product.name)}" loading="lazy">
            ${soldOut ? '<span class="product-card__badge">Sold out</span>' : ""}
          </div>
          <div class="product-card__body">
            <span class="eyebrow">${escapeHtml(product.category)}</span>
            <h3 class="product-card__name">${escapeHtml(product.name)}</h3>
            <p class="product-card__price">${formatPrice(product.price)}</p>
          </div>
        </a>
        <div class="product-card__action">
          <button class="btn btn--block" type="button" data-add-to-cart="${product.id}" ${soldOut ? "disabled" : ""}>
            ${soldOut ? "Sold out" : "Add to cart"}
          </button>
        </div>
      </article>`;
  }

  // Works for every card on the page, including ones rendered later
  document.addEventListener("click", (event) => {
    const button = event.target.closest("[data-add-to-cart]");
    if (!button) return;
    const result = Cart.add(button.dataset.addToCart);
    toast(result.message, result.ok ? "success" : "error");
  });

  return { url, escapeHtml, formatPrice, toast, setFlash, showAlert, showFlash, productCard };
})();
