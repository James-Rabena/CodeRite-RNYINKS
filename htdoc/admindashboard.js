function toggleDropdown() {
    document.getElementById("userDropdown").classList.toggle("show");
}

window.onclick = function(event) {
    if (!event.target.matches('.dropdown-button')) {
        var dropdowns = document.getElementsByClassName("dropdown-content");
        for (var i = 0; i < dropdowns.length; i++) {
            var openDropdown = dropdowns[i];
            if (openDropdown.classList.contains('show')) {
                openDropdown.classList.remove('show');
            }
        }
    }
}

function updateOrder(orderId, productName, purchaserName) {
    const statusDropdown = document.querySelector(`select[name='status_${orderId}']`);
    const newStatus = statusDropdown.value;
    alert(`Order ID: ${orderId}, Product: ${productName}, Purchaser: ${purchaserName}, Status: ${newStatus} (This would typically involve an AJAX call to update the database)`);
}

function removeRow(button, orderId) {
    const row = button.parentNode.parentNode; // Get the table row
    row.remove();
    alert(`Order ID: ${orderId} removed (This would typically involve an AJAX call to remove from the database)`);
}
