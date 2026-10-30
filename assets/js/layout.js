// Injects the shared header and footer. Becomes includes/header.php and includes/footer.php in Phase 2.

(() => {
  const SITE_NAME = "FocalPoint";

  const NAV_LINKS = [
    { page: "home", label: "Home", href: "index.html" },
    { page: "products", label: "Shop", href: "products.html" },
    { page: "about", label: "About", href: "about.html" },
    { page: "admin", label: "Admin", href: "admin/index.html" },
  ];

  const ICONS = {
    logo: `<svg viewBox="0 0 32 32" aria-hidden="true"><circle cx="16" cy="16" r="14.5" fill="none" stroke="currentColor" stroke-width="3"/><path d="M16 1.5 21 13M28.6 8.8 19 16.2M28.6 23.2 17.2 21.6M16 30.5 11 19M3.4 23.2 13 15.8M3.4 8.8 14.8 10.4" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="16" cy="16" r="3.2" fill="#c23a24"/></svg>`,
    cart: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 7h15l-1.5 9h-12z"/><path d="M6 7 5 3H2"/><circle cx="9" cy="20" r="1.3"/><circle cx="18" cy="20" r="1.3"/></svg>`,
    menu: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="M3 6h18M3 12h18M3 18h18"/></svg>`,
    close: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="M5 5l14 14M19 5 5 19"/></svg>`,
    search: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg>`,
  };

  function renderHeader(currentPage) {
    const activePage = currentPage === "login" ? "admin" : currentPage;
    const links = NAV_LINKS.map((link) => {
      const active = link.page === activePage;
      return `<li><a class="site-nav__link${active ? " is-active" : ""}" href="${UI.url(link.href)}"${active ? ' aria-current="page"' : ""}>${link.label}</a></li>`;
    }).join("");
    const logout = Auth.isLoggedIn()
      ? '<li><button class="site-nav__link site-nav__logout" id="logout" type="button">Log out</button></li>'
      : "";

    return `
      <a class="skip-link" href="#main-content">Skip to content</a>
      <header class="site-header">
        <div class="container site-header__inner">
          <a class="logo" href="${UI.url("index.html")}">${ICONS.logo}<span>${SITE_NAME}</span></a>
          <nav class="site-nav" id="site-nav" aria-label="Main">
            <ul>${links}${logout}</ul>
            <form class="site-search" action="${UI.url("products.html")}" method="get" role="search">
              <label class="sr-only" for="site-search-input">Search products</label>
              <input id="site-search-input" type="search" name="q" placeholder="Search cameras, lenses...">
              <button type="submit" aria-label="Search">${ICONS.search}</button>
            </form>
          </nav>
          <div class="site-header__actions">
            <a class="icon-btn" href="${UI.url("cart.html")}" id="cart-link"${currentPage === "cart" ? ' aria-current="page"' : ""}>
              ${ICONS.cart}
              <span class="cart-count" id="cart-count" hidden></span>
            </a>
            <button class="icon-btn menu-toggle" id="menu-toggle" type="button" aria-expanded="false" aria-controls="site-nav" aria-label="Open menu">
              ${ICONS.menu}
            </button>
          </div>
        </div>
      </header>`;
  }

  function renderFooter() {
    const categories = Store.getCategories()
      .map((category) => {
        const href = UI.url(`products.html?category=${encodeURIComponent(category.name)}`);
        return `<li><a href="${href}">${UI.escapeHtml(category.name)}</a></li>`;
      })
      .join("");

    return `
      <footer class="site-footer">
        <div class="container">
          <div class="site-footer__grid">
            <div>
              <a class="logo" href="${UI.url("index.html")}">${ICONS.logo}<span>${SITE_NAME}</span></a>
              <p>Cameras, lenses and studio gear for photographers in Kigali and across Rwanda.</p>
            </div>
            <div>
              <h3>Shop</h3>
              <ul>${categories}</ul>
            </div>
            <div>
              <h3>Visit</h3>
              <ul>
                <li><a href="${UI.url("about.html")}">About and contact</a></li>
                <li>Kigali, Rwanda</li>
                <li>Mon to Sat, 8am to 6pm</li>
              </ul>
            </div>
          </div>
          <div class="site-footer__bottom">
            <p>&copy; 2026 ${SITE_NAME}. Web Technology CAT project, Reg. No. 27578/2024.</p>
          </div>
        </div>
      </footer>`;
  }

  function updateCartCount() {
    const count = Cart.count();
    const badge = document.getElementById("cart-count");
    badge.textContent = count;
    badge.hidden = count === 0;
    document.getElementById("cart-link").setAttribute("aria-label", `Cart, ${count} item${count === 1 ? "" : "s"}`);
  }

  function setUpMenu() {
    const toggle = document.getElementById("menu-toggle");
    const nav = document.getElementById("site-nav");

    function setOpen(open) {
      nav.classList.toggle("is-open", open);
      toggle.setAttribute("aria-expanded", String(open));
      toggle.setAttribute("aria-label", open ? "Close menu" : "Open menu");
      toggle.innerHTML = open ? ICONS.close : ICONS.menu;
    }

    toggle.addEventListener("click", () => setOpen(!nav.classList.contains("is-open")));
    document.addEventListener("keydown", (event) => {
      if (event.key === "Escape") setOpen(false);
    });
  }

  document.getElementById("site-header").innerHTML = renderHeader(document.body.dataset.page);
  document.getElementById("site-footer").innerHTML = renderFooter();
  document.querySelector("main").id = "main-content";

  document.getElementById("logout")?.addEventListener("click", () => {
    Auth.logout();
    UI.setFlash("You have been logged out.");
    location.href = UI.url("login.html");
  });

  setUpMenu();
  updateCartCount();
  document.addEventListener("cart:change", updateCartCount);
  window.addEventListener("storage", updateCartCount);
})();
