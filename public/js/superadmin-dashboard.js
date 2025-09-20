// Superadmin Dashboard JavaScript
// Handles PO viewing and other dashboard interactions

function viewPO(poNumber) {
    // Redirect to PO show page
    window.location.href = '/po/' + poNumber;
}

// Auto-dismiss alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            if (alert && alert.parentNode) {
                alert.remove();
            }
        }, 5000);
    });
});



