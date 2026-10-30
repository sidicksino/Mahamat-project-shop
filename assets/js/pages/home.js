// Home page: category tiles and featured products.

(() => {
  const FEATURED_COUNT = 4;
  const products = Store.getAll();
  const categories = Store.getCategories();

  document.getElementById("category-grid").innerHTML = categories
    .map((category) => {
      const count = products.filter((product) => product.category === category.name).length;
      const href = `products.html?category=${encodeURIComponent(category.name)}`;
      return `
        <a class="category-tile" href="${href}">
          <img src="${UI.url(category.image)}" alt="" loading="lazy">
          <span class="category-tile__label">
            <strong>${UI.escapeHtml(category.name)}</strong>
            <span>${count} product${count === 1 ? "" : "s"}</span>
          </span>
        </a>`;
    })
    .join("");

  // One in-stock product per category, then topped up if a category is empty
  const inStock = products.filter((product) => product.stock > 0);
  const featured = categories
    .map((category) => inStock.find((product) => product.category === category.name))
    .filter(Boolean);
  for (const product of inStock) {
    if (featured.length >= FEATURED_COUNT) break;
    if (!featured.includes(product)) featured.push(product);
  }

  const featuredGrid = document.getElementById("featured-products");
  featuredGrid.innerHTML = featured.length
    ? featured.slice(0, FEATURED_COUNT).map(UI.productCard).join("")
    : '<p class="empty-state">New gear is on its way. Please check back soon.</p>';
})();
