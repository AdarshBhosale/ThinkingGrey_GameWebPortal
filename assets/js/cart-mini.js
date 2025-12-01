function getCart() {
  try {
    return JSON.parse(localStorage.getItem("tg_cart")) || [];
  } catch {
    return [];
  }
}
function setCart(arr) {
  localStorage.setItem("tg_cart", JSON.stringify(arr));
}
function syncBadge() {
  const badge = document.getElementById("nav-count");
  if (badge) {
    const n = getCart().length;
    badge.textContent = n;
    badge.style.display = n ? "inline" : "none";
  }
}
document.addEventListener("DOMContentLoaded", syncBadge);
