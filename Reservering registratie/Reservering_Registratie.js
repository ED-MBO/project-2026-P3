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

// Zoekfunctie — zoekt op naam lid (kolom 1) en datum (kolom 2)
function zoekReservering() {
    var zoekterm = document.getElementById("zoekbalk").value.toLowerCase();
    var tabel = document.getElementById("reserveringenTabel");
    var rijen = tabel.getElementsByTagName("tr");

    for (var i = 1; i < rijen.length; i++) {
        var kolommen = rijen[i].getElementsByTagName("td");

        if (kolommen.length > 0) {
            var lidnaam = kolommen[0].textContent.toLowerCase();
            var lesnaam = kolommen[1].textContent.toLowerCase();

            if (lidnaam.includes(zoekterm) || lesnaam.includes(zoekterm)) {
                rijen[i].style.display = "";
            } else {
                rijen[i].style.display = "none";
            }
        }
    }
}