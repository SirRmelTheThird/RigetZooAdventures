// Riget Zoo Adventures - JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Auto-hide flash messages after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });

    // Ticket Quantity Selectors
    setupQuantityControl('adult', 0, 20);
    setupQuantityControl('child', 0, 20);

    // Validate ticket forms on submit
    const ticketForms = document.querySelectorAll('form[action^="/tickets/"]');
    ticketForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const adultInput = form.querySelector('#adult_input');
            const childInput = form.querySelector('#child_input');
            const adultCount = adultInput ? parseInt(adultInput.value, 10) || 0 : 0;
            const childCount = childInput ? parseInt(childInput.value, 10) || 0 : 0;

            if (adultCount === 0 && childCount === 0) {
                e.preventDefault();
                alert('Please select at least one ticket.');
            }
        });
    });

    // Confirm before clearing or removing items from cart
    const clearCartBtn = document.getElementById('clear-cart');
    if (clearCartBtn) {
        clearCartBtn.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to clear your cart?')) {
                e.preventDefault();
            }
        });
    }

    const removeForms = document.querySelectorAll('form[action="/cart/remove"]');
    removeForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!confirm('Are you sure you want to remove this item from your cart?')) {
                e.preventDefault();
            }
        });
    });

    setupRevealAnimations();
});

function setupQuantityControl(type, min = 0, max = 20) {
    const addBtn = document.getElementById(`add_${type}`);
    const subBtn = document.getElementById(`sub_${type}`);
    const valDisplay = document.getElementById(`${type}_value`);
    const valInput = document.getElementById(`${type}_input`);

    if (!addBtn || !subBtn || !valDisplay || !valInput) {
        return;
    }

    const setValue = (value) => {
        const next = Math.max(min, Math.min(max, value));
        valInput.value = next;
        valDisplay.textContent = next;
    };

    addBtn.addEventListener('click', function() {
        setValue((parseInt(valInput.value, 10) || 0) + 1);
    });

    subBtn.addEventListener('click', function() {
        setValue((parseInt(valInput.value, 10) || 0) - 1);
    });
}

function setupRevealAnimations() {
    const revealItems = document.querySelectorAll('.reveal');
    if (!revealItems.length) {
        return;
    }

    if (!('IntersectionObserver' in window) || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        revealItems.forEach(item => item.classList.add('is-visible'));
        return;
    }

    const observer = new IntersectionObserver((entries, activeObserver) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                activeObserver.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.12,
        rootMargin: '0px 0px -40px 0px'
    });

    revealItems.forEach(item => observer.observe(item));
}
