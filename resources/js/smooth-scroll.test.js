import test from 'node:test';
import assert from 'node:assert/strict';
import { scrollElementIntoView, prefersReducedMotion } from './smooth-scroll.js';

function fakeWindow({ reducedMotion = false } = {}) {
    return {
        scrollY: 240,
        matchMedia: () => ({ matches: reducedMotion, addEventListener() {} }),
    };
}

function fakeElement() {
    return {
        calls: [],
        scrollIntoView(options) {
            this.calls.push(options);
        },
    };
}

test('uses the browser API for explicit settings navigation', () => {
    const element = fakeElement();
    scrollElementIntoView(element, { win: fakeWindow() });
    assert.deepEqual(element.calls, [{ behavior: 'smooth', block: 'start' }]);
});

test('moves immediately when the user prefers reduced motion', () => {
    const element = fakeElement();
    scrollElementIntoView(element, { win: fakeWindow({ reducedMotion: true }) });
    assert.deepEqual(element.calls, [{ behavior: 'instant', block: 'start' }]);
});

test('keeps dropdown suggestions within the nearest scroll container', () => {
    const element = fakeElement();
    scrollElementIntoView(element, { block: 'nearest', win: fakeWindow() });
    assert.deepEqual(element.calls, [{ behavior: 'smooth', block: 'nearest' }]);
});

test('does not change the page position or install navigation listeners', () => {
    const win = fakeWindow();
    const original = { ...win };
    scrollElementIntoView(fakeElement(), { win });
    assert.deepEqual(win, original);
});

test('reads reduced motion preferences again after the setting changes', () => {
    const win = fakeWindow();
    assert.equal(prefersReducedMotion(win), false);
    win.matchMedia = () => ({ matches: true });
    assert.equal(prefersReducedMotion(win), true);
});
