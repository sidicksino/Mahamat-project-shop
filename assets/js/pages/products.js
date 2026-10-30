// Live search and sorting: fetches the filtered page from PHP and swaps in the results.
// Without JavaScript the same form still works by reloading the page.

(() => {
  const DELAY = 250;
  const form = document.getElementById("shop-toolbar");
  const searchInput = document.getElementById("shop-search");
  const sortSelect = document.getElementById("shop-sort");
  const area = document.getElementById("shop-area");
  const chips = document.getElementById("shop-categories");
  const headerSearch = document.getElementById("site-search-input");
  let timer = null;
  let latestRequest = 0;

  function currentUrl() {
    const params = new URLSearchParams();
    for (const [key, value] of new FormData(form)) {
      if (value && value !== "default") params.set(key, value);
    }
    const query = params.toString();
    return query ? `${location.pathname}?${query}` : location.pathname;
  }

  async function update() {
    const request = ++latestRequest;
    const url = currentUrl();

    try {
      const response = await fetch(url);
      if (!response.ok) throw new Error(`Status ${response.status}`);
      const page = new DOMParser().parseFromString(await response.text(), "text/html");
      if (request !== latestRequest) return;

      area.innerHTML = page.getElementById("shop-area").innerHTML;
      chips.innerHTML = page.getElementById("shop-categories").innerHTML;
      history.replaceState(null, "", url);
    } catch {
      location.href = url;
    }
  }

  searchInput.addEventListener("input", () => {
    headerSearch.value = searchInput.value;
    clearTimeout(timer);
    timer = setTimeout(update, DELAY);
  });

  sortSelect.addEventListener("change", update);
})();
