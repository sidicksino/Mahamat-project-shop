// Product list: live search, category filter and sorting. The filters are kept in the URL.

(() => {
  const products = Store.getAll();
  const categoryNames = Store.getCategories().map((category) => category.name);
  const params = new URLSearchParams(location.search);

  const searchInput = document.getElementById("shop-search");
  const sortSelect = document.getElementById("shop-sort");
  const chips = document.getElementById("shop-categories");
  const results = document.getElementById("shop-results");
  const count = document.getElementById("shop-count");
  const empty = document.getElementById("shop-empty");
  const headerSearch = document.getElementById("site-search-input");

  const SORTERS = {
    "price-asc": (a, b) => a.price - b.price,
    "price-desc": (a, b) => b.price - a.price,
    name: (a, b) => a.name.localeCompare(b.name),
  };

  const state = {
    q: params.get("q") ?? "",
    category: categoryNames.includes(params.get("category")) ? params.get("category") : "",
    sort: params.get("sort") in SORTERS ? params.get("sort") : "default",
  };

  function matchesSearch(product, query) {
    const text = `${product.name} ${product.category} ${product.description}`.toLowerCase();
    return query.toLowerCase().split(/\s+/).filter(Boolean).every((word) => text.includes(word));
  }

  function matchesName(product, query) {
    const name = product.name.toLowerCase();
    return query.toLowerCase().split(/\s+/).filter(Boolean).every((word) => name.includes(word));
  }

  function getVisibleProducts() {
    const visible = products.filter(
      (product) =>
        (!state.category || product.category === state.category) && matchesSearch(product, state.q)
    );
    if (SORTERS[state.sort]) return visible.sort(SORTERS[state.sort]);

    // Featured order: products named after the search come before ones that only mention it
    const nameMatches = (product) => (matchesName(product, state.q) ? 0 : 1);
    return visible.sort((a, b) => nameMatches(a) - nameMatches(b));
  }

  function renderChips() {
    chips.innerHTML = ["", ...categoryNames]
      .map((name) => {
        const pressed = name === state.category;
        return `<button class="chip" type="button" data-category="${UI.escapeHtml(name)}" aria-pressed="${pressed}">${UI.escapeHtml(name || "All")}</button>`;
      })
      .join("");
  }

  // Lets the back button return to the same filtered view from a product page
  function updateUrl() {
    const next = new URLSearchParams();
    if (state.q) next.set("q", state.q);
    if (state.category) next.set("category", state.category);
    if (state.sort !== "default") next.set("sort", state.sort);
    const query = next.toString();
    try {
      history.replaceState(null, "", query ? `?${query}` : location.pathname);
    } catch {
      // Some browsers block this on file:// pages; filtering still works
    }
  }

  function render() {
    const visible = getVisibleProducts();
    results.innerHTML = visible.map(UI.productCard).join("");
    results.hidden = visible.length === 0;
    empty.hidden = visible.length !== 0;
    count.textContent = `${visible.length} product${visible.length === 1 ? "" : "s"}`;
    renderChips();
    updateUrl();
  }

  searchInput.addEventListener("input", () => {
    state.q = searchInput.value;
    headerSearch.value = state.q;
    render();
  });

  sortSelect.addEventListener("change", () => {
    state.sort = sortSelect.value;
    render();
  });

  chips.addEventListener("click", (event) => {
    const chip = event.target.closest("[data-category]");
    if (!chip) return;
    state.category = chip.dataset.category;
    render();
  });

  document.getElementById("shop-toolbar").addEventListener("submit", (event) => event.preventDefault());

  document.getElementById("shop-clear").addEventListener("click", () => {
    state.q = "";
    state.category = "";
    searchInput.value = "";
    headerSearch.value = "";
    render();
    searchInput.focus();
  });

  searchInput.value = state.q;
  headerSearch.value = state.q;
  sortSelect.value = state.sort;
  render();
})();
