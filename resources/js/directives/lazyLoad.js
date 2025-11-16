/**
 * Vue Lazy Load Directive
 * Usage: <img v-lazy="imageSrc" alt="..." />
 */

const observerOptions = {
    root: null,
    rootMargin: '50px',  // Start loading 50px before image is visible
    threshold: 0.01,
};

const imageCache = new Set();

export default {
    mounted(el, binding) {
        const imageSrc = binding.value;
        
        // If no src provided, do nothing
        if (!imageSrc) return;

        // If already loaded (cached), load immediately
        if (imageCache.has(imageSrc)) {
            loadImage(el, imageSrc);
            return;
        }

        // Set placeholder or loading state
        el.classList.add('lazy-loading');
        
        // Create observer
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    loadImage(el, imageSrc);
                    observer.unobserve(el);
                }
            });
        }, observerOptions);

        // Start observing
        observer.observe(el);
        
        // Store observer for cleanup
        el._lazyLoadObserver = observer;
    },

    updated(el, binding) {
        const newSrc = binding.value;
        const oldSrc = binding.oldValue;

        // If src changed, update image
        if (newSrc !== oldSrc && newSrc) {
            if (imageCache.has(newSrc)) {
                loadImage(el, newSrc);
            } else {
                // Reset and re-observe
                if (el._lazyLoadObserver) {
                    el._lazyLoadObserver.unobserve(el);
                }
                
                el.classList.add('lazy-loading');
                
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach((entry) => {
                        if (entry.isIntersecting) {
                            loadImage(el, newSrc);
                            observer.unobserve(el);
                        }
                    });
                }, observerOptions);

                observer.observe(el);
                el._lazyLoadObserver = observer;
            }
        }
    },

    unmounted(el) {
        // Cleanup observer when element is removed
        if (el._lazyLoadObserver) {
            el._lazyLoadObserver.disconnect();
            delete el._lazyLoadObserver;
        }
    },
};

function loadImage(el, src) {
    // Create a temporary image to preload
    const img = new Image();
    
    img.onload = () => {
        // Set the actual image
        el.src = src;
        el.classList.remove('lazy-loading');
        el.classList.add('lazy-loaded');
        
        // Cache this image URL
        imageCache.add(src);
        
        // Emit custom event for tracking/analytics
        el.dispatchEvent(new CustomEvent('lazy-loaded', {
            detail: { src },
        }));
    };
    
    img.onerror = () => {
        // Handle error - show placeholder or error image
        el.classList.remove('lazy-loading');
        el.classList.add('lazy-error');
        
        // Optionally set a fallback image
        if (el.dataset.fallback) {
            el.src = el.dataset.fallback;
        }
    };
    
    // Start loading
    img.src = src;
}

