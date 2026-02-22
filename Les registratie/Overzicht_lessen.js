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

// Zoekfunctie
function zoekLes() {
    var zoekterm = document.getElementById("zoekbalk").value.toLowerCase();
    var tabel = document.getElementById("lessenTabel");
    var rijen = tabel.getElementsByTagName("tr");

    for (var i = 1; i < rijen.length; i++) {
        var eersteKolom = rijen[i].getElementsByTagName("td")[0];

        if (eersteKolom) {
            var lesnaam = eersteKolom.textContent.toLowerCase();

            if (lesnaam.includes(zoekterm)) {
                rijen[i].style.display = "";
            } else {
                rijen[i].style.display = "none";
            }
        }
    }
}