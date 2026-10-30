// Admin product table: lists every product, links to edit and deletes after confirmation.

(() => {
  const LOW_STOCK = 5;
  const container = document.getElementById("admin-products");
  const count = document.getElementById("admin-count");
  const message = document.getElementById("admin-message");
  const dialog = document.getElementById("delete-dialog");
  const deleteName = document.getElementById("delete-name");
  let pendingDeleteId = null;

  function stockLabel(stock) {
    if (stock === 0) return '<span class="stock stock--out">Sold out</span>';
    if (stock <= LOW_STOCK) return `<span class="stock stock--low">${stock} left</span>`;
    return `<span class="stock stock--in">${stock}</span>`;
  }

  function renderRow(product) {
    const name = UI.escapeHtml(product.name);
    return `
      <tr>
        <td class="admin-table__image"><img src="${UI.escapeHtml(UI.url(product.image))}" alt="" loading="lazy"></td>
        <td class="admin-table__name"><a href="${UI.url(`product.html?id=${product.id}`)}">${name}</a></td>
        <td data-label="Category">${UI.escapeHtml(product.category)}</td>
        <td data-label="Price" class="admin-table__price">${UI.formatPrice(product.price)}</td>
        <td data-label="Stock">${stockLabel(product.stock)}</td>
        <td class="admin-table__actions">
          <a class="btn btn--outline btn--small" href="product-form.html?id=${product.id}">Edit<span class="sr-only"> ${name}</span></a>
          <button class="btn btn--danger btn--small" type="button" data-delete="${product.id}">Delete<span class="sr-only"> ${name}</span></button>
        </td>
      </tr>`;
  }

  function render() {
    const products = Store.getAll();
    count.textContent = `${products.length} product${products.length === 1 ? "" : "s"} in the shop`;

    if (products.length === 0) {
      container.innerHTML = `
        <div class="empty-state">
          <h2>No products yet</h2>
          <p>Add your first product, or bring back the sample catalogue.</p>
          <button class="btn btn--outline" id="restore-products" type="button">Restore sample products</button>
        </div>`;
      return;
    }

    container.innerHTML = `
      <div class="table-wrap">
        <table class="table admin-table">
          <thead>
            <tr>
              <th scope="col"><span class="sr-only">Image</span></th>
              <th scope="col">Product</th>
              <th scope="col">Category</th>
              <th scope="col">Price</th>
              <th scope="col">Stock</th>
              <th scope="col"><span class="sr-only">Actions</span></th>
            </tr>
          </thead>
          <tbody>${products.map(renderRow).join("")}</tbody>
        </table>
      </div>`;
  }

  container.addEventListener("click", (event) => {
    const deleteButton = event.target.closest("[data-delete]");
    if (deleteButton) {
      pendingDeleteId = Number(deleteButton.dataset.delete);
      deleteName.textContent = Store.getById(pendingDeleteId).name;
      dialog.showModal();
    }

    if (event.target.closest("#restore-products")) {
      Store.reset();
      UI.showAlert(message, "The sample products were restored.");
      render();
    }
  });

  document.getElementById("delete-cancel").addEventListener("click", () => dialog.close());

  document.getElementById("delete-confirm").addEventListener("click", () => {
    const product = Store.getById(pendingDeleteId);
    dialog.close();

    if (product && Store.remove(pendingDeleteId)) {
      UI.showAlert(message, `${product.name} was deleted.`);
    } else {
      UI.showAlert(message, "This product could not be deleted. It may already be gone.", "error");
    }
    render();
    document.dispatchEvent(new CustomEvent("cart:change"));
  });

  UI.showFlash(message);
  render();
})();
