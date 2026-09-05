document.addEventListener('DOMContentLoaded', () => {
    document.querySelector('[data-menu-toggle]')?.addEventListener('click', () => document.body.classList.toggle('menu-open'));

    document.querySelectorAll('[data-copy]').forEach((button) => {
        button.addEventListener('click', async () => {
            await navigator.clipboard.writeText(button.dataset.copy);
            const previous = button.textContent;
            button.textContent = 'Copied';
            window.setTimeout(() => button.textContent = previous, 1600);
        });
    });

    const photoChecks = () => [...document.querySelectorAll('.select-photo input[type="checkbox"]')];
    document.querySelector('[data-select-all]')?.addEventListener('click', () => photoChecks().forEach((input) => input.checked = true));
    document.querySelector('[data-clear-all]')?.addEventListener('click', () => photoChecks().forEach((input) => input.checked = false));

    const dialog = document.querySelector('[data-lightbox]');
    if (!dialog) return;
    const items = [...document.querySelectorAll('[data-lightbox-item]')];
    const image = dialog.querySelector('[data-lightbox-image]');
    const caption = dialog.querySelector('[data-lightbox-caption]');
    let current = 0;
    const show = (index) => { current = (index + items.length) % items.length; image.src = items[current].dataset.src; caption.textContent = items[current].dataset.name; };
    items.forEach((item, index) => item.querySelector('.photo-open').addEventListener('click', () => { show(index); dialog.showModal(); }));
    dialog.querySelector('[data-lightbox-close]').addEventListener('click', () => dialog.close());
    dialog.querySelector('[data-lightbox-prev]').addEventListener('click', () => show(current - 1));
    dialog.querySelector('[data-lightbox-next]').addEventListener('click', () => show(current + 1));
    dialog.addEventListener('click', (event) => { if (event.target === dialog) dialog.close(); });
    document.addEventListener('keydown', (event) => { if (!dialog.open) return; if (event.key === 'ArrowLeft') show(current - 1); if (event.key === 'ArrowRight') show(current + 1); });
});
