import test from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import { execFileSync } from 'node:child_process';

const viewPath = 'resources/views/welcome.blade.php';
const source = process.env.LANDING_SOURCE_REF
    ? execFileSync('git', ['show', `${process.env.LANDING_SOURCE_REF}:${viewPath}`], { encoding: 'utf8' })
    : readFileSync(new URL(`../../${viewPath}`, import.meta.url), 'utf8');

function component(marker) {
    const match = [...source.matchAll(/x-data="([^"]*)"/gs)].find(attribute => attribute[1].includes(marker));
    assert.ok(match, `Landing component containing ${marker} exists`);
    return match[1].replace(/&quot;/g, '"').replace(/&#039;|&apos;/g, "'").replace(/&amp;/g, '&');
}

function environment(reduced = false, observerAvailable = true) {
    let now = 0;
    let frameSequence = 0;
    const frames = new Map();
    const listeners = new Set();
    const observers = [];
    const media = {
        matches: reduced,
        addEventListener: (event, listener) => { if (event === 'change') listeners.add(listener); },
        removeEventListener: (event, listener) => { if (event === 'change') listeners.delete(listener); },
        addListener: listener => listeners.add(listener),
        removeListener: listener => listeners.delete(listener),
    };
    const browser = { matchMedia: () => media };
    class Observer {
        constructor(callback) { this.callback = callback; this.disconnected = false; observers.push(this); }
        observe(element) { this.element = element; }
        disconnect() { this.disconnected = true; }
    }
    const globals = {
        window: browser,
        performance: { now: () => now },
        requestAnimationFrame: callback => { const id = ++frameSequence; frames.set(id, callback); return id; },
        cancelAnimationFrame: id => frames.delete(id),
        IntersectionObserver: observerAvailable ? Observer : undefined,
    };
    if (observerAvailable) browser.IntersectionObserver = Observer;
    return {
        frames, listeners, observers, browser,
        instantiate(marker) {
            const state = Function(...Object.keys(globals), `return (${component(marker)});`)(...Object.values(globals));
            state.$el = { getBoundingClientRect: () => ({ left: 10, top: 20 }) };
            return state;
        },
        advance(duration) {
            now += duration;
            const pending = [...frames.values()];
            frames.clear();
            pending.forEach(callback => callback(now));
        },
        intersect(isIntersecting) {
            observers.filter(observer => !observer.disconnected).forEach(observer => observer.callback([{ isIntersecting, target: observer.element }]));
        },
        preferReducedMotion(value) {
            media.matches = value;
            [...listeners].forEach(listener => listener({ matches: value }));
        },
    };
}

const counters = state => [state.stat1, state.stat2, state.stat3];

test('reduced motion displays final counters without scheduling an animation', () => {
    const env = environment(true);
    const state = env.instantiate('startCountUp()');
    state.init();
    env.intersect(true);
    assert.deepEqual(counters(state), [350, 7, 5]);
    assert.equal(env.frames.size, 0);
    env.intersect(false);
    assert.deepEqual(counters(state), [350, 7, 5], 'Static reduced-motion content remains readable outside the intersection');
});

test('turning reduced motion on cancels an active count-up and shows final values', () => {
    const env = environment();
    const state = env.instantiate('startCountUp()');
    state.init();
    env.intersect(true);
    env.advance(400);
    assert.ok(state.stat1 > 0 && state.stat1 < 350);
    assert.ok(env.frames.size > 0);
    env.preferReducedMotion(true);
    assert.deepEqual(counters(state), [350, 7, 5]);
    assert.equal(env.frames.size, 0);
    env.advance(2000);
    assert.deepEqual(counters(state), [350, 7, 5]);
});

test('normal count-up reaches final values and leaving the viewport cancels and resets', () => {
    const env = environment();
    const state = env.instantiate('startCountUp()');
    state.init();
    env.intersect(true);
    env.advance(800);
    assert.ok(state.stat1 > 0 && state.stat1 < 350);
    env.advance(800);
    assert.deepEqual(counters(state), [350, 7, 5]);
    assert.equal(env.frames.size, 0);
    env.intersect(false);
    assert.deepEqual(counters(state), [0, 0, 0]);
    env.intersect(true);
    env.advance(400);
    assert.ok(env.frames.size > 0);
    env.intersect(false);
    assert.equal(env.frames.size, 0);
    assert.deepEqual(counters(state), [0, 0, 0]);
    env.advance(2000);
    assert.deepEqual(counters(state), [0, 0, 0]);
});

test('destroy disconnects the observer, cancels pending frames and removes the preference listener', () => {
    const env = environment();
    const state = env.instantiate('startCountUp()');
    state.init();
    assert.equal(env.listeners.size, 1);
    assert.equal(env.observers.length, 1);
    env.intersect(true);
    env.advance(400);
    const beforeDestroy = counters(state);
    state.destroy();
    assert.ok(env.observers.every(observer => observer.disconnected));
    assert.equal(env.listeners.size, 0);
    assert.equal(env.frames.size, 0);
    env.preferReducedMotion(true);
    env.advance(2000);
    assert.deepEqual(counters(state), beforeDestroy);
});

test('without IntersectionObserver the counters retain their static final fallback', () => {
    const env = environment(false, false);
    const state = env.instantiate('startCountUp()');
    state.init();
    assert.deepEqual(counters(state), [350, 7, 5]);
    assert.equal(env.frames.size, 0);
});

test('the decorative watermark ignores pointer motion while reduced motion is enabled', () => {
    const env = environment();
    const state = env.instantiate('mouseX:');
    state.updatePos({ clientX: 40, clientY: 70 });
    assert.deepEqual([state.mouseX, state.mouseY], [30, 50]);
    env.preferReducedMotion(true);
    state.updatePos({ clientX: 100, clientY: 200 });
    assert.deepEqual([state.mouseX, state.mouseY], [30, 50]);
});
