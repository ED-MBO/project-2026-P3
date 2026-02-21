   document.getElementById('searchInput').addEventListener('input', function () {
        const query = this.value.toLowerCase().trim();
        const rows  = document.querySelectorAll('#lessenTable tbody tr[data-naam]');
        let visible = 0;

        rows.forEach(row => {
            const match = row.dataset.naam.includes(query);
            row.style.display = match ? '' : 'none';
            if (match) visible++;
        });

        document.getElementById('resultCount').textContent = visible + ' lessen';
        document.getElementById('footerCount').textContent = visible + ' lessen weergegeven';
    });

    function openSidebar() {
        document.getElementById('sidebar').classList.add('open');
        document.getElementById('overlay').classList.add('open');
    }

    function closeSidebar() {
        document.getElementById('sidebar').classList.remove('open');
        document.getElementById('overlay').classList.remove('open');
    }