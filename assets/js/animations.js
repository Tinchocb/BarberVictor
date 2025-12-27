/* 
 * BarberiaPRO - Premium Animations 2025
 * Powered by GSAP 3 + ScrollTrigger
 */

document.addEventListener('DOMContentLoaded', () => {
    gsap.registerPlugin(ScrollTrigger);

    // --- Hero Animations ---
    const heroTl = gsap.timeline({ defaults: { ease: "power3.out" } });

    heroTl.from(".hero-title", {
        y: 50,
        opacity: 0,
        duration: 1.2,
        delay: 0.2
    })
        .from(".hero-subtitle", {
            y: 30,
            opacity: 0,
            duration: 1
        }, "-=0.8")
        .from(".hero-btns .btn", {
            y: 20,
            opacity: 0,
            stagger: 0.2,
            duration: 0.8
        }, "-=0.6");

    // --- Scroll Animations (Fade Up) ---
    gsap.utils.toArray('.fade-in-up').forEach(elem => {
        gsap.from(elem, {
            scrollTrigger: {
                trigger: elem,
                start: "top 85%",
                toggleActions: "play none none reverse"
            },
            y: 50,
            opacity: 0,
            duration: 1,
            ease: "power3.out"
        });
    });

    // --- Cards Stagger ---
    const cards = document.querySelectorAll('.card-premium, .service-card, .barber-card');
    if (cards.length > 0) {
        gsap.from(cards, {
            scrollTrigger: {
                trigger: cards[0],
                start: "top 80%"
            },
            y: 50,
            opacity: 0,
            stagger: 0.15,
            duration: 0.8,
            ease: "back.out(1.2)"
        });
    }

    // --- Button Micro-interactions ---
    const buttons = document.querySelectorAll('.btn-primary, .btn-outline');
    buttons.forEach(btn => {
        btn.addEventListener('mouseenter', () => {
            gsap.to(btn, { scale: 1.05, duration: 0.3, ease: "power2.out" });
        });
        btn.addEventListener('mouseleave', () => {
            gsap.to(btn, { scale: 1, duration: 0.3, ease: "power2.out" });
        });
    });

    // --- Login/Form Entrance ---
    const loginBox = document.querySelector('.login-container, .wizard-container');
    if (loginBox) {
        gsap.from(loginBox, {
            scale: 0.95,
            opacity: 0,
            duration: 0.8,
            ease: "power3.out"
        });
    }
});
