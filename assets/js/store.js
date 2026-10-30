// Data layer. Pages read and change products only through Store, never data.js or localStorage.
// Write methods return { ok: true, product } or { ok: false, errors: { field: message } }.

const Store = (() => {
  const PRODUCTS_KEY = "focalpoint.products";
  const NEXT_ID_KEY = "focalpoint.nextId";

  function seed() {
    const nextId = Math.max(...MOCK_PRODUCTS.map((product) => product.id)) + 1;
    localStorage.setItem(PRODUCTS_KEY, JSON.stringify(MOCK_PRODUCTS));
    localStorage.setItem(NEXT_ID_KEY, String(nextId));
    return structuredClone(MOCK_PRODUCTS);
  }

  function load() {
    const saved = localStorage.getItem(PRODUCTS_KEY);
    if (saved === null) return seed();
    try {
      return JSON.parse(saved);
    } catch {
      return seed();
    }
  }

  function save(products) {
    localStorage.setItem(PRODUCTS_KEY, JSON.stringify(products));
  }

  function validate(input) {
    const errors = {};
    const name = String(input.name ?? "").trim();
    const description = String(input.description ?? "").trim();
    const price = Number(input.price);
    const stock = Number(input.stock);
    const isCategory = MOCK_CATEGORIES.some((category) => category.name === input.category);

    if (name.length < 2) errors.name = "Enter a product name of at least 2 characters.";
    if (!isCategory) errors.category = "Choose a category.";
    if (input.price === "" || !Number.isInteger(price) || price <= 0) {
      errors.price = "Enter a price in whole Rwandan francs, above 0.";
    }
    if (input.stock === "" || !Number.isInteger(stock) || stock < 0) {
      errors.stock = "Enter a stock quantity of 0 or more.";
    }
    if (description.length < 10) errors.description = "Enter a description of at least 10 characters.";
    if (!input.image) errors.image = "Add a product image.";

    return errors;
  }

  function clean(input) {
    return {
      name: String(input.name).trim(),
      category: input.category,
      price: Number(input.price),
      stock: Number(input.stock),
      description: String(input.description).trim(),
      image: input.image,
    };
  }

  function commit(products, product) {
    try {
      save(products);
      return { ok: true, product };
    } catch {
      return { ok: false, errors: { form: "The browser storage is full. Use a smaller image." } };
    }
  }

  return {
    getAll() {
      return load();
    },

    getById(id) {
      return load().find((product) => product.id === Number(id)) ?? null;
    },

    getCategories() {
      return structuredClone(MOCK_CATEGORIES);
    },

    create(input) {
      const errors = validate(input);
      if (Object.keys(errors).length > 0) return { ok: false, errors };

      const products = load();
      const id = Number(localStorage.getItem(NEXT_ID_KEY));
      const product = { id, ...clean(input), created_at: new Date().toISOString().slice(0, 10) };
      const result = commit([...products, product], product);
      if (result.ok) localStorage.setItem(NEXT_ID_KEY, String(id + 1));
      return result;
    },

    update(id, input) {
      const errors = validate(input);
      if (Object.keys(errors).length > 0) return { ok: false, errors };

      const products = load();
      const index = products.findIndex((product) => product.id === Number(id));
      if (index === -1) return { ok: false, errors: { form: "This product no longer exists." } };

      const product = { ...products[index], ...clean(input) };
      products[index] = product;
      return commit(products, product);
    },

    remove(id) {
      const products = load();
      const remaining = products.filter((product) => product.id !== Number(id));
      if (remaining.length === products.length) return false;
      save(remaining);
      return true;
    },

    // Restores the original 13 mock products
    reset() {
      seed();
    },
  };
})();
