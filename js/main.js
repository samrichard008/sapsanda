/**
 * SAP SADA SOCIAL WELFARE & LIFE PROTECTION UNIT
 * Main Frontend Scripts
 */

document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    // 1. Sticky Navigation Bar Scroll Effect
    const navbar = document.querySelector('.navbar-sapsada');
    if (navbar) {
        window.addEventListener('scroll', function () {
            if (window.scrollY > 40) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
    }

    // 2. Animated Counter for Impact Statistics
    const counters = document.querySelectorAll('.counter-value');
    if (counters.length > 0 && 'IntersectionObserver' in window) {
        const counterObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const el = entry.target;
                    const targetStr = el.getAttribute('data-target') || el.innerText;
                    const numericValue = parseInt(targetStr.replace(/[^0-9]/g, ''), 10);
                    const suffix = targetStr.replace(/[0-9]/g, '');

                    if (!isNaN(numericValue)) {
                        let current = 0;
                        const step = Math.ceil(numericValue / 60);
                        const timer = setInterval(() => {
                            current += step;
                            if (current >= numericValue) {
                                el.innerText = numericValue.toLocaleString() + suffix;
                                clearInterval(timer);
                            } else {
                                el.innerText = current.toLocaleString() + suffix;
                            }
                        }, 25);
                    }
                    observer.unobserve(el);
                }
            });
        }, { threshold: 0.4 });

        counters.forEach(c => counterObserver.observe(c));
    }

    // 3. Simple Image Lightbox Modal
    const galleryItems = document.querySelectorAll('[data-lightbox="gallery"]');
    if (galleryItems.length > 0) {
        let modalEl = document.getElementById('sapsadaLightboxModal');
        if (!modalEl) {
            modalEl = document.createElement('div');
            modalEl.id = 'sapsadaLightboxModal';
            modalEl.className = 'modal fade';
            modalEl.tabIndex = -1;
            modalEl.innerHTML = `
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content bg-transparent border-0">
                        <div class="modal-body p-0 position-relative text-center">
                            <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3 z-3" data-bs-dismiss="modal"></button>
                            <img src="" id="lightboxModalImg" class="img-fluid rounded-4 shadow-lg" alt="Gallery Preview" style="max-height: 85vh; object-fit: contain;">
                            <div id="lightboxCaption" class="text-white mt-2 p-2 bg-dark bg-opacity-75 rounded-3 d-none"></div>
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(modalEl);
        }

        const bsModal = new bootstrap.Modal(modalEl);
        const modalImg = document.getElementById('lightboxModalImg');
        const modalCaption = document.getElementById('lightboxCaption');

        galleryItems.forEach(item => {
            item.addEventListener('click', function (e) {
                e.preventDefault();
                const imgSrc = this.getAttribute('data-src') || this.getAttribute('src') || this.getAttribute('href');
                const caption = this.getAttribute('data-caption') || this.getAttribute('alt') || '';
                if (imgSrc) {
                    modalImg.src = imgSrc;
                    if (caption) {
                        modalCaption.textContent = caption;
                        modalCaption.classList.remove('d-none');
                    } else {
                        modalCaption.classList.add('d-none');
                    }
                    bsModal.show();
                }
            });
        });
    }

    // 4. Category Filter Buttons (Gallery, Activities, Projects)
    const filterButtons = document.querySelectorAll('.filter-btn');
    if (filterButtons.length > 0) {
        filterButtons.forEach(btn => {
            btn.addEventListener('click', function () {
                filterButtons.forEach(b => b.classList.remove('active'));
                this.classList.add('active');

                const filterVal = this.getAttribute('data-filter');
                const filterTargets = document.querySelectorAll('.filter-item');

                filterTargets.forEach(target => {
                    if (filterVal === 'all' || target.getAttribute('data-category') === filterVal || target.classList.contains(filterVal)) {
                        target.style.display = '';
                    } else {
                        target.style.display = 'none';
                    }
                });
            });
        });
    }

    // 5. Auto dismiss alert messages after 5 seconds
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            if (bsAlert) {
                bsAlert.close();
            }
        }, 6000);
    });
});
