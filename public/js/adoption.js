/* Pemilihan paket adopsi, rincian dana, dan status pemrosesan pembayaran. */
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
    const totalOutput = form.querySelector('[data-adopt-total]');
    const submitButton = form.querySelector('[data-adopt-submit]');
    const submitLabel = form.querySelector('[data-adopt-submit-label]');
    const overlay = form.querySelector('[data-pay-overlay]');
    const overlaySummary = form.querySelector('[data-pay-summary]');
    const fundRows = Array.from(document.querySelectorAll('[data-fund-row]'));
    const fundTotal = document.querySelector('[data-fund-total]');

    const selectedPackage = () => {
        const selected = form.querySelector('input[name="paket"]:checked');

        return packages.find((item) => item.key === selected?.value) || packages[0] || null;
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

        if (overlaySummary) {
            const trees = new Intl.NumberFormat('id-ID').format(packageItem.trees);
            overlaySummary.textContent = `${packageItem.name} · ${trees} bibit · ${format(packageItem.price)}`;
        }
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

        if (submitButton) {
            submitButton.disabled = false;
        }

        form.removeAttribute('aria-busy');
        render();
    };

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
})();
