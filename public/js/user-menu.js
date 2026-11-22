/* Close the user menu on Escape and on clicks outside of it. */
(() => {
    const closeMenus = (except) => {
        document.querySelectorAll('.user-menu[open]').forEach((menu) => {
            if (menu !== except) {
                menu.removeAttribute('open');
            }
        });
    };

    document.addEventListener('click', (event) => {
        if (!(event.target instanceof Element)) return;
        const openMenu = event.target.closest('.user-menu');
        closeMenus(openMenu);
    }, { passive: true });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeMenus(null);
        }
    });
})();
