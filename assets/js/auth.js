// Mock login and admin page guard. Not real security: anything in the browser can be read or changed.
// Phase 2 replaces this with password_hash, password_verify and PHP sessions.

const Auth = (() => {
  const SESSION_KEY = "focalpoint.admin";
  const DEMO_USERNAME = "admin";
  const DEMO_PASSWORD = "admin123";
  const root = document.body.dataset.root ?? "";

  function isLoggedIn() {
    return localStorage.getItem(SESSION_KEY) === DEMO_USERNAME;
  }

  // Sends visitors to login from admin pages, remembering where they were going
  function guardAdminPages() {
    if (document.body.dataset.page !== "admin" || isLoggedIn()) return;
    const page = location.pathname.split("/").pop();
    const next = encodeURIComponent(`admin/${page}${location.search}`);
    location.replace(`${root}login.html?next=${next}`);
  }

  guardAdminPages();
  // The back button can restore an admin page from memory after logging out
  window.addEventListener("pageshow", (event) => {
    if (event.persisted) guardAdminPages();
  });

  return {
    isLoggedIn,

    login(username, password) {
      if (username !== DEMO_USERNAME || password !== DEMO_PASSWORD) return false;
      localStorage.setItem(SESSION_KEY, DEMO_USERNAME);
      return true;
    },

    logout() {
      localStorage.removeItem(SESSION_KEY);
    },
  };
})();
