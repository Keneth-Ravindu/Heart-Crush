document.addEventListener("DOMContentLoaded", () => {
    const muteBtn    = document.getElementById("mutebtn");
    const muteIcon   = muteBtn ? muteBtn.querySelector("i") : null;
    const music      = document.getElementById("music");
    const bossMusic  = document.getElementById("bossMusic");
    const volumeSlider = document.getElementById("musicVolume");

    if (!music && !bossMusic) return;

    const ENDPOINT = "../Controller/gameStateHandler.php";
    const LS_VOLUME_KEY = "hc_music_volume";

    let isMuted   = false;
    let bossMode  = false; // false = normal music, true = boss music
    let masterVolume = 1;  // 0..1

    // Load saved volume from localStorage
    (function initVolume() {
        const saved = localStorage.getItem(LS_VOLUME_KEY);
        if (saved !== null) {
            const v = parseFloat(saved);
            if (!isNaN(v)) {
                masterVolume = Math.min(1, Math.max(0, v));
            }
        }
        if (volumeSlider) {
            volumeSlider.value = masterVolume;
        }
        applyVolume();
    })();

    function applyVolume() {
        // Apply master volume to both tracks.
        // Mute is handled separately with .muted.
        if (music) {
            music.volume = masterVolume;
        }
        if (bossMusic) {
            bossMusic.volume = masterVolume;
        }
    }

    function safePlay(audio) {
        if (!audio) return;
        audio.muted = isMuted;
        if (!isMuted && audio.paused) {
            audio.play().catch(() => {
            });
        }
    }

    function safePause(audio) {
        if (!audio) return;
        audio.pause();
    }

    function updateIcon() {
        if (!muteIcon) return;
        muteIcon.classList.toggle("bi-volume-up-fill", !isMuted);
        muteIcon.classList.toggle("bi-volume-mute-fill", isMuted);
    }

    function updatePlayback() {
        // Decide which track should be active
        if (bossMode) {
            safePause(music);
            if (!isMuted) {
                safePlay(bossMusic);
            } else if (bossMusic) {
                bossMusic.muted = true;
            }
        } else {
            safePause(bossMusic);
            if (!isMuted) {
                safePlay(music);
            } else if (music) {
                music.muted = true;
            }
        }

        if (music) {
            music.muted = isMuted;
        }
        if (bossMusic) {
            bossMusic.muted = isMuted;
        }

        applyVolume();
        updateIcon();
    }

    async function loadMuteFromServer() {
        try {
            const res = await fetch(`${ENDPOINT}?action=get_audio`, {
                credentials: "same-origin"
            });

            // If not logged in or some error
            if (!res.ok) return;

            const data = await res.json();
            if (data && data.status === "ok" && typeof data.is_muted !== "undefined") {
                isMuted = !!data.is_muted;
                updatePlayback();
            }
        } catch (err) {
            console.warn("[HC] Could not load audio state from server:", err);
        }
    }

    function persistMuteToServer() {
        try {
            const body = new URLSearchParams();
            body.append("action", "set_audio");
            body.append("is_muted", isMuted ? "1" : "0");

            fetch(ENDPOINT, {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                credentials: "same-origin",
                body: body.toString()
            }).catch(() => {});
        } catch (err) {
            console.warn("[HC] Could not save audio state:", err);
        }
    }

    // Public API for the game (boss rounds)
    window.setBossMusicMode = function (isBoss) {
        bossMode = !!isBoss;
        updatePlayback();
    };

    // Mute button click
    if (muteBtn) {
        muteBtn.addEventListener("click", () => {
            isMuted = !isMuted;
            updatePlayback();
            persistMuteToServer();
        });
    }

    // Volume slider change
    if (volumeSlider) {
        volumeSlider.addEventListener("input", (e) => {
            const v = parseFloat(e.target.value);
            if (isNaN(v)) return;
            masterVolume = Math.min(1, Math.max(0, v));
            localStorage.setItem(LS_VOLUME_KEY, String(masterVolume));
            applyVolume();
        });
    }

    updatePlayback();
    loadMuteFromServer();
});
