</div>
    </div>

    <script>
        // Sidebar toggle for mobile
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('main-content');
            const toggleBtn = document.getElementById('toggle-sidebar');
            const showSidebarBtn = document.getElementById('show-sidebar');
            
            // Function to close sidebar
            function closeSidebar() {
                sidebar.classList.add('-translate-x-full');
                sidebar.classList.add('lg:translate-x-0');
                mainContent.classList.remove('ml-0');
                mainContent.classList.add('ml-64');
            }
            
            // Function to show sidebar
            function showSidebar() {
                sidebar.classList.remove('-translate-x-full');
                mainContent.classList.remove('ml-0');
            }
            
            // Toggle sidebar on mobile
            if (toggleBtn) {
                toggleBtn.addEventListener('click', function() {
                    sidebar.classList.add('-translate-x-full');
                    mainContent.classList.remove('ml-64');
                    mainContent.classList.add('ml-0');
                });
            }
            
            // Show sidebar on mobile
            if (showSidebarBtn) {
                showSidebarBtn.addEventListener('click', function() {
                    sidebar.classList.remove('-translate-x-full');
                    mainContent.classList.remove('ml-0');
                    mainContent.classList.add('ml-64');
                });
            }
            
            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', function(event) {
                const isClickInsideSidebar = sidebar.contains(event.target);
                const isClickOnShowSidebarBtn = showSidebarBtn && showSidebarBtn.contains(event.target);
                
                if (!isClickInsideSidebar && !isClickOnShowSidebarBtn && window.innerWidth < 1024) {
                    closeSidebar();
                }
            });
            
            // Adjust on window resize
            window.addEventListener('resize', function() {
                if (window.innerWidth >= 1024) {
                    sidebar.classList.remove('-translate-x-full');
                    mainContent.classList.remove('ml-0');
                    mainContent.classList.add('ml-64');
                } else {
                    closeSidebar();
                }
            });
            
            // Initially check for mobile
            if (window.innerWidth < 1024) {
                closeSidebar();
            }
        });
        
        // Add fade-in animation to cards
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.card');
            cards.forEach((card, index) => {
                setTimeout(() => {
                    card.classList.add('fade-in');
                }, index * 100);
            });
        });
    </script>
</body>
</html>