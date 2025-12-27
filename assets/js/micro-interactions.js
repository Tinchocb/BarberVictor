/**
 * MICRO-INTERACTIONS - Premium Button & Card Effects
 * ===================================================
 * Ripple effects, magnetic buttons, 3D tilt cards
 */

(function () {
    'use strict';

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /**
     * Initialize all micro-interactions
     */
    function init() {
        if (prefersReducedMotion) return;

        initRippleEffects();
        initMagneticButtons();
        initTiltCards();
        initInputAnimations();
        initShineEffects();
    }

    /**
     * Ripple effect on click
     */
    function initRippleEffects() {
        document.querySelectorAll('.ripple-effect, .btn-gold, .btn-primary-luxury, [data-ripple]').forEach(button => {
            button.addEventListener('click', function (e) {
                const rect = this.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                const x = e.clientX - rect.left - size / 2;
                const y = e.clientY - rect.top - size / 2;

                const ripple = document.createElement('span');
                ripple.className = 'ripple';
                ripple.style.cssText = `
                    width: ${size}px;
                    height: ${size}px;
                    left: ${x}px;
                    top: ${y}px;
                `;

                this.appendChild(ripple);

                setTimeout(() => ripple.remove(), 600);
            });
        });
    }

    /**
     * Magnetic button effect - button follows cursor
     */
    function initMagneticButtons() {
        document.querySelectorAll('.magnetic-button, [data-magnetic]').forEach(button => {
            const strength = parseFloat(button.dataset.magneticStrength) || 0.3;

            button.addEventListener('mousemove', function (e) {
                const rect = this.getBoundingClientRect();
                const centerX = rect.left + rect.width / 2;
                const centerY = rect.top + rect.height / 2;

                const deltaX = (e.clientX - centerX) * strength;
                const deltaY = (e.clientY - centerY) * strength;

                this.style.transform = `translate(${deltaX}px, ${deltaY}px)`;
            });

            button.addEventListener('mouseleave', function () {
                this.style.transform = 'translate(0, 0)';
            });
        });
    }

    /**
     * 3D Tilt card effect using Vanilla Tilt or custom implementation
     */
    function initTiltCards() {
        const tiltCards = document.querySelectorAll('.tilt-card, [data-tilt]');

        // If Vanilla Tilt is available, use it
        if (typeof VanillaTilt !== 'undefined') {
            VanillaTilt.init(tiltCards, {
                max: 8,
                speed: 400,
                glare: true,
                'max-glare': 0.15,
                perspective: 1000,
                scale: 1.02,
                transition: true,
                easing: 'cubic-bezier(0.25, 0.1, 0.25, 1)'
            });
        } else {
            // Fallback to custom implementation
            tiltCards.forEach(card => {
                const maxTilt = parseFloat(card.dataset.tiltMax) || 8;

                card.addEventListener('mousemove', function (e) {
                    const rect = this.getBoundingClientRect();
                    const centerX = rect.left + rect.width / 2;
                    const centerY = rect.top + rect.height / 2;

                    const percentX = (e.clientX - centerX) / (rect.width / 2);
                    const percentY = (e.clientY - centerY) / (rect.height / 2);

                    const tiltX = -percentY * maxTilt;
                    const tiltY = percentX * maxTilt;

                    this.style.transform = `perspective(1000px) rotateX(${tiltX}deg) rotateY(${tiltY}deg) scale(1.02)`;
                });

                card.addEventListener('mouseleave', function () {
                    this.style.transform = 'perspective(1000px) rotateX(0) rotateY(0) scale(1)';
                });
            });
        }
    }

    /**
     * Input focus animations
     */
    function initInputAnimations() {
        // Add glow effect on focus
        document.querySelectorAll('.input-premium, .input-luxury, input[type="text"], input[type="email"], input[type="password"], input[type="tel"], input[type="number"]').forEach(input => {
            // Skip inputs that are part of special components
            if (input.closest('.grid-opciones') || input.type === 'radio' || input.type === 'checkbox') return;

            input.addEventListener('focus', function () {
                this.parentElement?.classList.add('input-focused');
            });

            input.addEventListener('blur', function () {
                this.parentElement?.classList.remove('input-focused');
            });
        });
    }

    /**
     * Shine sweep effect on hover
     */
    function initShineEffects() {
        document.querySelectorAll('.shine-sweep, [data-shine]').forEach(element => {
            // Effect is handled by CSS, but we can add dynamic shine position
            element.addEventListener('mousemove', function (e) {
                const rect = this.getBoundingClientRect();
                const x = ((e.clientX - rect.left) / rect.width) * 100;
                this.style.setProperty('--shine-position', `${x}%`);
            });
        });
    }

    /**
     * Card hover lift effect with glow
     */
    function initCardHoverEffects() {
        document.querySelectorAll('.card-glass, .service-card, .feature-card-luxury').forEach(card => {
            card.addEventListener('mouseenter', function () {
                if (typeof gsap !== 'undefined') {
                    gsap.to(this, {
                        y: -8,
                        scale: 1.02,
                        duration: 0.3,
                        ease: 'power2.out'
                    });
                }
            });

            card.addEventListener('mouseleave', function () {
                if (typeof gsap !== 'undefined') {
                    gsap.to(this, {
                        y: 0,
                        scale: 1,
                        duration: 0.4,
                        ease: 'power2.out'
                    });
                }
            });
        });
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Re-initialize when page is ready (after preloader)
    document.addEventListener('pageReady', initCardHoverEffects);

    // Export for external use
    window.MicroInteractions = {
        init,
        initRippleEffects,
        initMagneticButtons,
        initTiltCards,
        reinit: init
    };

})();
