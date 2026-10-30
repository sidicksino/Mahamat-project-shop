// Product details: reads ?id= from the URL. Shows a message for a missing or unknown id.

(() => {
  const RELATED_COUNT = 4;
  const LOW_STOCK = 5;
  const view = document.getElementById("product-view");
  const id = new URLSearchParams(location.search).get("id");
  const product = id ? Store.getById(id) : null;

  function renderNotFound() {
    document.title = "Product Not Found | FocalPoint";
    view.innerHTML = `
      <div class="empty-state product-missing">
        <h1>Product not found</h1>
        <p>This product may have been removed, or the link may be wrong.</p>
        <a class="btn" href="${UI.url("products.html")}">Back to the shop</a>
      </div>`;
  }

  function stockMessage(stock) {
    if (stock === 0) return '<span class="stock stock--out">Sold out</span>';
    if (stock <= LOW_STOCK) return `<span class="stock stock--low">Only ${stock} left in stock</span>`;
    return `<span class="stock stock--in">In stock (${stock} available)</span>`;
  }

  function purchaseForm() {
    if (product.stock === 0) {
      return '<button class="btn btn--block" type="button" disabled>Sold out</button>';
    }
    return `
      <form class="purchase" id="purchase-form" novalidate>
        <div class="field purchase__quantity">
          <label for="quantity">Quantity</label>
          <input id="quantity" type="number" min="1" max="${product.stock}" step="1" value="1" inputmode="numeric">
        </div>
        <button class="btn btn--accent" type="submit">Add to cart</button>
      </form>`;
  }

  function getRelated() {
    return Store.getAll()
      .filter((item) => item.id !== product.id && item.category === product.category)
      .slice(0, RELATED_COUNT);
  }

  function render() {
    const name = UI.escapeHtml(product.name);
    const category = UI.escapeHtml(product.category);
    const categoryLink = UI.url(`products.html?category=${encodeURIComponent(product.category)}`);
    const related = getRelated();

    document.title = `${product.name} | FocalPoint`;
    view.innerHTML = `
      <nav class="breadcrumb" aria-label="Breadcrumb">
        <a href="${UI.url("products.html")}">Shop</a>
        <span aria-hidden="true">/</span>
        <a href="${categoryLink}">${category}</a>
        <span aria-hidden="true">/</span>
        <span aria-current="page">${name}</span>
      </nav>

      <article class="product-detail">
        <div class="product-detail__media">
          <img src="${UI.escapeHtml(UI.url(product.image))}" alt="${name}">
        </div>
        <div class="product-detail__info">
          <a class="eyebrow" href="${categoryLink}">${category}</a>
          <h1>${name}</h1>
          <p class="product-detail__price">${UI.formatPrice(product.price)}</p>
          ${stockMessage(product.stock)}
          <p class="product-detail__description">${UI.escapeHtml(product.description)}</p>
          ${purchaseForm()}
        </div>
      </article>

      ${related.length ? `
        <section class="related" aria-labelledby="related-heading">
          <div class="section-head">
            <h2 id="related-heading">More in ${category}</h2>
          </div>
          <div class="product-grid">${related.map(UI.productCard).join("")}</div>
        </section>` : ""}`;

    document.getElementById("purchase-form")?.addEventListener("submit", (event) => {
      event.preventDefault();
      const quantity = Number(document.getElementById("quantity").value);
      if (!Number.isInteger(quantity) || quantity < 1) {
        UI.toast("Enter a quantity of 1 or more.", "error");
        return;
      }
      const result = Cart.add(product.id, quantity);
      UI.toast(result.message, result.ok ? "success" : "error");
    });
  }

  if (product) {
    render();
  } else {
    renderNotFound();
  }
})();
