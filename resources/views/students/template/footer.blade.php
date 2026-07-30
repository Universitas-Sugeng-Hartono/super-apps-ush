<footer class="sticky-footer bg-white">
    <div class="container my-auto">
        <div class="copyright text-center my-auto">
            <span>Copyright &copy; Anwar Fauzi {{ now()->year }}</span>
        </div>
    </div>
</footer>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toastEls = document.querySelectorAll('.toast');
        toastEls.forEach((toastEl, index) => {
            const delay = 5000 * (index + 1); // Delay toast 5 detik berturut-turut
            const toast = new bootstrap.Toast(toastEl, {
                delay: delay
            });
            toast.show(); // Menampilkan toast
        });
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Mobile menu toggle
    const menuToggle = document.getElementById('menuToggle');
    const sidebar = document.getElementById('sidebar');

    menuToggle.addEventListener('click', () => {
        sidebar.classList.toggle('mobile-hidden');
    });

    // Navigation active state
    document.querySelectorAll('.nav-link, .mobile-nav-link').forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();

            // Remove active class from all links in the same navigation
            const isDesktop = link.classList.contains('nav-link');
            const selector = isDesktop ? '.nav-link' : '.mobile-nav-link';

            document.querySelectorAll(selector).forEach(l => l.classList.remove('active'));
            link.classList.add('active');

            // Close mobile sidebar when clicking menu item
            if (window.innerWidth <= 768) {
                sidebar.classList.add('mobile-hidden');
            }
        });
    });

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', (e) => {
        if (window.innerWidth <= 768 &&
            !sidebar.contains(e.target) &&
            !menuToggle.contains(e.target)) {
            sidebar.classList.add('mobile-hidden');
        }
    });

    // Handle window resize
    window.addEventListener('resize', () => {
        if (window.innerWidth > 768) {
            sidebar.classList.remove('mobile-hidden');
        } else {
            sidebar.classList.add('mobile-hidden');
        }
    });

    // Initialize mobile state
    if (window.innerWidth <= 768) {
        sidebar.classList.add('mobile-hidden');
    }

    // Smooth animations for cards
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    });

    document.querySelectorAll('.card').forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(card);
    });

    // Button hover effects
    document.querySelectorAll('button').forEach(btn => {
        btn.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.05)';
            this.style.transition = 'transform 0.2s ease';
        });

        btn.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
        });
    });
</script>
</body>

</html>
