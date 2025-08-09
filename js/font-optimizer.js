// Font Loading Optimization Script
// Preloads critical fonts and manages font loading strategy

(function() {
    'use strict';
    
    // Critical Font Awesome fonts to preload
    const criticalFonts = [
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/webfonts/fa-solid-900.woff2',
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/webfonts/fa-regular-400.woff2'
    ];
    
    // Function to preload fonts
    function preloadFont(url) {
        const link = document.createElement('link');
        link.rel = 'preload';
        link.as = 'font';
        link.type = 'font/woff2';
        link.crossOrigin = 'anonymous';
        link.href = url;
        document.head.appendChild(link);
    }
    
    // Preload critical fonts
    criticalFonts.forEach(font => {
        preloadFont(font);
    });
    
    // Font Face Observer pattern for better font loading
    if ('fonts' in document) {
        // Modern browsers - use Font Loading API
        Promise.all([
            document.fonts.load('400 1em Inter'),
            document.fonts.load('600 1em Inter'),
            document.fonts.load('700 1em Inter')
        ]).then(() => {
            document.documentElement.classList.add('fonts-loaded');
        }).catch(() => {
            // Fallback if fonts fail to load
            document.documentElement.classList.add('fonts-failed');
        });
    } else {
        // Fallback for older browsers
        setTimeout(() => {
            document.documentElement.classList.add('fonts-loaded');
        }, 3000);
    }
    
    // Optimize Font Awesome loading
    function optimizeFontAwesome() {
        // Get all Font Awesome icons on the page
        const icons = document.querySelectorAll('[class*="fa-"]');
        const usedIcons = new Set();
        
        icons.forEach(icon => {
            const classes = icon.className.split(' ');
            classes.forEach(cls => {
                if (cls.startsWith('fa-')) {
                    usedIcons.add(cls);
                }
            });
        });
        
        // Log which icons are used (for potential subsetting)
        if (usedIcons.size > 0) {
            console.log('Font Awesome icons in use:', Array.from(usedIcons));
        }
    }
    
    // Resource hints for next page navigation
    function addResourceHints() {
        const links = document.querySelectorAll('a[href]');
        const domains = new Set();
        
        links.forEach(link => {
            try {
                const url = new URL(link.href);
                if (url.hostname && url.hostname !== window.location.hostname) {
                    domains.add(url.hostname);
                }
            } catch (e) {
                // Invalid URL, skip
            }
        });
        
        // Add DNS prefetch for external domains
        domains.forEach(domain => {
            const link = document.createElement('link');
            link.rel = 'dns-prefetch';
            link.href = '//' + domain;
            document.head.appendChild(link);
        });
    }
    
    // Initialize optimizations when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            optimizeFontAwesome();
            addResourceHints();
        });
    } else {
        optimizeFontAwesome();
        addResourceHints();
    }
    
    // Progressive enhancement for font loading
    window.addEventListener('load', () => {
        // Check if fonts are taking too long
        setTimeout(() => {
            if (!document.documentElement.classList.contains('fonts-loaded')) {
                console.warn('Fonts are taking longer than expected to load');
                document.documentElement.classList.add('fonts-timeout');
            }
        }, 5000);
    });
    
})();

// CSS to handle font loading states
const fontLoadingStyles = document.createElement('style');
fontLoadingStyles.textContent = `
    /* Hide text briefly while custom fonts load */
    .fonts-loading body {
        opacity: 0.9;
    }
    
    /* Show text with system fonts first */
    body {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }
    
    /* Apply custom fonts when loaded */
    .fonts-loaded body {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        opacity: 1;
        transition: opacity 0.3s ease;
    }
    
    /* Fallback styles if fonts fail */
    .fonts-failed body,
    .fonts-timeout body {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        opacity: 1;
    }
    
    /* Optimize icon rendering */
    [class*="fa-"] {
        display: inline-block;
        font-style: normal;
        font-variant: normal;
        text-rendering: auto;
        line-height: 1;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
        will-change: auto;
    }
`;
document.head.appendChild(fontLoadingStyles);