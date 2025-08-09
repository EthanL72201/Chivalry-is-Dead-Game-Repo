// Gym Page Performance Optimizer
(function() {
    'use strict';
    
    // Debounce function for performance
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
    
    // Initialize gym page optimizations
    function initGymOptimizations() {
        // Lazy load progress bar animations
        const progressBars = document.querySelectorAll('.modern-progress-bar');
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const bar = entry.target;
                    const width = bar.style.width;
                    bar.style.width = '0%';
                    
                    // Use requestAnimationFrame for smooth animation
                    requestAnimationFrame(() => {
                        bar.style.width = width;
                    });
                    
                    observer.unobserve(bar);
                }
            });
        }, { threshold: 0.1 });
        
        progressBars.forEach(bar => observer.observe(bar));
        
        // Optimize quick action buttons
        const quickButtons = document.querySelectorAll('.quick-action-btn');
        const amountInput = document.getElementById('amnt');
        
        if (quickButtons.length && amountInput) {
            quickButtons.forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Use classList for better performance
                    quickButtons.forEach(b => b.classList.remove('selected'));
                    this.classList.add('selected');
                    
                    // Update input value
                    amountInput.value = this.dataset.amount;
                    
                    // Trigger change event for validation
                    amountInput.dispatchEvent(new Event('change'));
                });
            });
            
            // Add keyboard shortcuts for quick actions
            document.addEventListener('keydown', function(e) {
                if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
                
                const keyMap = {
                    '1': 0, '2': 1, '3': 2, '4': 3,
                    '5': 4, '6': 5, '7': 6, '8': 7
                };
                
                if (keyMap[e.key] !== undefined && quickButtons[keyMap[e.key]]) {
                    quickButtons[keyMap[e.key]].click();
                }
            });
        }
        
        // Add input validation
        if (amountInput) {
            const maxEnergy = parseInt(amountInput.getAttribute('max'));
            
            amountInput.addEventListener('input', debounce(function() {
                let value = parseInt(this.value);
                
                if (isNaN(value) || value < 1) {
                    this.value = 1;
                } else if (value > maxEnergy) {
                    this.value = maxEnergy;
                }
                
                // Remove selected class from buttons if custom value
                const matchingBtn = Array.from(quickButtons).find(btn => 
                    parseInt(btn.dataset.amount) === parseInt(this.value)
                );
                
                quickButtons.forEach(btn => btn.classList.remove('selected'));
                if (matchingBtn) {
                    matchingBtn.classList.add('selected');
                }
            }, 300));
        }
        
        // Optimize form submission
        const trainingForm = document.querySelector('.form-modern');
        if (trainingForm) {
            trainingForm.addEventListener('submit', function(e) {
                const submitBtn = this.querySelector('.btn-train');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Training...';
                }
            });
        }
        
        // Add visual feedback for stat changes
        const statValues = document.querySelectorAll('.stat-value');
        statValues.forEach(stat => {
            const value = parseInt(stat.textContent.replace(/,/g, ''));
            if (sessionStorage.getItem(stat.id)) {
                const oldValue = parseInt(sessionStorage.getItem(stat.id));
                if (value > oldValue) {
                    stat.classList.add('stat-increased');
                    setTimeout(() => stat.classList.remove('stat-increased'), 3000);
                }
            }
            sessionStorage.setItem(stat.id, value);
        });
    }
    
    // Resource card hover effects with GPU acceleration
    function initCardEffects() {
        const cards = document.querySelectorAll('.resource-card, .stat-showcase-card');
        
        cards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.willChange = 'transform';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.willChange = 'auto';
            });
        });
    }
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            initGymOptimizations();
            initCardEffects();
        });
    } else {
        initGymOptimizations();
        initCardEffects();
    }
    
})();

// Add optimized styles for animations
const gymStyles = document.createElement('style');
gymStyles.textContent = `
    .stat-increased {
        animation: pulseGreen 1s ease;
        color: #28a745 !important;
    }
    
    @keyframes pulseGreen {
        0% { transform: scale(1); }
        50% { transform: scale(1.2); color: #28a745; }
        100% { transform: scale(1); }
    }
    
    .modern-progress-bar {
        will-change: width;
    }
    
    .resource-card,
    .stat-showcase-card {
        transform: translateZ(0);
        backface-visibility: hidden;
    }
    
    .btn-train:disabled {
        opacity: 0.7;
        cursor: not-allowed;
    }
`;
document.head.appendChild(gymStyles);