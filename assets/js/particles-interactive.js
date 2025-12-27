/**
 * INTERACTIVE PARTICLES - Gold Particle System
 * =============================================
 * Mouse-following gold particles for hero section
 */

(function () {
    'use strict';

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    class GoldParticleSystem {
        constructor(container, options = {}) {
            this.container = typeof container === 'string'
                ? document.querySelector(container)
                : container;

            if (!this.container || prefersReducedMotion) return;

            this.options = {
                particleCount: options.particleCount || 30,
                particleSize: options.particleSize || { min: 2, max: 5 },
                speed: options.speed || { min: 0.5, max: 1.5 },
                mouseInfluence: options.mouseInfluence || 100,
                fadeDistance: options.fadeDistance || 200,
                color: options.color || 'rgba(197, 160, 89, 0.6)',
                ...options
            };

            this.particles = [];
            this.mouse = { x: null, y: null };
            this.animationFrame = null;

            this.init();
        }

        init() {
            // Create canvas
            this.canvas = document.createElement('canvas');
            this.canvas.style.cssText = `
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                pointer-events: none;
                z-index: 1;
            `;
            this.container.style.position = 'relative';
            this.container.appendChild(this.canvas);

            this.ctx = this.canvas.getContext('2d');
            this.resize();

            // Create particles
            for (let i = 0; i < this.options.particleCount; i++) {
                this.particles.push(this.createParticle());
            }

            // Event listeners
            window.addEventListener('resize', () => this.resize());
            this.container.addEventListener('mousemove', (e) => this.handleMouseMove(e));
            this.container.addEventListener('mouseleave', () => {
                this.mouse.x = null;
                this.mouse.y = null;
            });

            // Start animation
            this.animate();
        }

        createParticle() {
            const { particleSize, speed } = this.options;
            return {
                x: Math.random() * this.canvas.width,
                y: Math.random() * this.canvas.height,
                size: Math.random() * (particleSize.max - particleSize.min) + particleSize.min,
                speedX: (Math.random() - 0.5) * speed.max,
                speedY: (Math.random() - 0.5) * speed.max,
                baseSpeedX: (Math.random() - 0.5) * speed.min,
                baseSpeedY: (Math.random() - 0.5) * speed.min,
                opacity: Math.random() * 0.5 + 0.3,
                pulse: Math.random() * Math.PI * 2
            };
        }

        resize() {
            const rect = this.container.getBoundingClientRect();
            this.canvas.width = rect.width;
            this.canvas.height = rect.height;
        }

        handleMouseMove(e) {
            const rect = this.canvas.getBoundingClientRect();
            this.mouse.x = e.clientX - rect.left;
            this.mouse.y = e.clientY - rect.top;
        }

        animate() {
            this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);

            this.particles.forEach(particle => {
                // Update position with base movement
                particle.x += particle.baseSpeedX;
                particle.y += particle.baseSpeedY;

                // Mouse influence
                if (this.mouse.x !== null && this.mouse.y !== null) {
                    const dx = particle.x - this.mouse.x;
                    const dy = particle.y - this.mouse.y;
                    const distance = Math.sqrt(dx * dx + dy * dy);

                    if (distance < this.options.mouseInfluence) {
                        const force = (this.options.mouseInfluence - distance) / this.options.mouseInfluence;
                        particle.x += dx * force * 0.02;
                        particle.y += dy * force * 0.02;
                    }
                }

                // Wrap around edges
                if (particle.x < 0) particle.x = this.canvas.width;
                if (particle.x > this.canvas.width) particle.x = 0;
                if (particle.y < 0) particle.y = this.canvas.height;
                if (particle.y > this.canvas.height) particle.y = 0;

                // Pulse opacity
                particle.pulse += 0.02;
                const pulseOpacity = particle.opacity + Math.sin(particle.pulse) * 0.2;

                // Draw particle
                this.ctx.beginPath();
                const gradient = this.ctx.createRadialGradient(
                    particle.x, particle.y, 0,
                    particle.x, particle.y, particle.size
                );
                gradient.addColorStop(0, `rgba(197, 160, 89, ${pulseOpacity})`);
                gradient.addColorStop(0.5, `rgba(197, 160, 89, ${pulseOpacity * 0.5})`);
                gradient.addColorStop(1, 'rgba(197, 160, 89, 0)');

                this.ctx.fillStyle = gradient;
                this.ctx.arc(particle.x, particle.y, particle.size * 2, 0, Math.PI * 2);
                this.ctx.fill();
            });

            // Draw connections between nearby particles
            this.drawConnections();

            this.animationFrame = requestAnimationFrame(() => this.animate());
        }

        drawConnections() {
            const maxDistance = 100;

            for (let i = 0; i < this.particles.length; i++) {
                for (let j = i + 1; j < this.particles.length; j++) {
                    const dx = this.particles[i].x - this.particles[j].x;
                    const dy = this.particles[i].y - this.particles[j].y;
                    const distance = Math.sqrt(dx * dx + dy * dy);

                    if (distance < maxDistance) {
                        const opacity = (1 - distance / maxDistance) * 0.15;
                        this.ctx.beginPath();
                        this.ctx.strokeStyle = `rgba(197, 160, 89, ${opacity})`;
                        this.ctx.lineWidth = 0.5;
                        this.ctx.moveTo(this.particles[i].x, this.particles[i].y);
                        this.ctx.lineTo(this.particles[j].x, this.particles[j].y);
                        this.ctx.stroke();
                    }
                }
            }
        }

        destroy() {
            if (this.animationFrame) {
                cancelAnimationFrame(this.animationFrame);
            }
            if (this.canvas && this.canvas.parentNode) {
                this.canvas.parentNode.removeChild(this.canvas);
            }
        }
    }

    /**
     * Simple CSS-based particle fallback
     */
    function createSimpleParticles(container, count = 20) {
        if (prefersReducedMotion) return;

        const layer = document.createElement('div');
        layer.className = 'particle-layer';

        for (let i = 0; i < count; i++) {
            const particle = document.createElement('div');
            particle.className = 'gold-particle';
            particle.style.cssText = `
                left: ${Math.random() * 100}%;
                top: ${Math.random() * 100}%;
                animation-delay: ${Math.random() * 5}s;
                animation-duration: ${5 + Math.random() * 5}s;
                opacity: ${0.3 + Math.random() * 0.4};
                width: ${2 + Math.random() * 4}px;
                height: ${2 + Math.random() * 4}px;
            `;
            layer.appendChild(particle);
        }

        container.appendChild(layer);
    }

    // Auto-initialize on hero sections
    function init() {
        // Try canvas particles on hero
        const heroWrapper = document.querySelector('.hero-wrapper');
        if (heroWrapper) {
            // Use canvas particles for better interactivity
            new GoldParticleSystem(heroWrapper, {
                particleCount: 25,
                particleSize: { min: 2, max: 4 },
                mouseInfluence: 150
            });
        }
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Export for external use
    window.GoldParticles = {
        GoldParticleSystem,
        createSimple: createSimpleParticles,
        init
    };

})();
