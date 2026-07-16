// DevlioPay Frontend JS

document.addEventListener('DOMContentLoaded', () => {

    // Auto-dismiss flash messages after 5 seconds
    document.querySelectorAll('.alert-auto-dismiss').forEach(el => {
        setTimeout(() => {
            el.style.transition = 'opacity 0.5s ease';
            el.style.opacity = '0';
            setTimeout(() => el.remove(), 500);
        }, 5000);
    });

    // Confirm dialogs
    document.querySelectorAll('[data-confirm]').forEach(el => {
        el.addEventListener('click', (e) => {
            if (!confirm(el.dataset.confirm || 'Are you sure?')) {
                e.preventDefault();
            }
        });
    });

    // Tab switching
    document.querySelectorAll('[data-tab-group]').forEach(group => {
        const tabs = group.querySelectorAll('[data-tab]');
        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                const tabName = tab.dataset.tab;
                const groupName = group.dataset.tabGroup;

                tabs.forEach(t => t.classList.remove('active', 'text-primary-400', 'border-primary-500'));
                tab.classList.add('active', 'text-primary-400', 'border-primary-500');

                document.querySelectorAll(`[data-tab-panel="${groupName}"]`).forEach(panel => {
                    panel.classList.add('hidden');
                    if (panel.dataset.tabPanel === tabName) {
                        panel.classList.remove('hidden');
                    }
                });
            });
        });
    });
});

// Cart helpers
window.cart = {
    add(productId, pricingId, quantity = 1, config = {}) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/cart/add';

        const csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = document.querySelector('meta[name="csrf-token"]').content;
        form.appendChild(csrf);

        const pInput = document.createElement('input');
        pInput.type = 'hidden';
        pInput.name = 'product_id';
        pInput.value = productId;
        form.appendChild(pInput);

        const prInput = document.createElement('input');
        prInput.type = 'hidden';
        prInput.name = 'pricing_id';
        prInput.value = pricingId;
        form.appendChild(prInput);

        const qInput = document.createElement('input');
        qInput.type = 'hidden';
        qInput.name = 'quantity';
        qInput.value = quantity;
        form.appendChild(qInput);

        Object.keys(config).forEach(key => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = `config[${key}]`;
            input.value = config[key];
            form.appendChild(input);
        });

        document.body.appendChild(form);
        form.submit();
    }
};
