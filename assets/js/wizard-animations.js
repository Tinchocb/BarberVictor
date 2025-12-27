/**
 * WIZARD ANIMATIONS - Reservation Flow Premium Transitions
 * =========================================================
 * Smooth step transitions, progress bar animations, date pop-ins
 */

(function () {
    'use strict';

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /**
     * Animate wizard step transition
     * @param {number} fromStep - Current step number
     * @param {number} toStep - Target step number
     * @param {Function} callback - Callback after transition
     */
    window.animateWizardStep = function (fromStep, toStep, callback) {
        if (prefersReducedMotion) {
            // Simple show/hide for reduced motion
            document.querySelector(`.paso-${fromStep}`)?.classList.remove('active');
            document.querySelector(`.paso-${toStep}`)?.classList.add('active');
            updateProgressBar(toStep);
            if (callback) callback();
            return;
        }

        const currentStep = document.querySelector(`.paso-${fromStep}`);
        const nextStep = document.querySelector(`.paso-${toStep}`);

        if (!currentStep || !nextStep) {
            if (callback) callback();
            return;
        }

        const direction = toStep > fromStep ? 1 : -1;
        const duration = 0.4;

        if (typeof gsap !== 'undefined') {
            // GSAP animation
            const tl = gsap.timeline({
                onComplete: () => {
                    currentStep.classList.remove('active');
                    currentStep.style.cssText = '';
                    nextStep.classList.add('active');
                    nextStep.style.cssText = '';
                    if (callback) callback();
                }
            });

            tl.to(currentStep, {
                x: -60 * direction,
                opacity: 0,
                duration: duration * 0.6,
                ease: 'power2.in'
            })
                .set(nextStep, {
                    display: 'block',
                    x: 60 * direction,
                    opacity: 0
                })
                .to(nextStep, {
                    x: 0,
                    opacity: 1,
                    duration: duration,
                    ease: 'power2.out'
                }, '-=0.1');

        } else {
            // CSS fallback
            currentStep.classList.add(direction > 0 ? 'exiting-left' : 'exiting-right');

            setTimeout(() => {
                currentStep.classList.remove('active', 'exiting-left', 'exiting-right');
                nextStep.classList.add('active', direction > 0 ? 'entering-right' : 'entering-left');

                setTimeout(() => {
                    nextStep.classList.remove('entering-right', 'entering-left');
                    if (callback) callback();
                }, 400);
            }, 300);
        }

        // Animate progress bar
        updateProgressBar(toStep);

        // Animate progress step indicator
        animateProgressStep(toStep);
    };

    /**
     * Update progress bar fill
     */
    function updateProgressBar(step) {
        const totalSteps = 4;
        const progress = ((step - 1) / (totalSteps - 1)) * 100;

        const progressLine = document.querySelector('.progress-line');
        if (progressLine) {
            if (typeof gsap !== 'undefined') {
                gsap.to(progressLine, {
                    width: `${progress}%`,
                    duration: 0.5,
                    ease: 'power2.out'
                });
            } else {
                progressLine.style.width = `${progress}%`;
            }
        }

        // Update step indicators
        document.querySelectorAll('.progress-step').forEach((stepEl, i) => {
            if (i < step) {
                stepEl.classList.add('active');
            } else {
                stepEl.classList.remove('active');
            }
        });
    }

    /**
     * Animate progress step completion
     */
    function animateProgressStep(step) {
        if (prefersReducedMotion) return;

        const stepIndicator = document.querySelector(`#ind-${step} .progress-circle`);
        if (!stepIndicator) return;

        if (typeof gsap !== 'undefined') {
            gsap.fromTo(stepIndicator,
                { scale: 1 },
                {
                    scale: 1.2,
                    duration: 0.2,
                    ease: 'power2.out',
                    yoyo: true,
                    repeat: 1
                }
            );
        } else {
            stepIndicator.classList.add('progress-step-complete');
            setTimeout(() => stepIndicator.classList.remove('progress-step-complete'), 500);
        }
    }

    /**
     * Animate date cards pop-in
     */
    window.animateDateCards = function () {
        if (prefersReducedMotion) return;

        const dateCards = document.querySelectorAll('.date-card');

        if (typeof gsap !== 'undefined') {
            gsap.fromTo(dateCards,
                { scale: 0.8, y: 15, opacity: 0 },
                {
                    scale: 1,
                    y: 0,
                    opacity: 1,
                    duration: 0.4,
                    stagger: 0.05,
                    ease: 'back.out(1.7)'
                }
            );
        } else {
            dateCards.forEach((card, i) => {
                card.classList.add('date-card-animated');
                card.style.animationDelay = `${i * 0.05}s`;
            });
        }
    };

    /**
     * Animate time slot selection
     */
    window.animateTimeSlotSelection = function (slot) {
        if (prefersReducedMotion) return;

        if (typeof gsap !== 'undefined') {
            gsap.fromTo(slot,
                { scale: 1 },
                {
                    scale: 1.1,
                    duration: 0.15,
                    ease: 'power2.out',
                    yoyo: true,
                    repeat: 1
                }
            );
        }

        // Add gold pulse effect
        const pulse = document.createElement('div');
        pulse.style.cssText = `
            position: absolute;
            inset: 0;
            border-radius: inherit;
            background: rgba(197, 160, 89, 0.3);
            animation: rippleExpand 0.5s ease-out forwards;
            pointer-events: none;
        `;
        slot.style.position = 'relative';
        slot.appendChild(pulse);
        setTimeout(() => pulse.remove(), 500);
    };

    /**
     * Animate time slots loading
     */
    window.animateTimeSlotsLoad = function (container) {
        if (prefersReducedMotion) return;

        const slots = container.querySelectorAll('.btn-horario');

        if (typeof gsap !== 'undefined') {
            gsap.fromTo(slots,
                { scale: 0.9, opacity: 0 },
                {
                    scale: 1,
                    opacity: 1,
                    duration: 0.3,
                    stagger: 0.03,
                    ease: 'power2.out'
                }
            );
        }
    };

    /**
     * Animate confirm button on final step
     */
    window.animateConfirmButton = function () {
        if (prefersReducedMotion) return;

        const confirmBtn = document.querySelector('.paso-4 .btn-gold[type="submit"]');
        if (!confirmBtn) return;

        if (typeof gsap !== 'undefined') {
            gsap.fromTo(confirmBtn,
                { scale: 0.9, opacity: 0 },
                {
                    scale: 1,
                    opacity: 1,
                    duration: 0.5,
                    delay: 0.3,
                    ease: 'back.out(1.7)'
                }
            );

            // Add subtle pulse animation
            gsap.to(confirmBtn, {
                boxShadow: '0 0 30px rgba(197, 160, 89, 0.5)',
                duration: 1,
                repeat: -1,
                yoyo: true,
                ease: 'power1.inOut',
                delay: 1
            });
        }
    };

    /**
     * Animate resumen card on step 4
     */
    window.animateResumenCard = function () {
        if (prefersReducedMotion) return;

        const resumenCard = document.querySelector('.resumen-card');
        if (!resumenCard) return;

        const groups = resumenCard.querySelectorAll('.resumen-group');

        if (typeof gsap !== 'undefined') {
            gsap.fromTo(resumenCard,
                { y: 30, opacity: 0 },
                {
                    y: 0,
                    opacity: 1,
                    duration: 0.5,
                    ease: 'power2.out'
                }
            );

            gsap.fromTo(groups,
                { x: -20, opacity: 0 },
                {
                    x: 0,
                    opacity: 1,
                    duration: 0.4,
                    stagger: 0.1,
                    delay: 0.3,
                    ease: 'power2.out'
                }
            );
        }
    };

    // Export for external use
    window.WizardAnimations = {
        animateStep: window.animateWizardStep,
        animateDateCards: window.animateDateCards,
        animateTimeSlots: window.animateTimeSlotsLoad,
        animateConfirm: window.animateConfirmButton,
        animateResumen: window.animateResumenCard
    };

})();
