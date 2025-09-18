// Handles adding items to the cart and clearing the cart
document.querySelectorAll(".add-to-cart").forEach(button => {
  button.addEventListener("click", function () {
      const productCard = this.closest(".product, .fragrance-card");
      if (!productCard) {
          console.error("Product card not found.");
          return;
      }

      const name = productCard.querySelector("h2, .card-title")?.textContent;
      const priceElement = productCard.querySelector(".price, .text-muted");
      const price = priceElement ? parseFloat(priceElement.textContent.replace("$", "")) : 0;
      const img = productCard.querySelector("img")?.src;

      if (!name || !price || !img) {
          console.error("Missing product details.");
          return;
      }

      // Get existing cart from localStorage or initialize an empty array
      const cart = JSON.parse(localStorage.getItem("cart")) || [];

      // Check if the item already exists in the cart
      const existingItem = cart.find(item => item.name === name);
      if (existingItem) {
          existingItem.quantity += 1; // Increment quantity if item exists
      } else {
          cart.push({ name, price, img, quantity: 1 }); // Add new item
      }

      // Save updated cart to localStorage
      try {
          localStorage.setItem("cart", JSON.stringify(cart));
          alert(`"${name}" has been added to your cart!`);
      } catch (error) {
          console.error("Error saving to localStorage:", error);
          alert("Failed to add item to cart. Please try again.");
      }
  });
});

// Update item quantity in the cart
document.querySelectorAll(".update-quantity").forEach(button => {
  button.addEventListener("click", function () {
      const productCard = this.closest(".cart-item");
      const name = productCard.querySelector(".item-name")?.textContent;
      const action = this.dataset.action; // "increase" or "decrease"

      const cart = JSON.parse(localStorage.getItem("cart")) || [];
      const item = cart.find(item => item.name === name);

      if (item) {
          if (action === "increase") {
              item.quantity += 1;
          } else if (action === "decrease" && item.quantity > 1) {
              item.quantity -= 1;
          } else {
              alert("Quantity cannot be less than 1.");
              return;
          }

          localStorage.setItem("cart", JSON.stringify(cart));
          location.reload(); // Reload to reflect changes
      } else {
          console.error("Item not found in cart.");
      }
  });
});

// Remove all items from the cart
const removeAllButton = document.getElementById("remove-all");
if (removeAllButton) {
  removeAllButton.addEventListener("click", function () {
      if (confirm("Are you sure you want to remove all items from your cart?")) {
          // Clear the cart in localStorage
          localStorage.removeItem("cart");

          // Send a request to the backend to clear the cart
          fetch("cart.php", {
              method: "POST",
              headers: {
                  "Content-Type": "application/x-www-form-urlencoded",
              },
              body: "action=clear",
          })
          .then(response => response.json())
          .then(data => {
              if (data.success) {
                  // Update the UI
                  const cartItemsList = document.getElementById("cart-items-list");
                  if (cartItemsList) {
                      cartItemsList.innerHTML = '<p>Your cart is empty.</p>';
                  }

                  // Update subtotal and total
                  const subtotalElement = document.getElementById("subtotal");
                  const totalElement = document.getElementById("total");
                  if (subtotalElement) subtotalElement.textContent = "0.00";
                  if (totalElement) totalElement.textContent = "0.00";

                  alert("All items have been removed from your cart.");
              } else {
                  alert("Failed to clear the cart. Please try again.");
              }
          })
          .catch(error => {
              console.error("Error clearing the cart:", error);
              alert("Failed to clear the cart. Please try again.");
          });
      }
  });
} else {
  console.warn("Remove All button not found.");
}