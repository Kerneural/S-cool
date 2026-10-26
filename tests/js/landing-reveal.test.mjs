import test from 'node:test';
import assert from 'node:assert/strict';
import { landingReveal } from '../../resources/js/landing-reveal.js';

function classList() {
    const names = new Set();
    return { add: name => names.add(name), remove: name => names.delete(name), contains: name => names.has(name) };
}

function page({ reduced = false, autoplay = false, observer = true, throwOnObserve = false } = {}) {
    const elements = [1, 2, 3].map(id => ({ id, classList: classList() }));
    const html = { classList: classList(), dataset: { landingMotion: autoplay ? 'autoplay' : '' } };
    const root = { documentElement: html, querySelectorAll: () => elements };
    const listeners = new Set();
    const media = {
        matches: reduced,
        addEventListener: (event, listener) => listeners.add(listener),
        removeEventListener: (event, listener) => listeners.delete(listener),
    };
    const observers = [];
    class Observer {
        constructor(callback) { this.callback = callback; this.observed = new Set(); this.disconnected = false; observers.push(this); }
        observe(element) { if (throwOnObserve) throw new Error('observe failed'); this.observed.add(element); }
        unobserve(element) { this.observed.delete(element); }
        disconnect() { this.disconnected = true; this.observed.clear(); }
    }
    const browser = { matchMedia: () => media, IntersectionObserver: observer ? Observer : undefined };
    return {
        elements, html, listeners, observers,
        start: () => landingReveal(root, browser),
        enter: element => observers.at(-1).callback([{ isIntersecting: true, target: element }]),
        preferReducedMotion() { media.matches = true; [...listeners].forEach(listener => listener({ matches: true })); },
    };
}

const revealed = env => env.elements.map(element => element.classList.contains('is-revealed'));

test('content is never hidden without IntersectionObserver or with reduced motion', () => {
    for (const options of [{ observer: false }, { reduced: true }]) {
        const env = page(options);
        env.start();
        assert.equal(env.html.classList.contains('landing-reveal-ready'), false);
        assert.equal(env.observers.length, 0);
    }
});

test('each element replays on repeated viewport entries without accumulating observers', () => {
    const env = page();
    env.start();
    assert.equal(env.html.classList.contains('landing-reveal-ready'), true);
    for (let visit = 0; visit < 3; visit++) {
        env.enter(env.elements[0]);
        assert.deepEqual(revealed(env), [true, false, false]);
        assert.equal(env.observers[0].observed.has(env.elements[0]), true);
        env.observers[0].callback([{ isIntersecting: false, target: env.elements[0] }]);
        assert.deepEqual(revealed(env), [false, false, false], 'Leaving resets the next entrance');
    }
    env.enter(env.elements[0]);
    assert.deepEqual(revealed(env), [true, false, false]);
    assert.equal(env.observers.length, 1);
});

test('owner-selected landing autoplay reveals content even with reduced OS motion', () => {
    const env = page({ reduced: true, autoplay: true });
    const stop = env.start();
    assert.equal(env.html.classList.contains('landing-reveal-ready'), true);
    assert.equal(env.listeners.size, 0, 'OS preference changes do not disable this landing');
    env.enter(env.elements[0]);
    assert.deepEqual(revealed(env), [true, false, false]);
    env.preferReducedMotion();
    assert.equal(env.observers[0].disconnected, false);
    stop();
    assert.deepEqual(revealed(env), [true, true, true]);
});

test('turning reduced motion on reveals everything and tears down the observer', () => {
    const env = page();
    env.start();
    env.preferReducedMotion();
    assert.deepEqual(revealed(env), [true, true, true]);
    assert.equal(env.observers[0].disconnected, true);
    assert.equal(env.listeners.size, 0);
    assert.equal(env.html.classList.contains('landing-reveal-ready'), false);
});

test('teardown and setup failure leave all content visible', () => {
    const env = page();
    const stop = env.start();
    stop();
    assert.deepEqual(revealed(env), [true, true, true]);
    assert.equal(env.observers[0].disconnected, true);

    const failing = page({ throwOnObserve: true });
    failing.start();
    assert.deepEqual(revealed(failing), [true, true, true]);
    assert.equal(failing.html.classList.contains('landing-reveal-ready'), false);
});
