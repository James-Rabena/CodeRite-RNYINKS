// cart.js - Handles cart functionality with API integration
const cartItemsList = document.getElementById('cart-items-list');
const subtotalElement = document.getElementById('subtotal');
const totalElement = document.getElementById('total');
const emptyCartMessage = '<p id="empty-cart-message">Your cart is empty.</p>';

// Placeholder variables for PHP session info (replace with actual values in PHP)
const isLoggedIn = false; // Replace with PHP variable
const userId = null; // Replace with PHP variable
const debugInfo = {}; // Replace with PHP variable

console.log('Cart Debug Info:', debugInfo);

// First-load initialization
document.addEventListener('DOMContentLoaded', function () {
    console.log('DOM loaded, initializing cart');
    // If not logged in or no server-side items were loaded, check localStorage
    if (!isLoggedIn || (isLoggedIn && debugInfo.db_items_found === 0)) {
        loadCartFromLocalStorage();
    }

    // Update the year in footer
    const yearElement = document.getElementById('current-year');
    if (yearElement) {
        yearElement.textContent = new Date().getFullYear();
    }
});

// Helper function to fix image paths
function fixImagePath(path) {
    if (!path) return 'assets/product-placeholder.png'; // Default placeholder
    if (!path.startsWith('http') && !path.startsWith('assets')) {
        return 'assets/' + path.split('/').pop();
    }
    return path;
}

/**
 * Load cart items from localStorage and display them
 */
function loadCartFromLocalStorage() {
    console.log('Loading cart from localStorage');
    try {
        // Get items from localStorage
        const cartItems = JSON.parse(localStorage.getItem('cart')) || [];
        console.log('Local storage cart items:', cartItems);

        // If no items, show message
        if (cartItems.length === 0) {
            cartItemsList.innerHTML = '<div class="empty-cart-message">Your cart is empty.</div>';
            updateSubtotal(0);
            return;
        }

        // Clear any existing items if we're reloading
        cartItemsList.innerHTML = '';

        // Calculate subtotal
        let subtotal = 0;

        // Add each item to the cart display
        cartItems.forEach(item => {
            // Calculate item total and add to subtotal
            const itemTotal = item.price * item.quantity;
            subtotal += itemTotal;

            // Fix image path
            const imagePath = fixImagePath(item.image_url);

            // Create cart item element
            const itemElement = document.createElement('div');
            itemElement.className = 'cart-item';
            itemElement.dataset.id = item.product_id;

            itemElement.innerHTML = `
                <div class="cart-item-image">
                    <img src="${imagePath}" alt="${item.product_name}">
                </div>
                <div class="cart-item-details">
                    <h6>${item.product_name}</h6>
                    <p>$${item.price.toFixed(2)}</p>
                    <div class="quantity-controls">
                        <button onclick="updateQuantity(${item.product_id}, -1)">-</button>
                        <span class="quantity">${item.quantity}</span>
                        <button onclick="updateQuantity(${item.product_id}, 1)">+</button>
                    </div>
                </div>
                <button class="remove-btn" onclick="removeItem(${item.product_id})">×</button>
            `;

            // Add to cart
            cartItemsList.appendChild(itemElement);
        });

        // Update subtotal display
        updateSubtotal(subtotal);
    } catch (error) {
        console.error('Error loading cart from localStorage', error);
        cartItemsList.innerHTML =
            '<div class="alert alert-danger">There was an error loading your cart. Please try refreshing the page.</div>';
    }
}

function updateQuantity(productId, change) {
    console.log(`Updating quantity for product ${productId} by ${change}`);

    // Find the quantity input element
    const quantityInput = document.querySelector(`.cart-item[data-id="${productId}"] .quantity-input`);
    if (!quantityInput) {
        console.error(`Quantity input not found for product ${productId}`);
        return;
    }

    // Get the current quantity
    const currentQuantity = parseInt(quantityInput.value);
    const newQuantity = currentQuantity + change;

    // Validate the new quantity
    if (newQuantity < 1) {
        showToast('Minimum quantity is 1', 'limit-warning');
        return;
    }

    if (newQuantity > 100) {
        showToast('Maximum limit is 100 units per item', 'limit-warning');
        return;
    }

    // Update the input value immediately for better UX
    quantityInput.value = newQuantity;

    if (isLoggedIn) {
        // Update in the database for logged-in users
        fetch('cart_api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'update',
                product_id: productId,
                quantity: newQuantity
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateCartTotals();
                showToast('Quantity updated', 'success');
            } else {
                console.error('Failed to update item:', data.message);
                showToast('Could not update item: ' + (data.message || 'Unknown error'), 'danger');
                quantityInput.value = currentQuantity; // Revert to the previous value
            }
        })
        .catch(error => {
            console.error('Error updating item:', error);
            showToast('Error updating item. Please try again.', 'danger');
            quantityInput.value = currentQuantity; // Revert to the previous value
        });
    } else {
        // Update in localStorage for guest users
        try {
            const cartItems = JSON.parse(localStorage.getItem('cart')) || [];
            const itemIndex = cartItems.findIndex(item => item.product_id == productId);

            if (itemIndex > -1) {
                cartItems[itemIndex].quantity = newQuantity;
                localStorage.setItem('cart', JSON.stringify(cartItems));
                updateCartTotals();
                showToast('Quantity updated', 'success');
            }
        } catch (e) {
            console.error('Error updating quantity in localStorage:', e);
            showToast('Could not update item quantity', 'danger');
            quantityInput.value = currentQuantity; // Revert to the previous value
        }
    }
}

function removeItem(productId) {
    console.log(`Removing item with product ID: ${productId}`);

    if (isLoggedIn) {
        // Remove from the database for logged-in users
        fetch('cart_api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'remove',
                product_id: productId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.querySelector(`.cart-item[data-id="${productId}"]`).remove();
                updateCartTotals();
                showToast('Item removed from cart', 'success');
            } else {
                console.error('Failed to remove item:', data.message);
                showToast('Could not remove item: ' + (data.message || 'Unknown error'), 'danger');
            }
        })
        .catch(error => {
            console.error('Error removing item:', error);
            showToast('Error removing item. Please try again.', 'danger');
        });
    } else {
        // Remove from localStorage for guest users
        try {
            let cartItems = JSON.parse(localStorage.getItem('cart')) || [];
            cartItems = cartItems.filter(item => item.product_id != productId);
            localStorage.setItem('cart', JSON.stringify(cartItems));
            document.querySelector(`.cart-item[data-id="${productId}"]`).remove();
            updateCartTotals();
            showToast('Item removed from cart', 'success');
        } catch (e) {
            console.error('Error removing item from localStorage:', e);
            showToast('Could not remove item', 'danger');
        }
    }
}

// Remove all items from cart
function removeAllItems() {
    if (confirm('Are you sure you want to remove all items from your cart?')) {
        console.log('Remove all items initiated. isLoggedIn:', isLoggedIn);

        if (isLoggedIn) {
            // Clear from database
            fetch('cart_api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'clear'
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('API response:', data);
                if (data.success) {
                    // Clear display
                    document.getElementById('cart-items-list').innerHTML = 
                        '<div class="empty-cart-message">Your cart is empty.</div>';
                    updateSubtotal(0);
                } else {
                    console.error('Failed to clear cart:', data.message);
                    alert('Could not clear cart: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error clearing cart:', error);
                alert('Error clearing cart. Please try again.');
            });
        } else {
            // Clear localStorage
            try {
                localStorage.removeItem('cart');
                document.getElementById('cart-items-list').innerHTML = 
                    '<div class="empty-cart-message">Your cart is empty.</div>';
                updateSubtotal(0);
                console.log('Cart cleared from localStorage.');
            } catch (error) {
                console.error('Error clearing localStorage cart:', error);
                alert('Error clearing cart. Please try again.');
            }
        }
    }
}

// Recalculate cart total based on current items
function recalculateTotal() {
    if (isLoggedIn) {
        // For logged in users - reload from server for most accurate total
        window.location.reload();
    } else {
        // For guests - calculate from local storage
        const cartItems = JSON.parse(localStorage.getItem('cart')) || [];
        let subtotal = 0;
        
        cartItems.forEach(item => {
            subtotal += item.price * item.quantity;
        });
        
        updateSubtotal(subtotal);
    }
}

// Update the subtotal and total display
function updateSubtotal(amount) {
    if (subtotalElement) subtotalElement.textContent = amount.toFixed(2);
    if (totalElement) totalElement.textContent = amount.toFixed(2);
}

// Update the displayed quantity for an item
function updateItemQuantityDisplay(productId, newQuantity) {
    const item = document.querySelector(`.cart-item[data-id="${productId}"]`);
    if (item) {
        if (newQuantity <= 0) {
            item.remove();
        } else {
            item.querySelector('.quantity').textContent = newQuantity;
        }
    }
}

// Add item to cart (for product pages)
function addToCart(productData) {
    if (isLoggedIn) {
        // For logged in users - use API
        fetch('cart_api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'add',
                product_id: productData.product_id,
                product_name: productData.product_name,
                price: productData.price,
                quantity: productData.quantity || 1,
                image_url: productData.image_url
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Item added to cart!');
            } else {
                console.error('Failed to add item:', data.message);
            }
        })
        .catch(error => {
            console.error('Error adding item:', error);
        });
    } else {
        // For guests using localStorage
        let cartItems = JSON.parse(localStorage.getItem('cart')) || [];
        const existingItem = cartItems.find(item => item.product_id == productData.product_id);
        
        if (existingItem) {
            existingItem.quantity += (productData.quantity || 1);
        } else {
            cartItems.push({
                product_id: productData.product_id,
                product_name: productData.product_name,
                price: productData.price,
                quantity: productData.quantity || 1,
                image_url: productData.image_url
            });
        }
        
        localStorage.setItem('cart', JSON.stringify(cartItems));
        alert('Item added to cart!');
    }
}

// Proceed to checkout
function proceedToCheckout() {
    // Check if cart is empty
    const subtotal = parseFloat(document.getElementById('subtotal').textContent);
    if (subtotal <= 0) {
        alert('Your cart is empty. Please add items before checking out.');
        return;
    }
    
    // Redirect to checkout
    window.location.href = 'checkout.php';
}

document.addEventListener("DOMContentLoaded", function () {
    const cart = JSON.parse(localStorage.getItem("cart")) || [];
    const cartItemsList = document.getElementById("cart-items-list");

    if (cart.length === 0) {
        cartItemsList.innerHTML = '<p>Your cart is empty.</p>';
        return;
    }

    let subtotal = 0;
    cartItemsList.innerHTML = "";

    cart.forEach(item => {
        subtotal += item.price * item.quantity;

        const itemElement = document.createElement("div");
        itemElement.classList.add("cart-item");
        itemElement.innerHTML = `
            <img src="${item.img}" alt="${item.name}" class="item-img">
            <p class="item-name">${item.name}</p>
            <p class="item-price">$${item.price.toFixed(2)}</p>
            <div class="quantity-controls">
                <button class="update-quantity" data-action="decrease">-</button>
                <span class="item-quantity">${item.quantity}</span>
                <button class="update-quantity" data-action="increase">+</button>
            </div>
            <p class="item-total">$${(item.price * item.quantity).toFixed(2)}</p>
        `;
        cartItemsList.appendChild(itemElement);
    });

    document.getElementById("subtotal").textContent = subtotal.toFixed(2);
    document.getElementById("total").textContent = (subtotal * 1.1).toFixed(2); // Assuming 10% tax
});
