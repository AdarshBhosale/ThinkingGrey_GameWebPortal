/* ───── TG Global Cart – add | remove | persist – with live badge ───── */
(() => {
  const CART_KEY = "tg_cart";

  /* helpers */
  const loadCart = () => {
    try {
      return JSON.parse(localStorage.getItem(CART_KEY)) || [];
    } catch {
      return [];
    }
  };
  const saveCart = (arr) => localStorage.setItem(CART_KEY, JSON.stringify(arr));

  /* utility for DevTools */
  window.getCart = loadCart;
  window.clearCart = () => localStorage.removeItem(CART_KEY);

  /* update the nav-badge everywhere */
  const refreshBadge = () => {
    const badge = document.getElementById("cart-count");
    if (!badge) return; // nav not present

    const n = loadCart().length;
    badge.textContent = n;
    badge.style.display = n ? "inline" : "none";
  };

  document.addEventListener("DOMContentLoaded", () => {
    /* always refresh badge on every page */
    refreshBadge();

    /* only product pages have the button & title */
    const cartBtn = document.querySelector('button[type="submit"]');
    const titleEl = document.getElementById("game-title");
    if (!cartBtn || !titleEl) return; // exit on non-product pages

    const productName = titleEl.textContent.trim();

    /* UI helpers */
    const markAdded = () => {
      cartBtn.innerHTML = '<i class="fa fa-check"></i> ADDED';
      cartBtn.classList.remove("btn-primary");
      cartBtn.classList.add("btn-success");
    };
    const markRemoved = () => {
      cartBtn.innerHTML =
        '<i class="fa fa-shopping-bag"></i> ADD&nbsp;TO&nbsp;CART';
      cartBtn.classList.remove("btn-success");
      cartBtn.classList.add("btn-primary");
    };

    /* initial state */
    loadCart().includes(productName) ? markAdded() : markRemoved();

    /* toggle on click */
    cartBtn.addEventListener("click", (e) => {
      e.preventDefault();
      let cart = loadCart();

      if (cart.includes(productName)) {
        cart = cart.filter((p) => p !== productName); // remove
        markRemoved();
        console.log("Removed →", productName);
      } else {
        cart.push(productName); // add
        markAdded();
        console.log("Added →", productName);
      }

      saveCart(cart);
      refreshBadge(); // << instant update
    });
  });
})();
