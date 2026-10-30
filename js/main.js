/**
 * SIPARES - Sistem Pembayaran Retribusi Sampah
 * Main JavaScript File
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all modules
    initPreloader();
    initNavbar();
    initMobileMenu();
    initNotificationPanel();
    initFAQ();
    initCountUp();
    initBackToTop();
    initSmoothScroll();
    initAnimations();
});

/**
 * Preloader
 */
function initPreloader() {
    const preloader = document.getElementById('preloader');
    if (preloader) {
        window.addEventListener('load', () => {
            setTimeout(() => {
                preloader.classList.add('hidden');
            }, 500);
        });
    }
}

/**
 * Navbar scroll effect
 */
function initNavbar() {
    const navbar = document.getElementById('navbar');
    if (!navbar) return;

    const handleScroll = () => {
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    };

    window.addEventListener('scroll', handleScroll);
    handleScroll(); // Initial check
}

/**
 * Mobile Menu Toggle
 */
function initMobileMenu() {
    const navToggle = document.getElementById('nav-toggle');
    const navMenu = document.getElementById('nav-menu');

    if (!navToggle || !navMenu) return;

    navToggle.addEventListener('click', () => {
        navToggle.classList.toggle('active');
        navMenu.classList.toggle('active');
    });

    // Close menu when clicking on a link
    const navLinks = navMenu.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            navToggle.classList.remove('active');
            navMenu.classList.remove('active');
        });
    });
}

/**
 * Notification Panel & Automated Notifications Manager
 */
function initNotificationPanel() {
    const btnNotification = document.getElementById('btn-notification');
    if (!btnNotification) return;

    // Pastikan container parent relatif agar panel posisinya pas
    if (btnNotification.parentElement) {
        btnNotification.parentElement.style.position = 'relative';
    }

    // Buat panel otomatis jika belum ada di DOM
    let notificationPanel = document.getElementById('notification-panel');
    if (!notificationPanel) {
        notificationPanel = document.createElement('div');
        notificationPanel.id = 'notification-panel';
        notificationPanel.className = 'notification-panel';
        notificationPanel.innerHTML = `
            <div class="notification-header">
                <h4><i class="fas fa-bell"></i> Notifikasi <span id="notif-count-text" style="font-size:0.78rem;font-weight:600;color:var(--primary-600);">(0)</span></h4>
                <button type="button" class="mark-read" id="btn-mark-all-read">Tandai semua dibaca</button>
            </div>
            <div class="notification-list" id="notification-list">
                <div class="notification-empty">
                    <i class="fas fa-spinner fa-spin"></i>
                    <p>Memuat notifikasi...</p>
                </div>
            </div>
        `;
        btnNotification.parentElement.appendChild(notificationPanel);
    }

    // Toggle panel
    btnNotification.addEventListener('click', (e) => {
        e.stopPropagation();
        const isActive = notificationPanel.classList.toggle('active');
        if (isActive) {
            loadNotifications();
        }
    });

    // Close panel when clicking outside
    document.addEventListener('click', (e) => {
        if (!notificationPanel.contains(e.target) && !btnNotification.contains(e.target)) {
            notificationPanel.classList.remove('active');
        }
    });

    // Load notifikasi dari server API
    function loadNotifications() {
        // Tentukan path API relatif yang tepat (baik dari / atau dari /user/)
        const apiPath = window.location.pathname.includes('/user/') || window.location.pathname.includes('/admin/') 
            ? '../api/notifikasi.php?action=list' 
            : 'api/notifikasi.php?action=list';

        fetch(apiPath)
            .then(res => res.json())
            .then(res => {
                if (!res.success) return;

                updateBadge(res.unread_count);
                renderNotificationList(res.data);
            })
            .catch(err => {
                console.warn('Gagal memuat notifikasi:', err);
            });
    }

    // Update badge counter di bell
    function updateBadge(count) {
        let badge = btnNotification.querySelector('.notification-badge');
        if (!badge) {
            badge = document.createElement('span');
            badge.className = 'notification-badge';
            btnNotification.appendChild(badge);
        }

        if (count > 0) {
            badge.textContent = count > 99 ? '99+' : count;
            badge.style.display = 'inline-block';
        } else {
            badge.textContent = '0';
            badge.style.display = 'none';
        }

        const countText = document.getElementById('notif-count-text');
        if (countText) {
            countText.textContent = `(${count} baru)`;
        }
    }

    // Render list HTML
    function renderNotificationList(items) {
        const listContainer = document.getElementById('notification-list');
        if (!listContainer) return;

        if (!items || items.length === 0) {
            listContainer.innerHTML = `
                <div class="notification-empty">
                    <i class="fas fa-bell-slash"></i>
                    <p>Tidak ada notifikasi saat ini</p>
                </div>
            `;
            return;
        }

        let html = '';
        items.forEach(item => {
            const isUnread = item.is_read == 0 ? 'unread' : '';
            let iconClass = 'fa-info-circle';
            let boxClass = item.tipe || 'info';

            if (item.tipe === 'tagihan') {
                iconClass = 'fa-file-invoice-dollar';
            } else if (item.tipe === 'pembayaran') {
                iconClass = 'fa-check-circle';
            } else if (item.tipe === 'warning') {
                iconClass = 'fa-exclamation-triangle';
            }

            // Tentukan link tujuan yang aman
            let targetLink = '#';
            if (item.link) {
                targetLink = item.link;
                if (!window.location.pathname.includes('/user/') && targetLink.startsWith('tagihan.php')) {
                    targetLink = 'user/' + targetLink;
                }
            }

            html += `
                <div class="notification-item ${isUnread}" data-id="${item.id}" data-link="${targetLink}">
                    <div class="notification-icon-box ${boxClass}">
                        <i class="fas ${iconClass}"></i>
                    </div>
                    <div class="notification-content">
                        <div class="notification-item-title">${escapeHtml(item.judul)}</div>
                        <div class="notification-item-text">${escapeHtml(item.pesan)}</div>
                        <div class="notification-item-time">
                            <i class="far fa-clock"></i> ${item.time_ago || 'Baru saja'}
                        </div>
                    </div>
                </div>
            `;
        });

        listContainer.innerHTML = html;

        // Klik per item
        listContainer.querySelectorAll('.notification-item').forEach(el => {
            el.addEventListener('click', function(e) {
                e.stopPropagation();
                const notifId = this.getAttribute('data-id');
                const link = this.getAttribute('data-link');

                const markApiPath = window.location.pathname.includes('/user/') || window.location.pathname.includes('/admin/')
                    ? '../api/notifikasi.php?action=mark_read'
                    : 'api/notifikasi.php?action=mark_read';

                fetch(markApiPath, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: notifId })
                }).finally(() => {
                    if (link && link !== '#') {
                        window.location.href = link;
                    } else {
                        loadNotifications();
                    }
                });
            });
        });
    }

    // Tombol tandai semua dibaca
    document.addEventListener('click', function(e) {
        if (e.target && e.target.id === 'btn-mark-all-read') {
            e.stopPropagation();
            const markAllPath = window.location.pathname.includes('/user/') || window.location.pathname.includes('/admin/')
                ? '../api/notifikasi.php?action=mark_all'
                : 'api/notifikasi.php?action=mark_all';

            fetch(markAllPath, { method: 'POST' })
                .then(r => r.json())
                .then(() => {
                    updateBadge(0);
                    const unreadItems = notificationPanel.querySelectorAll('.notification-item.unread');
                    unreadItems.forEach(i => i.classList.remove('unread'));
                });
        }
    });

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // Jalankan pemeriksaan awal otomatis badge count saat halaman dimuat
    loadNotifications();
}

/**
 * FAQ Accordion
 */
function initFAQ() {
    const faqItems = document.querySelectorAll('.faq-item');

    faqItems.forEach(item => {
        const question = item.querySelector('.faq-question');

        question.addEventListener('click', () => {
            // Close other items
            faqItems.forEach(otherItem => {
                if (otherItem !== item && otherItem.classList.contains('active')) {
                    otherItem.classList.remove('active');
                }
            });

            // Toggle current item
            item.classList.toggle('active');
        });
    });
}

/**
 * Animated Counter
 */
function initCountUp() {
    const counters = document.querySelectorAll('[data-count]');

    if (counters.length === 0) return;

    const animateCounter = (counter) => {
        const target = parseInt(counter.getAttribute('data-count'));
        const duration = 2000;
        const step = target / (duration / 16);
        let current = 0;

        const updateCounter = () => {
            current += step;
            if (current < target) {
                counter.textContent = formatNumber(Math.floor(current));
                requestAnimationFrame(updateCounter);
            } else {
                counter.textContent = formatNumber(target);
            }
        };

        updateCounter();
    };

    const formatNumber = (num) => {
        return num.toLocaleString('id-ID');
    };

    // Intersection Observer for counters
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animateCounter(entry.target);
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });

    counters.forEach(counter => observer.observe(counter));
}

/**
 * Back to Top Button
 */
function initBackToTop() {
    const backToTop = document.getElementById('back-to-top');

    if (!backToTop) return;

    window.addEventListener('scroll', () => {
        if (window.scrollY > 500) {
            backToTop.classList.add('visible');
        } else {
            backToTop.classList.remove('visible');
        }
    });

    backToTop.addEventListener('click', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
}

/**
 * Smooth Scroll for anchor links
 */
function initSmoothScroll() {
    const links = document.querySelectorAll('a[href^="#"]');

    links.forEach(link => {
        link.addEventListener('click', (e) => {
            const href = link.getAttribute('href');
            if (href === '#') return;

            const target = document.querySelector(href);
            if (target) {
                e.preventDefault();
                const offsetTop = target.offsetTop - 80; // Account for fixed navbar
                window.scrollTo({
                    top: offsetTop,
                    behavior: 'smooth'
                });
            }
        });
    });
}

/**
 * Scroll Animations
 */
function initAnimations() {
    const animatedElements = document.querySelectorAll('[data-aos]');

    if (animatedElements.length === 0) return;

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const delay = entry.target.getAttribute('data-aos-delay') || 0;
                setTimeout(() => {
                    entry.target.classList.add('aos-animate');
                }, delay);
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });

    animatedElements.forEach(el => {
        el.classList.add('aos-init');
        observer.observe(el);
    });
}

/**
 * Testimonial Slider (Simple version)
 */
function initTestimonialSlider() {
    const slider = document.querySelector('.testimonials-slider');
    const dots = document.querySelectorAll('.testimonial-dots .dot');

    if (!slider || dots.length === 0) return;

    let currentIndex = 0;

    dots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
            currentIndex = index;
            updateSlider();
        });
    });

    function updateSlider() {
        const cards = slider.querySelectorAll('.testimonial-card');
        if (cards.length === 0) return;

        const cardWidth = cards[0].offsetWidth + 32; // card width + gap
        slider.scrollTo({
            left: cardWidth * currentIndex,
            behavior: 'smooth'
        });

        dots.forEach((dot, index) => {
            dot.classList.toggle('active', index === currentIndex);
        });
    }
}

// Initialize testimonial slider after DOM is ready
document.addEventListener('DOMContentLoaded', initTestimonialSlider);
