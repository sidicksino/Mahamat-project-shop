// Cart page: change quantities, remove items and see the total. No checkout, as the brief excludes it.

(() => {
  const view = document.getElementById("cart-view");
  const notices = document.getElementById("cart-notices");

  function renderLine({ product, quantity, lineTotal }) {
    const link = UI.url(`product.html?id=${product.id}`);
    const name = UI.escapeHtml(product.name);

    return `
      <li class="cart-line" data-id="${product.id}">
        <a class="cart-line__image" href="${link}">
          <img src="${UI.escapeHtml(UI.url(product.image))}" alt="${name}">
        </a>
        <div class="cart-line__info">
          <a class="cart-line__name" href="${link}">${name}</a>
          <p class="cart-line__unit">${UI.formatPrice(product.price)} each</p>
        </div>
        <div class="stepper">
          <button class="stepper__btn" type="button" data-step="-1" aria-label="Decrease quantity of ${name}" ${quantity <= 1 ? "disabled" : ""}>&minus;</button>
          <input class="stepper__input" type="number" min="1" max="${product.stock}" step="1" value="${quantity}" inputmode="numeric" aria-label="Quantity of ${name}">
          <button class="stepper__btn" type="button" data-step="1" aria-label="Increase quantity of ${name}" ${quantity >= product.stock ? "disabled" : ""}>+</button>
        </div>
        <p class="cart-line__total">${UI.formatPrice(lineTotal)}</p>
        <button class="cart-line__remove" type="button" data-remove>Remove<span class="sr-only"> ${name}</span></button>
      </li>`;
  }

  function render() {
    const lines = Cart.getLines();

    if (lines.length === 0) {
      view.innerHTML = `
        <div class="empty-state">
          <h2>Your cart is empty</h2>
          <p>Browse the shop to find your next camera, lens or accessory.</p>
          <a class="btn" href="${UI.url("products.html")}">Go to the shop</a>
        </div>`;
      return;
    }

    const count = Cart.count();
    view.innerHTML = `
      <div class="cart">
        <ul class="cart__lines">${lines.map(renderLine).join("")}</ul>
        <aside class="cart-summary" aria-label="Order summary">
          <h2>Summary</h2>
          <p class="cart-summary__row">
            <span>${count} item${count === 1 ? "" : "s"}</span>
            <strong id="cart-total">${UI.formatPrice(Cart.total())}</strong>
          </p>
          <p class="cart-summary__note">Online checkout is not available yet. Visit our Kigali shop to buy these items.</p>
          <a class="btn btn--outline btn--block" href="${UI.url("products.html")}">Continue shopping</a>
          <button class="cart-summary__clear" type="button" id="cart-clear">Clear cart</button>
        </aside>
      </div>`;
  }

  // Rendering replaces the line, so focus returns to the pressed button, or the box if it is now disabled
  function changeQuantity(id, quantity, step) {
    const result = Cart.setQuantity(id, quantity);
    if (!result.ok) UI.toast(result.message, "error");
    render();
    const line = view.querySelector(`[data-id="${id}"]`);
    const button = step && line.querySelector(`[data-step="${step}"]:not(:disabled)`);
    (button || line.querySelector(".stepper__input")).focus();
  }

  view.addEventListener("click", (event) => {
    const line = event.target.closest(".cart-line");

    if (event.target.closest("[data-step]") && line) {
      const button = event.target.closest("[data-step]");
      const input = line.querySelector(".stepper__input");
      changeQuantity(Number(line.dataset.id), Number(input.value) + Number(button.dataset.step), button.dataset.step);
    }

    if (event.target.closest("[data-remove]") && line) {
      const name = Store.getById(line.dataset.id).name;
      Cart.remove(line.dataset.id);
      UI.toast(`${name} removed from your cart.`);
      render();
    }

    if (event.target.closest("#cart-clear")) {
      Cart.clear();
      UI.toast("Your cart is now empty.");
      render();
    }
  });

  view.addEventListener("change", (event) => {
    if (!event.target.matches(".stepper__input")) return;
    const id = Number(event.target.closest(".cart-line").dataset.id);
    changeQuantity(id, Number(event.target.value));
  });

  // Keeps the page right if the cart changes in another tab
  window.addEventListener("storage", render);

  const messages = Cart.reconcile();
  notices.innerHTML = messages
    .map((message) => `<div class="alert alert--error" role="alert">${UI.escapeHtml(message)}</div>`)
    .join("");
  render();
})();
