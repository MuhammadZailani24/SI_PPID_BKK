// assets/js/script.js

// Fungsi Hamburger Menu (Buka/Tutup Menu Utama di Mobile)
function toggleMenu() {
    const navLinks = document.getElementById('navLinks');
    navLinks.classList.toggle('active');
}

// Fungsi Dropdown di Mobile
function toggleDropdown(element) {
    if (window.innerWidth <= 768) {
        const allItems = document.querySelectorAll('.nav-item');
        allItems.forEach(item => {
            if (item !== element) {
                item.classList.remove('active');
            }
        });
        element.classList.toggle('active');
    }
}