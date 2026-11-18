document.querySelectorAll('form[data-confirm-message]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        const message = form.dataset.confirmMessage;

        if (message && !window.confirm(message)) {
            event.preventDefault();
        }
    });
});