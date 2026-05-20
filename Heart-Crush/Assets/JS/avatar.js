    document.addEventListener('DOMContentLoaded', function () {
        const seedSelect  = document.getElementById('avatarSeed');
        const styleSelect = document.getElementById('avatarStyle');
        const previewImg  = document.getElementById('avatarPreview');
        const seedLabel   = document.getElementById('avatarPreviewSeedLabel');
        const styleLabel  = document.getElementById('avatarPreviewStyleLabel');

        if (!seedSelect || !styleSelect || !previewImg) return;

        const styleLabels = {
            'lorelei': 'Lorelei',
            'adventurer': 'Adventurer',
            'pixel-art': 'Pixel Art',
            'bottts': 'Bottts'
        };

        function updatePreview() {
            const seed  = seedSelect.value || 'HeartCrushPlayer';
            const style = styleSelect.value || 'lorelei';
            const base  = previewImg.dataset.baseUrl || 'https://api.dicebear.com/7.x/';

            const url   = base + style + '/svg?seed=' + encodeURIComponent(seed) + '&size=160&radius=50';
            previewImg.src = url;

            if (seedLabel)  seedLabel.textContent  = seed;
            if (styleLabel) styleLabel.textContent = styleLabels[style] || style;
        }

        seedSelect.addEventListener('change', updatePreview);
        styleSelect.addEventListener('change', updatePreview);
    });