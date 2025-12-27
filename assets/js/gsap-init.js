/**
 * GSAP INITIALIZATION - Premium Animation Engine
 * ===============================================
 * Core GSAP setup with ScrollTrigger and global animations
 */

(function() {
    'use strict';

    // Check for reduced motion preference
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    
    // Wait for GSAP to be loaded
    function initGSAP() {
        if (typeof gsap === 'undefined') {
            console.warn('GSAP not loaded');
            return;
        }

        // Register ScrollTrigger if available
        if (typeof ScrollTrigger !== 'undefined') {
            gsap.registerPlugin(ScrollTrigger);
        }

        // Set GSAP defaults
        gsap.defaults({
            ease: 'power2.out',
            duration: prefersReducedMotion ? 0.01 : 0.6
        });

        // Initialize all animations
        if (!prefersReducedMotion) {
            initScrollAnimations();
            initParallax();
            initPreloader();
            initHeaderEffects();
        } else {
            // Reveal all elements immediately for reduced motion
            revealAllElements();
        }
    }

    /**
     * Scroll-triggered reveal animations (replacing AOS)
     */
    function initScrollAnimations() {
        if (typeof ScrollTrigger === 'undefined') return;

        // Fade up elements
        gsap.utils.toArray('[data-reveal="fade-up"]').forEach((elem, i) => {
            gsap.fromTo(elem,
                { y: 40, opacity: 0 },
                {
                    y: 0,
                    opacity: 1,
                    duration: 0.8,
                    ease: 'power2.out',
                    scrollTrigger: {
                        trigger: elem,
                        start: 'top 85%',
                        toggleActions: 'play none none none'
                    },
                    delay: elem.dataset.revealDelay ? parseFloat(elem.dataset.revealDelay) : 0
                }
            );
        });

        // Fade down elements
        gsap.utils.toArray('[data-reveal="fade-down"]').forEach(elem => {
            gsap.fromTo(elem,
                { y: -40, opacity: 0 },
                {
                    y: 0,
                    opacity: 1,
                    duration: 0.8,
                    ease: 'power2.out',
                    scrollTrigger: {
                        trigger: elem,
                        start: 'top 85%',
                        toggleActions: 'play none none none'
                    },
                    delay: elem.dataset.revealDelay ? parseFloat(elem.dataset.revealDelay) : 0
                }
            );
        });

        // Scale in elements
        gsap.utils.toArray('[data-reveal="scale"]').forEach(elem => {
            gsap.fromTo(elem,
                { scale: 0.9, opacity: 0 },
                {
                    scale: 1,
                    opacity: 1,
                    duration: 0.7,
                    ease: 'power2.out',
                    scrollTrigger: {
                        trigger: elem,
                        start: 'top 85%',
                        toggleActions: 'play none none none'
                    }
                }
            );
        });

        // Stagger children reveal
        gsap.utils.toArray('[data-stagger-children]').forEach(container => {
            const children = container.children;
            const staggerDelay = parseFloat(container.dataset.staggerChildren) || 0.1;
            
            gsap.fromTo(children,
                { y: 30, opacity: 0 },
                {
                    y: 0,
                    opacity: 1,
                    duration: 0.6,
                    stagger: staggerDelay,
                    ease: 'power2.out',
                    scrollTrigger: {
                        trigger: container,
                        start: 'top 80%',
                        toggleActions: 'play none none none'
                    }
                }
            );
        });
    }

    /**
     * Parallax scroll effects
     */
    function initParallax() {
        if (typeof ScrollTrigger === 'undefined') return;

        gsap.utils.toArray('.parallax-bg').forEach(bg => {
            const speed = parseFloat(bg.dataset.parallaxSpeed) || 0.5;
            
            gsap.to(bg, {
                y: () => window.innerHeight * speed * -0.3,
                ease: 'none',
                scrollTrigger: {
                    trigger: bg.parentElement,
                    start: 'top bottom',
                    end: 'bottom top',
                    scrub: 1
                }
            });
        });

        // Hero content parallax (slower)
        gsap.utils.toArray('.hero-content').forEach(content => {
            gsap.to(content, {
                y: 100,
                opacity: 0.3,
                ease: 'none',
                scrollTrigger: {
                    trigger: content.closest('.hero-wrapper') || content,
                    start: 'top top',
                    end: 'bottom top',
                    scrub: 1
                }
            });
        });
    }

    /**
     * Preloader exit animation
     */
    function initPreloader() {
        const preloader = document.getElementById('preloader');
        if (!preloader) return;

        window.addEventListener('load', () => {
            gsap.timeline()
                .to(preloader.querySelector('.preloader-spinner'), {
                    scale: 0.8,
                    opacity: 0,
                    duration: 0.4,
                    ease: 'power2.in'
                })
                .to(preloader.querySelector('.preloader-logo'), {
                    y: -20,
                    opacity: 0,
                    duration: 0.3,
                    ease: 'power2.in'
                }, '-=0.2')
                .to(preloader, {
                    opacity: 0,
                    duration: 0.5,
                    ease: 'power2.out',
                    onComplete: () => {
                        preloader.style.display = 'none';
                        // Trigger page entrance animations
                        document.dispatchEvent(new CustomEvent('pageReady'));
                    }
                });
        });
    }

    /**
     * Header scroll effects
     */
    function initHeaderEffects() {
        const header = document.getElementById('header') || document.querySelector('header');
        if (!header) return;

        let lastScrollY = 0;
        let ticking = false;

        // Debounced scroll handler
        window.addEventListener('scroll', () => {
            if (!ticking) {
                window.requestAnimationFrame(() => {
                    const currentScrollY = window.scrollY;
                    
                    // Add scrolled class for styling
                    if (currentScrollY > 100) {
                        header.classList.add('scrolled');
                    } else {
                        header.classList.remove('scrolled');
                    }

                    // Hide/show on scroll direction (optional)
                    if (currentScrollY > lastScrollY && currentScrollY > 300) {
                        gsap.to(header, { y: -100, duration: 0.3, ease: 'power2.in' });
                    } else {
                        gsap.to(header, { y: 0, duration: 0.3, ease: 'power2.out' });
                    }

                    lastScrollY = currentScrollY;
                    ticking = false;
                });
                ticking = true;
            }
        });
    }

    /**
     * Reveal all elements for reduced motion preference
     */
    function revealAllElements() {
        document.querySelectorAll('[data-reveal], [data-stagger-children] > *').forEach(el => {
            el.style.opacity = '1';
            el.style.transform = 'none';
        });
    }

    /**
     * Hero text stagger reveal
     */
    window.revealHeroText = function(selector) {
        if (prefersReducedMotion) return;
        
        const element = document.querySelector(selector);
        if (!element) return;

        // Split text into spans if not already
        if (!element.querySelector('span')) {
            const words = element.textContent.split(' ');
            element.innerHTML = words.map(word => `<span>${word}</span>`).join(' ');
        }

        gsap.fromTo(element.querySelectorAll('span'),
            { y: '100%', opacity: 0 },
            { 
                y: '0%', 
                opacity: 1, 
                duration: 0.8, 
                stagger: 0.1,
                ease: 'power3.out'
            }
        );
    };

    /**
     * Counter animation for KPIs
     */
    window.animateCounter = function(element, endValue, duration = 1.5, prefix = '', suffix = '') {
        if (prefersReducedMotion) {
            element.textContent = prefix + endValue + suffix;
            return;
        }

        const counter = { value: 0 };
        gsap.to(counter, {
            value: endValue,
            duration: duration,
            ease: 'power2.out',
            onUpdate: () => {
                element.textContent = prefix + Math.round(counter.value).toLocaleString('es-AR') + suffix;
            }
        });
    };

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initGSAP);
    } else {
        initGSAP();
    }

    // Export for external use
    window.GSAPInit = {
        initScrollAnimations,
        initParallax,
        revealHeroText: window.revealHeroText,
        animateCounter: window.animateCounter,
        prefersReducedMotion
    };

})();
