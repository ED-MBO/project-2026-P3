/* ═══════════════════════════════════════
   SCROLL REVEAL
═══════════════════════════════════════ */
const observer = new IntersectionObserver(
  (entries) => {
    entries.forEach((e, i) => {
      if (e.isIntersecting) {
        setTimeout(() => e.target.classList.add("visible"), i * 80);
        observer.unobserve(e.target);
      }
    });
  },
  { threshold: 0.12 },
);

document.querySelectorAll(".reveal").forEach((el) => observer.observe(el));

/* ═══════════════════════════════════════
   NAV ACTIVE STATE ON SCROLL
═══════════════════════════════════════ */
const sections = document.querySelectorAll("section[id]");
const navLinks = document.querySelectorAll(".nav-link");

window.addEventListener("scroll", () => {
  let current = "";
  sections.forEach((s) => {
    if (window.scrollY >= s.offsetTop - 200) current = s.getAttribute("id");
  });
  navLinks.forEach((a) => {
    a.classList.toggle("active", a.getAttribute("href") === "#" + current);
  });
});

/* ═══════════════════════════════════════
   HAMBURGER MENU
═══════════════════════════════════════ */
const hamburger = document.querySelector(".hamburger");
const nav = document.querySelector(".navbar");
const sluitNav = document.querySelector(".close-menu");
const overlay = document.querySelector(".overlay");

hamburger.addEventListener("click", () => {
  nav.classList.add("active");
  overlay.style.display = "block";
  document.body.style.overflow = "hidden";
});

function closeNav() {
  nav.classList.remove("active");
  overlay.style.display = "none";
  document.body.style.overflow = "";
}

sluitNav.addEventListener("click", closeNav);
overlay.addEventListener("click", closeNav);
