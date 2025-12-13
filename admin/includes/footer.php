    </div>
    
    <script>
    // Améliorer l'affichage des tableaux sur mobile
    document.addEventListener('DOMContentLoaded', function() {
        const tables = document.querySelectorAll('.admin-table');
        
        tables.forEach(table => {
            const headers = table.querySelectorAll('thead th');
            const rows = table.querySelectorAll('tbody tr');
            
            headers.forEach((header, index) => {
                const label = header.textContent.trim();
                rows.forEach(row => {
                    const cell = row.querySelectorAll('td')[index];
                    if (cell) {
                        cell.setAttribute('data-label', label);
                    }
                });
            });
        });
    });
    </script>
</body>
</html>

