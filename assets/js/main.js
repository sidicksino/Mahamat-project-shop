// Runs on every page: mobile menu, toast messages and clearing field errors while typing.

(() => {
  const CLOSE_ICON = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="M5 5l14 14M19 5 5 19"/></svg>`;
  const toggle = document.getElementById("menu-toggle");
  const nav = document.getElementById("site-nav");
  const menuIcon = toggle.innerHTML;

  function setOpen(open) {
    nav.classList.toggle("is-open", open);
    toggle.setAttribute("aria-expanded", String(open));
    toggle.setAttribute("aria-label", open ? "Close menu" : "Open menu");
    toggle.innerHTML = open ? CLOSE_ICON : menuIcon;
  }

  toggle.addEventListener("click", () => setOpen(!nav.classList.contains("is-open")));
  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape") setOpen(false);
  });

  document.addEventListener("input", (event) => {
    const field = event.target.closest(".field.has-error");
    if (!field) return;
    field.classList.remove("has-error");
    const error = field.querySelector(".field__error");
    if (error) error.hidden = true;
  });

  const toast = document.querySelector(".toast-region");
  if (toast) setTimeout(() => toast.remove(), 5000);
})();
