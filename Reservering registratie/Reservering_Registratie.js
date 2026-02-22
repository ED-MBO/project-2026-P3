// Sidebar openen en sluiten
function toggleSidebar() {
    var sidebar = document.getElementById("sidebar");
    var overlay = document.getElementById("overlay");

    sidebar.classList.toggle("open");
    overlay.classList.toggle("open");
    document.body.classList.toggle('sidebar-open');
}

function sluitSidebar() {
    document.getElementById("sidebar").classList.remove("open");
    document.getElementById("overlay").classList.remove("open");
    document.body.classList.remove('sidebar-open');
}

// Zoekfunctie — zoekt op naam lid (kolom 1) en lesnaam (kolom 2)
function zoekReservering() {
    var zoekterm = document.getElementById("zoekbalk").value.toLowerCase();
    var tabel = document.getElementById("reserveringenTabel");
    var rijen = tabel.getElementsByTagName("tr");

    for (var i = 1; i < rijen.length; i++) {
        var kolommen = rijen[i].getElementsByTagName("td");

        if (kolommen.length > 0) {
            var lidnaam  = kolommen[0].textContent.toLowerCase();
            var lesnaam  = kolommen[1].textContent.toLowerCase();

            // Laat de rij zien als de zoekterm voorkomt in de naam of lesnaam
            if (lidnaam.includes(zoekterm) || lesnaam.includes(zoekterm)) {
                rijen[i].style.display = "";
            } else {
                rijen[i].style.display = "none";
            }
        }
    }
}