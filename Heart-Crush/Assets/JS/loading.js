const HC_LOADING_MIN_TIME = 8000; // 8 seconds

// Fun fact loader
async function hcLoadFunFact() {
    const funFactEl = document.getElementById('hcFunFact');
    if (!funFactEl) return;

    try {
        const res = await fetch('https://uselessfacts.jsph.pl/random.json?language=en');
        if (!res.ok) {
            throw new Error('Network error');
        }
        const data = await res.json();
        funFactEl.textContent =
            data.text ||
            'Facts are shy today, but your puzzle instincts are not!';
    } catch (err) {
        console.error('Error loading fun fact:', err);
        funFactEl.textContent =
            'Could not load a fun fact this time, but your HeartCrush run will be legendary!';
    }
}

// Falling hearts
function hcCreateFallingHearts() {
    const container = document.querySelector('.hc-loading-backdrop');
    if (!container) return;

    const HEART_COUNT = 400;

    for (let i = 0; i < HEART_COUNT; i++) {
        const heart = document.createElement('div');
        heart.className = 'hc-loading-heart';
        heart.textContent = '❤️';

        const left = Math.random() * 100;
        const delay = Math.random() * 1.5;
        const duration = 10 + Math.random() * 8;
        const scale = 0.6 + Math.random() * 1.0;

        heart.style.left = `${left}%`;
        heart.style.animationDelay = `${delay.toFixed(2)}s`;
        heart.style.animationDuration = `${duration.toFixed(2)}s`;
        heart.style.transform = `scale(${scale.toFixed(2)})`;

        container.appendChild(heart);
    }
}


// Starfield
function hcCreateStarfield() {
    const starsContainer = document.querySelector('.hc-loading-stars');
    if (!starsContainer) return;

    const STAR_COUNT = 120;

    for (let i = 0; i < STAR_COUNT; i++) {
        const star = document.createElement('div');
        star.className = 'hc-loading-star';

        const left = Math.random() * 100;
        const top = Math.random() * 100;
        const delay = Math.random() * 4;
        const duration = 3 + Math.random() * 5;
        const scale = 0.4 + Math.random() * 0.9;

        star.style.left = `${left}%`;
        star.style.top = `${top}%`;
        star.style.animationDelay = `${delay.toFixed(2)}s`;
        star.style.animationDuration = `${duration.toFixed(2)}s`;
        star.style.transform = `scale(${scale.toFixed(2)})`;

        starsContainer.appendChild(star);
    }
}

// smooth page-out transition + redirect
function hcStartLoadingSequence() {
    const progressFill = document.querySelector('.hc-loading-progress-bar-fill');
    if (progressFill) {
        progressFill.classList.add('hc-loading-progress-bar-fill--animate');
    }

    const body = document.querySelector('.hc-loading-body');
    let redirected = false;

    setTimeout(() => {
        if (redirected) return;
        redirected = true;

        if (body) {
            // triggers the CSS page-out animation
            body.classList.add('hc-loading-body--fade-out');
        }

        // wait for animation to finish before redirect
        setTimeout(() => {
            window.location.href = 'index.php';
        }, 950); // slightly longer than CSS animation (0.9s)
    }, HC_LOADING_MIN_TIME);
}

// Init
document.addEventListener('DOMContentLoaded', () => {
    hcLoadFunFact();
    hcCreateStarfield();
    hcCreateFallingHearts();
    hcStartLoadingSequence();
});
