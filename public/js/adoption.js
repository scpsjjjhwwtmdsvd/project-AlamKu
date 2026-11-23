/* Konfirmasi pembayaran, pemilihan metode, rincian dana, dan status pemrosesan. */
(() => {
    const form = document.querySelector('[data-adopt-form]');
    const dataElement = document.querySelector('[data-adopt-data]');

    if (!form || !dataElement) {
        return;
    }

    let data;

    try {
        data = JSON.parse(dataElement.textContent);
    } catch {
        return;
    }

    const packages = Array.isArray(data.packages) ? data.packages : [];
    const currency = new Intl.NumberFormat('id-ID');
    const format = (value) => 'Rp ' + currency.format(value);
    const formatTrees = (value) => currency.format(value) + ' bibit';
    const totalOutput = form.querySelector('[data-adopt-total]');
    const submitButton = form.querySelector('[data-adopt-submit]');
    const submitLabel = form.querySelector('[data-adopt-submit-label]');
    const overlay = form.querySelector('[data-pay-overlay]');
    const overlaySummary = form.querySelector('[data-pay-summary]');
    const fundRows = Array.from(document.querySelectorAll('[data-fund-row]'));
    const fundTotal = document.querySelector('[data-fund-total]');

    const dialog = form.querySelector('[data-checkout-dialog]');
    const openButton = form.querySelector('[data-checkout-open]');
    const closeButtons = Array.from(form.querySelectorAll('[data-checkout-close]'));
    const methodInputs = Array.from(form.querySelectorAll('input[name="metode"]'));
    const methodPanels = Array.from(form.querySelectorAll('[data-pay-detail]'));
    const checkoutPackage = form.querySelector('[data-checkout-package]');
    const checkoutTrees = form.querySelector('[data-checkout-trees]');
    const checkoutTotal = form.querySelector('[data-checkout-total]');
    const checkoutSubmitLabel = form.querySelector('[data-checkout-submit-label]');

    const selectedPackage = () => {
        const selected = form.querySelector('input[name="paket"]:checked');

        return packages.find((item) => item.key === selected?.value) || packages[0] || null;
    };

    const selectedMethod = () => form.querySelector('input[name="metode"]:checked');

    const methodLabel = () => {
        const input = selectedMethod();
        const card = input?.closest('.pay-method')?.querySelector('.pay-method-card strong');

        return card?.textContent?.trim() || 'QRIS';
    };

    const allocationAmounts = (total) => {
        const amounts = fundRows.map((row) => Math.round(total * Number(row.dataset.percent || 0) / 100));
        const difference = total - amounts.reduce((sum, amount) => sum + amount, 0);

        if (amounts.length > 0) {
            amounts[amounts.length - 1] += difference;
        }

        return amounts;
    };

    const render = () => {
        const packageItem = selectedPackage();

        if (!packageItem) {
            return;
        }

        if (totalOutput) {
            totalOutput.textContent = format(packageItem.price);
        }

        if (fundTotal) {
            fundTotal.textContent = format(packageItem.price);
        }

        const amounts = allocationAmounts(packageItem.price);

        fundRows.forEach((row, index) => {
            const amount = row.querySelector('[data-fund-amount]');

            if (amount) {
                amount.textContent = format(amounts[index] ?? 0);
            }
        });

        if (submitLabel) {
            submitLabel.textContent = 'Bayar ' + format(packageItem.price);
        }

        if (checkoutPackage) {
            checkoutPackage.textContent = packageItem.name;
        }

        if (checkoutTrees) {
            checkoutTrees.textContent = formatTrees(packageItem.trees);
        }

        if (checkoutTotal) {
            checkoutTotal.textContent = format(packageItem.price);
        }

        if (checkoutSubmitLabel) {
            checkoutSubmitLabel.textContent = 'Konfirmasi & bayar ' + format(packageItem.price);
        }

        if (overlaySummary) {
            overlaySummary.textContent = `${packageItem.name} · ${formatTrees(packageItem.trees)} · ${format(packageItem.price)}`;
        }
    };

    const syncMethod = () => {
        const method = selectedMethod()?.value || 'qris';

        methodPanels.forEach((panel) => {
            const active = panel.dataset.payDetail === method;

            panel.hidden = !active;
            panel.querySelectorAll('[data-pay-field]').forEach((field) => {
                field.disabled = !active;
            });
        });
    };

    const openCheckout = () => {
        if (!dialog) {
            return;
        }

        render();
        syncMethod();

        if (dialog.open) {
            return;
        }

        if (typeof dialog.showModal === 'function') {
            dialog.showModal();
        } else {
            dialog.setAttribute('open', '');
        }

        dialog.focus({ preventScroll: true });
    };

    const closeCheckout = () => {
        if (!dialog) {
            return;
        }

        if (dialog.open && typeof dialog.close === 'function') {
            dialog.close();
        } else {
            dialog.removeAttribute('open');
        }

        openButton?.focus({ preventScroll: true });
    };

    let submitting = false;
    let timer = null;

    const reset = () => {
        submitting = false;
        window.clearTimeout(timer);
        timer = null;

        if (overlay) {
            overlay.hidden = true;
        }

        if (dialog?.open) {
            dialog.close();
        }

        if (submitButton) {
            submitButton.disabled = false;
        }

        form.removeAttribute('aria-busy');
        render();
        syncMethod();
    };

    openButton?.addEventListener('click', (event) => {
        event.preventDefault();
        openCheckout();
    });

    closeButtons.forEach((button) => {
        button.addEventListener('click', closeCheckout);
    });

    dialog?.addEventListener('click', (event) => {
        if (event.target === dialog) {
            closeCheckout();
        }
    });

    dialog?.addEventListener('close', () => {
        if (!submitting) {
            openButton?.focus({ preventScroll: true });
        }
    });

    methodInputs.forEach((input) => {
        input.addEventListener('change', syncMethod);
    });

    form.addEventListener('change', (event) => {
        if (event.target instanceof Element && event.target.matches('input[name="paket"]')) {
            render();
        }
    });

    form.addEventListener('submit', (event) => {
        if (submitting) {
            event.preventDefault();

            return;
        }

        if (!form.checkValidity()) {
            return;
        }

        event.preventDefault();
        submitting = true;
        form.setAttribute('aria-busy', 'true');

        if (dialog?.open) {
            dialog.close();
        }

        if (overlaySummary) {
            const packageItem = selectedPackage();

            if (packageItem) {
                overlaySummary.textContent = `${packageItem.name} · ${formatTrees(packageItem.trees)} · ${format(packageItem.price)} · ${methodLabel()}`;
            }
        }

        if (overlay) {
            overlay.hidden = false;
            overlay.focus({ preventScroll: true });
        }

        if (submitButton) {
            submitButton.disabled = true;
        }

        // Jeda singkat agar status pemrosesan sempat terbaca sebelum halaman berpindah.
        timer = window.setTimeout(() => {
            form.submit();
        }, 700);
    });

    window.addEventListener('pageshow', (event) => {
        if (event.persisted) {
            reset();
        }
    });

    render();
    syncMethod();

    if (dialog?.hasAttribute('data-checkout-open-on-load')) {
        openCheckout();
    }
})();
