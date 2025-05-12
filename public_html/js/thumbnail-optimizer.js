document.addEventListener('DOMContentLoaded', () => {
    const thumbnails = document.querySelectorAll('.slides[data-optimize-thumbnail]');

    thumbnails.forEach(thumbnail => {
        const img = new Image();
        img.onload = () => optimizeThumbnail(thumbnail, img);
        img.src = thumbnail.dataset.optimizeThumbnail;
    });

    // Initialize Masonry after all images are loaded
    const $grid = $('.grid').imagesLoaded(() => {
        $grid.masonry({
            itemSelector: '.grid-item',
            columnWidth: '.grid-sizer',
            percentPosition: true
        });
    });
});

function optimizeThumbnail(element, img) {
    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d');
    canvas.width = 300;  // Set desired thumbnail width
    canvas.height = 300; // Set desired thumbnail height

    const scale = Math.max(canvas.width / img.width, canvas.height / img.height);
    const x = (canvas.width / 2) - (img.width / 2) * scale;
    const y = (canvas.height / 2) - (img.height / 2) * scale;

    ctx.drawImage(img, x, y, img.width * scale, img.height * scale);

    updateThumbnail(element, canvas);
}

function updateThumbnail(element, canvas) {
    const optimizedImageUrl = canvas.toDataURL('image/jpeg');
    element.style.backgroundImage = `url(${optimizedImageUrl})`;
}