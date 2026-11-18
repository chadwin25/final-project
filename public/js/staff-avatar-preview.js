document.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById('avatar');
    const preview = document.getElementById('staff-avatar-preview');

    if (!input || !preview) {
        return;
    }

    let previewUrl = null;
    const originalMarkup = preview.innerHTML;

    input.addEventListener('change', () => {
        if (previewUrl) {
            URL.revokeObjectURL(previewUrl);
            previewUrl = null;
        }

        const file = input.files && input.files[0];

        if (!file) {
            preview.innerHTML = originalMarkup;
            return;
        }

        if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
            preview.textContent = 'Choose a JPG, PNG, or WebP image';
            return;
        }

        previewUrl = URL.createObjectURL(file);
        const image = document.createElement('img');
        image.src = previewUrl;
        image.alt = 'Selected avatar preview';
        preview.replaceChildren(image);
    });

    window.addEventListener('beforeunload', () => {
        if (previewUrl) {
            URL.revokeObjectURL(previewUrl);
        }
    });
});