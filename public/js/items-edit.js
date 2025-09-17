// Items Edit Page JavaScript
// Handles auto-calculation of total cost

document.addEventListener('DOMContentLoaded', function() {
    const quantityInput = document.getElementById('quantity');
    const unitPriceInput = document.getElementById('unit_price');
    const totalCostInput = document.getElementById('total_cost');
    
    function calculateTotal() {
        const quantity = parseFloat(quantityInput.value) || 0;
        const unitPrice = parseFloat(unitPriceInput.value) || 0;
        const total = quantity * unitPrice;
        totalCostInput.value = total.toFixed(2);
    }
    
    if (quantityInput && unitPriceInput && totalCostInput) {
        quantityInput.addEventListener('input', calculateTotal);
        unitPriceInput.addEventListener('input', calculateTotal);
        
        // Calculate on page load
        calculateTotal();
    }
});



