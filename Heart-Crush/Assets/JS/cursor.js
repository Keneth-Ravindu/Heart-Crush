document.addEventListener("DOMContentLoaded", () => {
  if (!window.matchMedia("(pointer: fine)").matches) return;

  const dot = document.querySelector(".hc-cursor");
  const ring = document.querySelector(".hc-cursor-outline");
  if (!dot || !ring) return;

  let mouseX = window.innerWidth / 2;
  let mouseY = window.innerHeight / 2;
  let ringX = mouseX;
  let ringY = mouseY;
  let hasMoved = false;

  const lerp = (a, b, n) => a + (b - a) * n;

  
  dot.style.opacity = "0";
  ring.style.opacity = "0";

  // Track mouse movement
  window.addEventListener("mousemove", (e) => {
    mouseX = e.clientX;
    mouseY = e.clientY;

    // On first move, fade them in
    if (!hasMoved) {
      hasMoved = true;
      dot.style.opacity = "1";
      ring.style.opacity = "0.9";
    }

    // Inner dot snaps immediately
    dot.style.transform = `translate(${mouseX}px, ${mouseY}px) translate(-50%, -50%)`;
  });

  // Ring follows smoothly
  function animate() {
    ringX = lerp(ringX, mouseX, 0.18);
    ringY = lerp(ringY, mouseY, 0.18);

    ring.style.transform = `translate(${ringX}px, ${ringY}px) translate(-50%, -50%)`;

    requestAnimationFrame(animate);
  }
  animate();

  // Hover detection for interactive elements
  const hoverTargets = document.querySelectorAll(
    "a, button, [role='button'], input, textarea, select, .btn, .answer-input, .answer-btn, .resetBtn, .pauseBtn"
  );

  hoverTargets.forEach((el) => {
    el.addEventListener("mouseenter", () => ring.classList.add("is-hovering"));
    el.addEventListener("mouseleave", () => ring.classList.remove("is-hovering"));
  });

  // Heart particle creator
  function spawnHeart(x, y) {
    const heart = document.createElement("div");
    heart.className = "hc-heart-click";
    heart.textContent = "❤";
    heart.style.left = x + "px";
    heart.style.top = y + "px";
    document.body.appendChild(heart);

    heart.addEventListener("animationend", () => heart.remove());
  }

  // Click animation + heart pop
  window.addEventListener("mousedown", (e) => {
    if (!hasMoved) return; // safety
    ring.classList.add("is-clicking");
    spawnHeart(e.clientX, e.clientY);
  });

  window.addEventListener("mouseup", () => {
    ring.classList.remove("is-clicking");
  });

  // Hide cursor when leaving the window
  window.addEventListener("mouseleave", () => {
    dot.style.opacity = "0";
    ring.style.opacity = "0";
  });

  window.addEventListener("mouseenter", () => {
    if (!hasMoved) return;
    dot.style.opacity = "1";
    ring.style.opacity = "0.9";
  });
});
