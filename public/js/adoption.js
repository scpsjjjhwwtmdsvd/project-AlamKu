/* Konfirmasi pembayaran bertahap: metode, konfirmasi, pembayaran, dan status pemrosesan. */
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
    const nextButtons = Array.from(form.querySelectorAll('[data-checkout-next]'));
    const backButtons = Array.from(form.querySelectorAll('[data-checkout-back]'));
    const methodInputs = Array.from(form.querySelectorAll('input[name="metode"]'));
    const methodPanels = Array.from(form.querySelectorAll('[data-pay-detail]'));
    const methodPreviews = Array.from(form.querySelectorAll('[data-pay-preview]'));
    const steps = Array.from(form.querySelectorAll('[data-checkout-step]'));
    const stepIndicators = Array.from(form.querySelectorAll('[data-checkout-indicator]'));
    const stepLabel = form.querySelector('[data-checkout-step-label]');
    const stepTitle = form.querySelector('#checkout-title');
    const payTitle = form.querySelector('[data-checkout-pay-title]');
    const paySubtitle = form.querySelector('[data-checkout-pay-subtitle]');
    const checkoutPackages = Array.from(form.querySelectorAll('[data-checkout-package]'));
    const checkoutTrees = Array.from(form.querySelectorAll('[data-checkout-trees]'));
    const checkoutTotals = Array.from(form.querySelectorAll('[data-checkout-total]'));
    const checkoutMethods = Array.from(form.querySelectorAll('[data-checkout-method]'));
    const checkoutSubmitLabel = form.querySelector('[data-checkout-submit-label]');
    const previewBank = form.querySelector('[data-preview-bank]');
    const previewRekening = form.querySelector('[data-preview-rekening]');
    const previewWallet = form.querySelector('[data-preview-wallet]');
    const previewPhone = form.querySelector('[data-preview-phone]');

    const bankSelect = form.querySelector('select[name="bank"]');
    const rekeningInput = form.querySelector('input[name="nomor_rekening"]');
    const walletSelect = form.querySelector('select[name="dompet"]');
    const phoneInput = form.querySelector('input[name="nomor_hp"]');

    let currentStep = 1;

    const stepCopy = {
        1: { label: 'Langkah 1 dari 3 · Metode', title: 'Pilih metode pembayaran' },
        2: { label: 'Langkah 2 dari 3 · Konfirmasi', title: 'Periksa pesanan Anda' },
        3: { label: 'Langkah 3 dari 3 · Pembayaran', title: 'Selesaikan pembayaran' },
    };

    const payCopy = {
        qris: {
            title: 'Pindai kode QRIS',
            subtitle: 'Buka aplikasi bank atau e-wallet, pilih QRIS, lalu pindai kode di bawah.',
        },
        virtual_account: {
            title: 'Transfer ke rekening tujuan',
            subtitle: 'Gunakan nominal yang tepat, lalu konfirmasi setelah transfer.',
        },
        dompet_digital: {
            title: 'Bayar dengan e-wallet',
            subtitle: 'Buka aplikasi e-wallet Anda dan selesaikan pembayaran.',
        },
    };

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

    const maskDigits = (value, prefix = 0) => {
        const digits = String(value || '').replace(/\D+/g, '');

        if (digits === '') {
            return '-';
        }

        const suffix = Math.min(4, digits.length);
        const safePrefix = Math.min(prefix, Math.max(0, digits.length - suffix));
        const hidden = Math.max(0, digits.length - safePrefix - suffix);

        return digits.slice(0, safePrefix) + '•'.repeat(hidden) + digits.slice(-suffix);
    };

    const allocationAmounts = (total) => {
        const amounts = fundRows.map((row) => Math.round(total * Number(row.dataset.percent || 0) / 100));
        const difference = total - amounts.reduce((sum, amount) => sum + amount, 0);

        if (amounts.length > 0) {
            amounts[amounts.length - 1] += difference;
        }

        return amounts;
    };

    const syncPreview = () => {
        const method = selectedMethod()?.value || 'qris';
        const copy = payCopy[method] || payCopy.qris;

        methodPreviews.forEach((preview) => {
            preview.hidden = preview.dataset.payPreview !== method;
        });

        if (payTitle) {
            payTitle.textContent = copy.title;
        }

        if (paySubtitle) {
            paySubtitle.textContent = copy.subtitle;
        }

        if (method === 'virtual_account') {
            if (previewBank) {
                previewBank.textContent = bankSelect?.value || '-';
            }

            if (previewRekening) {
                previewRekening.textContent = maskDigits(rekeningInput?.value, 0);
            }
        }

        if (method === 'dompet_digital') {
            if (previewWallet) {
                previewWallet.textContent = walletSelect?.value || '-';
            }

            if (previewPhone) {
                previewPhone.textContent = maskDigits(phoneInput?.value, 4);
            }
        }
    };

    const showStep = (step) => {
        currentStep = Math.min(3, Math.max(1, Number(step) || 1));

        steps.forEach((section) => {
            const active = Number(section.dataset.checkoutStep) === currentStep;
            section.hidden = !active;
            section.classList.toggle('is-active', active);
        });

        stepIndicators.forEach((indicator) => {
            const number = Number(indicator.dataset.checkoutIndicator);
            indicator.classList.toggle('is-active', number === currentStep);
            indicator.classList.toggle('is-done', number < currentStep);
        });

        const copy = stepCopy[currentStep];

        if (stepLabel) {
            stepLabel.textContent = copy.label;
        }

        if (stepTitle) {
            stepTitle.textContent = currentStep === 3
                ? (payCopy[selectedMethod()?.value || 'qris'] || payCopy.qris).title
                : copy.title;
        }

        if (currentStep === 3) {
            syncPreview();
        }

        const activeStep = steps.find((section) => Number(section.dataset.checkoutStep) === currentStep);
        activeStep?.focus({ preventScroll: true });
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

        checkoutPackages.forEach((element) => {
            element.textContent = packageItem.name;
        });

        checkoutTrees.forEach((element) => {
            element.textContent = formatTrees(packageItem.trees);
        });

        checkoutTotals.forEach((element) => {
            element.textContent = format(packageItem.price);
        });

        if (checkoutSubmitLabel) {
            checkoutSubmitLabel.textContent = 'Konfirmasi & bayar ' + format(packageItem.price);
        }

        if (overlaySummary) {
            overlaySummary.textContent = `${packageItem.name} · ${formatTrees(packageItem.trees)} · ${format(packageItem.price)}`;
        }

        syncPreview();
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

        checkoutMethods.forEach((element) => {
            element.textContent = methodLabel();
        });

        syncPreview();
    };

    const validateActiveFields = () => {
        const activeFields = Array.from(
            form.querySelectorAll('[data-pay-detail]:not([hidden]) [data-pay-field]')
        );

        return activeFields.every((field) => {
            if (field.reportValidity()) {
                return true;
            }

            field.focus({ preventScroll: true });

            return false;
        });
    };

    const openCheckout = () => {
        if (!dialog) {
            return;
        }

        render();
        syncMethod();

        const startStep = Number(dialog.dataset.checkoutStartStep || 1);
        showStep(startStep);

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
        showStep(1);
    };

    openButton?.addEventListener('click', (event) => {
        event.preventDefault();
        openCheckout();
    });

    closeButtons.forEach((button) => {
        button.addEventListener('click', closeCheckout);
    });

    nextButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const target = Number(button.dataset.checkoutNext);

            if (currentStep === 2 && target === 3 && !validateActiveFields()) {
                return;
            }

            showStep(target);
        });
    });

    backButtons.forEach((button) => {
        button.addEventListener('click', () => {
            showStep(Number(button.dataset.checkoutBack));
        });
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

    form.addEventListener('input', (event) => {
        if (event.target instanceof Element && event.target.matches('[data-pay-field]')) {
            syncPreview();
        }
    });

    form.addEventListener('submit', (event) => {
        if (submitting) {
            event.preventDefault();

            return;
        }

        if (currentStep === 1) {
            event.preventDefault();
            showStep(2);

            return;
        }

        if (currentStep === 2) {
            event.preventDefault();

            if (validateActiveFields()) {
                showStep(3);
            }

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
    showStep(1);

    if (dialog?.hasAttribute('data-checkout-open-on-load')) {
        openCheckout();
    }
})();
