// js/login.js
window.addEventListener("DOMContentLoaded", () => {
  // Show/hide password
  const show = document.getElementById("showPass");
  const pwd = document.getElementById("password");
  if (show && pwd) {
    show.addEventListener("change", () => {
      pwd.type = show.checked ? "text" : "password";
    });
  }

  // Parallax only on brand badge/title + H1 (not cards/panel)
  const prefersReduced = window.matchMedia(
    "(prefers-reduced-motion: reduce)"
  ).matches;
  const isTouch = "ontouchstart" in window || navigator.maxTouchPoints > 0;
  const disableParallax = prefersReduced || isTouch || window.innerWidth < 992;

  if (!disableParallax) {
    const items = document.querySelectorAll(".parallax");
    document.addEventListener("mousemove", (e) => {
      const { innerWidth: w, innerHeight: h } = window;
      const x = (e.clientX / w - 0.5) * 2;
      const y = (e.clientY / h - 0.5) * 2;
      items.forEach((el, i) => {
        const strength = 6 - Math.min(i, 5);
        el.style.setProperty("--tx", `${x * strength}px`);
        el.style.setProperty("--ty", `${y * strength}px`);
      });
    });
    document.addEventListener("mouseleave", () => {
      document.querySelectorAll(".parallax").forEach((el) => {
        el.style.setProperty("--tx", "0px");
        el.style.setProperty("--ty", "0px");
      });
    });
  }
});
