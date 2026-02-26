document.addEventListener("DOMContentLoaded", function () {
    // NAVBAR
    const hamburger = document.querySelector(".hamburger");
    const navMenu = document.querySelector(".navbar");
    const closeBtn = document.querySelector(".close-menu");
    const overlay = document.querySelector(".overlay");

    hamburger.addEventListener("click", function () {
        navMenu.classList.add("active");
        overlay.style.display = "block";
        document.body.style.overflow = "hidden";
    });

    function closeMenu() {
        navMenu.classList.remove("active");
        overlay.style.display = "none";
        document.body.style.overflow = "auto";
    }

    closeBtn.addEventListener("click", closeMenu);
    overlay.addEventListener("click", closeMenu);
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