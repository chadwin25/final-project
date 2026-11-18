const imageInput = document.getElementById('image');
const imagePreview = document.getElementById('image-preview');
const imagePlaceholder = document.getElementById('image-placeholder');

if (imageInput && imagePreview && imagePlaceholder) {
    let previewUrl = null;

    imageInput.addEventListener('change', () => {
        if (previewUrl) {
            URL.revokeObjectURL(previewUrl);
            previewUrl = null;
        }

        const file = imageInput.files?.[0];

        if (!file) {
            imagePreview.hidden = true;
            imagePlaceholder.hidden = false;
            return;
        }

        if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
            imagePreview.hidden = true;
            imagePlaceholder.hidden = false;
            return;
        }

        previewUrl = URL.createObjectURL(file);
        imagePreview.src = previewUrl;
        imagePreview.alt = `Preview of ${file.name}`;
        imagePreview.hidden = false;
        imagePlaceholder.hidden = true;
    });

    window.addEventListener('beforeunload', () => {
        if (previewUrl) {
            URL.revokeObjectURL(previewUrl);
        }
    });
}