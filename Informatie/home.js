document.addEventListener("DOMContentLoaded", function () {
    const hamburgerButton = document.querySelector(".hamburger");
    const navigatieMenu = document.querySelector(".navbar");
    const sluitKnop = document.querySelector(".close-menu");
    const overlayElement = document.querySelector(".overlay");
 
    function openNavigatie() {
        navigatieMenu.classList.add("active");
        overlayElement.style.display = "block";
        document.body.style.overflow = "hidden";
    }

    function sluitNavigatie() {
        navigatieMenu.classList.remove("active");
        overlayElement.style.display = "none";
        document.body.style.overflow = "auto";
    }

    hamburgerButton.addEventListener("click", openNavigatie);
    sluitKnop.addEventListener("click", sluitNavigatie);
    overlayElement.addEventListener("click", sluitNavigatie);
});
