// Cart stored in the browser as [{ id, quantity }].

const Cart = (() => {
  const CART_KEY = "focalpoint.cart";

  function load() {
    try {
      return JSON.parse(localStorage.getItem(CART_KEY)) ?? [];
    } catch {
      return [];
    }
  }

  function save(items) {
    localStorage.setItem(CART_KEY, JSON.stringify(items));
    document.dispatchEvent(new CustomEvent("cart:change"));
  }

  return {
    // Skips products that have since been deleted
    getItems() {
      return load().filter((item) => Store.getById(item.id) !== null);
    },

    count() {
      return this.getItems().reduce((total, item) => total + item.quantity, 0);
    },

    // Cart items joined with their product details, for display
    getLines() {
      return this.getItems().map((item) => {
        const product = Store.getById(item.id);
        return { product, quantity: item.quantity, lineTotal: product.price * item.quantity };
      });
    },

    total() {
      return this.getLines().reduce((sum, line) => sum + line.lineTotal, 0);
    },

    add(id, quantity = 1) {
      const product = Store.getById(id);
      if (!product) return { ok: false, message: "This product is no longer available." };

      const items = load();
      const item = items.find((entry) => entry.id === product.id);
      const inCart = item ? item.quantity : 0;

      if (product.stock === 0) {
        return { ok: false, message: `${product.name} is sold out.` };
      }

      if (inCart + quantity > product.stock) {
        return { ok: false, message: `Only ${product.stock} of ${product.name} in stock.` };
      }

      if (item) {
        item.quantity += quantity;
      } else {
        items.push({ id: product.id, quantity });
      }
      save(items);
      return { ok: true, message: `${product.name} added to your cart.` };
    },

    setQuantity(id, quantity) {
      const product = Store.getById(id);
      if (!product) return { ok: false, message: "This product is no longer available." };
      if (!Number.isInteger(quantity) || quantity < 1) {
        return { ok: false, message: "Enter a quantity of 1 or more." };
      }
      if (quantity > product.stock) {
        return { ok: false, message: `Only ${product.stock} of ${product.name} in stock.` };
      }

      const items = load();
      const item = items.find((entry) => entry.id === product.id);
      if (!item) return { ok: false, message: "This product is not in your cart." };

      item.quantity = quantity;
      save(items);
      return { ok: true, message: `${product.name} quantity updated.` };
    },

    remove(id) {
      save(load().filter((item) => item.id !== Number(id)));
    },

    clear() {
      save([]);
    },

    // Brings the cart in line with current stock. Returns a message for each change made.
    reconcile() {
      const messages = [];
      const items = [];

      for (const item of load()) {
        const product = Store.getById(item.id);
        if (!product) {
          messages.push("A product in your cart is no longer available, so it was removed.");
        } else if (product.stock === 0) {
          messages.push(`${product.name} has sold out, so it was removed from your cart.`);
        } else if (item.quantity > product.stock) {
          messages.push(`Only ${product.stock} of ${product.name} left, so your quantity was reduced.`);
          items.push({ id: item.id, quantity: product.stock });
        } else {
          items.push(item);
        }
      }

      if (messages.length > 0) save(items);
      return messages;
    },
  };
})();
