// c:/xampp/htdocs/InventoryManagementSystem/assets/js/dashboard.js
document.addEventListener('DOMContentLoaded', function() {
    // You can add JavaScript for charts, modals, or other dynamic features here.
    console.log('Admin dashboard script loaded.');
});

function toggleSidebar() {
    const sidebar = document.querySelector('aside');
    sidebar.classList.toggle('active');
}

function performSearch(val) {
    const dropdown = document.getElementById('searchDropdown');
    if (val.length < 2) {
        dropdown.style.display = 'none';
        return;
    }
    
    fetch(`../../process/search.php?q=${val}`)
        .then(response => response.text())
        .then(data => {
            dropdown.innerHTML = data;
            if (data.trim() !== '') {
                dropdown.style.display = 'block';
            } else {
                dropdown.style.display = 'none';
            }
        });
}