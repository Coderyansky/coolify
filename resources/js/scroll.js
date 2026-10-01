export const REDUCED_MOTION_QUERY = '(prefers-reduced-motion: reduce)';

/**
 * @param {Window} win
 * @returns {boolean}
 */
export function prefersReducedMotion(win) {
    return typeof win.matchMedia === 'function' && win.matchMedia(REDUCED_MOTION_QUERY).matches;
}

/**
 * Explicit navigation uses the browser scroll API; root and nested wheel
 * scrolling and history restoration remain under browser/Livewire control.
 * @param {Element} element
 * @param {{ block?: ScrollLogicalPosition, win?: Window }} [options]
 */
export function scrollElementIntoView(element, { block = 'start', win = window } = {}) {
    element.scrollIntoView({
        behavior: prefersReducedMotion(win) ? 'instant' : 'smooth',
        block,
    });
}
