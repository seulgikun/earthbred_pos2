document.addEventListener('DOMContentLoaded', function() {
    // Create the toggle button
    const btn = document.createElement('button');
    btn.className = 'sidebar-toggle-btn';
    btn.innerHTML = '<i class="fa-solid fa-chevron-right"></i>';
    document.body.appendChild(btn);

    // Find the sidebar
    const sidebar = document.querySelector('.mgr-sidebar, .sidebar');
    
    if (sidebar) {
        btn.addEventListener('click', function(e) {
            e.stopPropagation(); // prevent document click from firing
            sidebar.classList.toggle('show');
            // Toggle icon
            if (sidebar.classList.contains('show')) {
                btn.innerHTML = '<i class="fa-solid fa-chevron-left"></i>';
                btn.classList.add('sidebar-open');
            } else {
                btn.innerHTML = '<i class="fa-solid fa-chevron-right"></i>';
                btn.classList.remove('sidebar-open');
            }
        });

        // Close sidebar if clicking outside of it
        document.addEventListener('click', function(e) {
            if (sidebar.classList.contains('show') && !sidebar.contains(e.target) && !btn.contains(e.target)) {
                sidebar.classList.remove('show');
                btn.innerHTML = '<i class="fa-solid fa-chevron-right"></i>';
                btn.classList.remove('sidebar-open');
            }
        });
    } else {
        // If no sidebar on this page, hide the button
        btn.style.display = 'none';
    }
});
