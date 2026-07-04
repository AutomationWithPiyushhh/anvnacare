/**
 * ANVNA Care - Main Client Script
 * Shared functions for UI, AJAX requests, toasts, loader, and search suggestion.
 */

document.addEventListener('DOMContentLoaded', function () {
    // 1. Initialize Bootstrap Tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // 2. Global Search Auto-suggestion Handler
    const searchInput = document.getElementById('globalSearchInput');
    const suggestionsBox = document.getElementById('globalSearchSuggestions');
    const searchBtn = document.getElementById('globalSearchButton');

    if (searchInput && suggestionsBox) {
        let debounceTimer;

        searchInput.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            const query = searchInput.value.trim();

            if (query.length < 2) {
                suggestionsBox.classList.add('d-none');
                suggestionsBox.innerHTML = '';
                return;
            }

            debounceTimer = setTimeout(() => {
                fetch(`api/search.php?q=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(data => {
                        suggestionsBox.innerHTML = '';
                        if (data.success && data.results.length > 0) {
                            data.results.forEach((item, index) => {
                                const div = document.createElement('div');
                                div.className = 'autosuggest-item';
                                div.id = `suggest-item-${index}`;
                                div.setAttribute('data-testid', `suggest-item-${item.type}`);
                                
                                // Label icon depending on type
                                let icon = 'bi-capsule';
                                if (item.type === 'doctor') icon = 'bi-person-heart';
                                if (item.type === 'product') icon = 'bi-heart-pulse';
                                if (item.type === 'test') icon = 'bi-clipboard2-pulse';

                                div.innerHTML = `<i class="bi ${icon} text-success me-2"></i> ${item.name} <span class="badge bg-light text-dark ms-2" style="font-size: 0.7rem">${item.type}</span>`;
                                div.addEventListener('click', () => {
                                    window.location.href = item.url;
                                });
                                suggestionsBox.appendChild(div);
                            });
                            suggestionsBox.classList.remove('d-none');
                        } else {
                            suggestionsBox.innerHTML = `<div class="autosuggest-item text-muted">No results found for "${query}"</div>`;
                            suggestionsBox.classList.remove('d-none');
                        }
                    })
                    .catch(err => console.error('Search error:', err));
            }, 300);
        });

        // Close suggestion box on outside click
        document.addEventListener('click', function (e) {
            if (!searchInput.contains(e.target) && !suggestionsBox.contains(e.target)) {
                suggestionsBox.classList.add('d-none');
            }
        });

        // Search Button click links to general list with parameter
        if (searchBtn) {
            searchBtn.addEventListener('click', function () {
                const query = searchInput.value.trim();
                if (query.length > 0) {
                    window.location.href = `medicines.php?search=${encodeURIComponent(query)}`;
                }
            });
        }
    }
});

// 3. Dynamic Toast Notification Utility
function showToast(message, type = 'success') {
    const container = document.getElementById('customToastContainer');
    if (!container) return;

    const toast = document.createElement('div');
    toast.className = `custom-toast ${type}`;
    toast.setAttribute('data-testid', `toast-notification-${type}`);
    
    let icon = '<i class="bi bi-check-circle-fill text-success fs-5 me-2"></i>';
    if (type === 'info') {
        icon = '<i class="bi bi-info-circle-fill text-primary fs-5 me-2"></i>';
    } else if (type === 'danger') {
        icon = '<i class="bi bi-exclamation-triangle-fill text-danger fs-5 me-2"></i>';
    }

    toast.innerHTML = `
        <div class="d-flex align-items-center">
            ${icon}
            <span class="toast-message text-dark fw-medium">${message}</span>
        </div>
        <button type="button" class="btn-close ms-3" aria-label="Close" onclick="this.parentElement.remove()"></button>
    `;

    container.appendChild(toast);

    // Auto-remove toast after 4 seconds
    setTimeout(() => {
        toast.style.animation = 'slideInRight 0.3s ease reverse forwards';
        setTimeout(() => {
            toast.remove();
        }, 300);
    }, 4000);
}

// 4. Global Spinner Loader Control
function showLoader() {
    const loader = document.getElementById('globalLoader');
    if (loader) {
        loader.classList.remove('d-none');
    }
}

function hideLoader() {
    const loader = document.getElementById('globalLoader');
    if (loader) {
        loader.classList.add('d-none');
    }
}

// 5. AJAX Add To Cart Handler
function addToCart(itemId, itemType, quantity = 1) {
    showLoader();
    
    // Simulate a brief API loader delay (1 second) to allow students to automate loading states
    setTimeout(() => {
        fetch('api/cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'add',
                item_id: itemId,
                item_type: itemType,
                quantity: quantity
            })
        })
        .then(response => response.json())
        .then(data => {
            hideLoader();
            if (data.success) {
                // Update badge count
                const badge = document.getElementById('navCartCount');
                if (badge) {
                    badge.innerText = data.cart_count;
                }
                showToast(data.message, 'success');
            } else {
                showToast(data.message, 'danger');
                if (data.redirect) {
                    // Redirect to login if user session expired and it is required
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 1000);
                }
            }
        })
        .catch(err => {
            hideLoader();
            console.error('Cart error:', err);
            showToast('Failed to add item. Connection error.', 'danger');
        });
    }, 800);
}

// 6. AJAX Add To Wishlist Handler
function addToWishlist(itemId, itemType) {
    showLoader();
    setTimeout(() => {
        fetch('api/wishlist.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'add',
                item_id: itemId,
                item_type: itemType
            })
        })
        .then(response => response.json())
        .then(data => {
            hideLoader();
            if (data.success) {
                showToast(data.message, 'success');
            } else {
                showToast(data.message, 'danger');
                if (data.redirect) {
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 1000);
                }
            }
        })
        .catch(err => {
            hideLoader();
            console.error('Wishlist error:', err);
            showToast('Failed to add item. Connection error.', 'danger');
        });
    }, 500);
}
