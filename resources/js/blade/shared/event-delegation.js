/**
 * Event Delegation System
 * Handles all click events through data attributes instead of inline onclick
 */

export function initEventDelegation() {
    // Global click handler using event delegation
    document.addEventListener('click', function(e) {
        const target = e.target.closest('[data-action]');
        if (!target) return;

        const action = target.dataset.action;
        const params = target.dataset.params ? JSON.parse(target.dataset.params) : {};
        
        // Prevent default for certain actions
        if (target.dataset.preventDefault === 'true') {
            e.preventDefault();
        }

        // Route to appropriate handler
        handleAction(action, params, target, e);
    });

    // Global change handler for selects
    document.addEventListener('change', function(e) {
        const target = e.target.closest('[data-change-action]');
        if (!target) return;

        const action = target.dataset.changeAction;
        const params = target.dataset.params ? JSON.parse(target.dataset.params) : {};
        
        handleAction(action, params, target, e);
    });

    // Global input handler
    document.addEventListener('input', function(e) {
        const target = e.target.closest('[data-input-action]');
        if (!target) return;

        const action = target.dataset.inputAction;
        const params = target.dataset.params ? JSON.parse(target.dataset.params) : {};
        
        // Pass the input value as the first argument
        if (typeof window[action] === 'function') {
            window[action](target.value);
        } else {
            handleAction(action, params, target, e);
        }
    });

    // Global keydown handler
    document.addEventListener('keydown', function(e) {
        const target = e.target.closest('[data-keydown-action]');
        if (!target) return;

        const action = target.dataset.keydownAction;
        const key = target.dataset.key || 'Enter';
        
        if (e.key === key) {
            const params = target.dataset.params ? JSON.parse(target.dataset.params) : {};
            handleAction(action, params, target, e);
        }
    });
}

function handleAction(action, params, element, event) {
    // Navigation actions
    if (action === 'navigate') {
        const url = params.url || element.dataset.url;
        if (url) window.location.href = url;
        return;
    }

    // Call window-scoped functions (for backward compatibility)
    if (typeof window[action] === 'function') {
        const args = params.args || [];
        window[action](...args);
        return;
    }

    // Log unhandled actions in development
    if (process.env.NODE_ENV === 'development') {
        console.warn(`[EventDelegation] Unhandled action: ${action}`, params);
    }
}

/**
 * Image error handler - replaces inline onerror
 */
export function initImageErrorHandling() {
    document.addEventListener('error', function(e) {
        if (e.target.tagName !== 'IMG') return;
        
        const img = e.target;
        const fallbackAction = img.dataset.errorAction;
        
        if (fallbackAction === 'hide-show-next') {
            img.style.display = 'none';
            if (img.nextElementSibling) {
                img.nextElementSibling.style.display = 'flex';
            }
        } else if (fallbackAction === 'hide') {
            img.style.display = 'none';
        }
    }, true); // Use capture phase for error events
}

/**
 * Initialize all delegation systems
 */
export function init() {
    initEventDelegation();
    initImageErrorHandling();
}
