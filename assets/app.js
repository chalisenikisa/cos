// ============================================
// CANTEEN ORDERING SYSTEM - MAIN JAVASCRIPT
// ============================================

document.addEventListener('DOMContentLoaded', () => {

    // ---- Toast notification system ----
    const toastContainer = document.createElement('div');
    document.body.appendChild(toastContainer);

    window.showToast = function(message, type = '') {
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.innerHTML = message;
        document.body.appendChild(toast);
        requestAnimationFrame(() => {
            requestAnimationFrame(() => toast.classList.add('show'));
        });
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 400);
        }, 2800);
    };

    // ---- Add to cart (AJAX) ----
    document.querySelectorAll('.add-cart-btn').forEach(btn => {
        btn.addEventListener('click', async function() {
            const itemId = this.dataset.id;
            const itemName = this.dataset.name;
            try {
                const res = await fetch('api/add-to-cart.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ item_id: itemId })
                });
                const data = await res.json();
                if (data.success) {
                    showToast(`${itemName} added to cart`, 'success');
                    updateCartBadge(data.cart_count);
                    // Button animation
                    this.style.transform = 'scale(1.3)';
                    setTimeout(() => this.style.transform = '', 200);
                } else {
                    showToast(data.message || 'Error adding item', 'error');
                }
            } catch(e) {
                showToast('Network error', 'error');
            }
        });
    });

    // ---- Update cart badge ----
    function updateCartBadge(count) {
        const badges = document.querySelectorAll('.cart-badge');
        badges.forEach(b => {
            b.textContent = count;
            b.style.display = count > 0 ? 'flex' : 'none';
        });
    }

    // Load initial cart count
    async function loadCartCount() {
        try {
            const res = await fetch('api/cart-count.php');
            const data = await res.json();
            updateCartBadge(data.count || 0);
        } catch(e) {}
    }
    loadCartCount();

    // ---- Cart toggle button ----
    const cartToggle = document.getElementById('cart-toggle');
    if (cartToggle) {
        cartToggle.addEventListener('click', () => {
            window.location.href = 'cart.php';
        });
    }

    // ---- Category filter ----
    const catBtns = document.querySelectorAll('.cat-btn');
    const foodCards = document.querySelectorAll('.food-card');

    catBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            catBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            const cat = this.dataset.category;

            foodCards.forEach(card => {
                if (cat === 'all' || card.dataset.category == cat) {
                    card.style.display = '';
                    card.style.animation = 'fadeUp 0.35s ease forwards';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });

    // ---- Cart page: quantity controls ----
    document.querySelectorAll('.qty-minus').forEach(btn => {
        btn.addEventListener('click', async function() {
            const row = this.closest('.cart-item');
            const itemId = row.dataset.id;
            await updateCartQty(itemId, -1);
        });
    });
    document.querySelectorAll('.qty-plus').forEach(btn => {
        btn.addEventListener('click', async function() {
            const row = this.closest('.cart-item');
            const itemId = row.dataset.id;
            await updateCartQty(itemId, 1);
        });
    });

    async function updateCartQty(itemId, delta) {
        try {
            const res = await fetch('api/update-cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ item_id: itemId, delta: delta })
            });
            const data = await res.json();
            if (data.success) {
                if (data.reload) {
                    window.location.reload();
                } else {
                    const row = document.querySelector(`.cart-item[data-id="${itemId}"]`);
                    if (row) {
                        row.querySelector('.qty-value').textContent = data.quantity;
                        row.querySelector('.item-total').textContent = data.item_total;
                    }
                    document.querySelector('.summary-subtotal').textContent = data.subtotal;
                    document.querySelector('.summary-total').textContent = data.total;
                    updateCartBadge(data.cart_count);
                }
            } else {
                showToast(data.message || 'Error', 'error');
            }
        } catch(e) {
            showToast('Network error', 'error');
        }
    }

    // ---- Cart page: remove item ----
    document.querySelectorAll('.cart-item-remove').forEach(btn => {
        btn.addEventListener('click', async function() {
            const row = this.closest('.cart-item');
            const itemId = row.dataset.id;
            try {
                const res = await fetch('api/remove-from-cart.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ item_id: itemId })
                });
                const data = await res.json();
                if (data.success) {
                    row.style.transform = 'translateX(100px)';
                    row.style.opacity = '0';
                    row.style.transition = 'all 0.3s';
                    setTimeout(() => window.location.reload(), 350);
                }
            } catch(e) {
                showToast('Network error', 'error');
            }
        });
    });

    // ---- Payment option selection ----
    document.querySelectorAll('.payment-option').forEach(opt => {
        opt.addEventListener('click', function() {
            document.querySelectorAll('.payment-option').forEach(o => o.classList.remove('selected'));
            this.classList.add('selected');
            this.querySelector('input').checked = true;
        });
    });

    // ---- Fade up animation ----
    const style = document.createElement('style');
    style.textContent = `
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }
    `;
    document.head.appendChild(style);
});