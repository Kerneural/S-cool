import test from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import { execFileSync } from 'node:child_process';

const viewPath = 'resources/views/welcome.blade.php';
const cssPath = 'resources/css/app.css';
const read = path => process.env.LANDING_SOURCE_REF
    ? execFileSync('git', ['show', `${process.env.LANDING_SOURCE_REF}:${path}`], { encoding: 'utf8' })
    : readFileSync(new URL(`../../${path}`, import.meta.url), 'utf8');
const source = read(viewPath);
const css = read(cssPath);

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

test('the footer wordmark is static decoration excluded from the accessibility tree', () => {
    assert.ok(!source.includes('mouseX:'), 'No pointer-tracking spotlight remains');
    assert.match(source, /<div[^>]*aria-hidden="true"[^>]*>\s*<span class="landing-wordmark/);
});

function hero({ reduced = false } = {}) {
    let now = 0;
    let sequence = 0;
    const jobs = new Map();
    const browser = { reducedMotion: reduced, matchMedia: () => ({ matches: browser.reducedMotion }) };
    const page = { hidden: false };
    const globals = {
        window: browser,
        document: page,
        setInterval: (callback, delay) => { const id = ++sequence; jobs.set(id, { callback, delay, at: now + delay }); return id; },
        clearInterval: id => jobs.delete(id),
    };
    const attribute = component('heroTimer').replace(/\{\{[^}]*\}\}/g, '3');
    const state = Function(...Object.keys(globals), `return (${attribute});`)(...Object.values(globals));
    return {
        state, browser, page, jobs,
        advance(duration) {
            const until = now + duration;
            for (;;) {
                const due = [...jobs.values()].filter(job => job.at <= until).sort((a, b) => a.at - b.at)[0];
                if (!due) break;
                now = due.at;
                due.at += due.delay;
                due.callback();
            }
            now = until;
        },
    };
}

test('hero preview rotates every 8 seconds with a single interval', () => {
    const { state, jobs, advance } = hero();
    state.init();
    assert.equal(jobs.size, 1);
    advance(7999);
    assert.equal(state.activeSet, 0);
    advance(1);
    assert.equal(state.activeSet, 1);
    advance(16000);
    assert.equal(state.activeSet, 0, 'Rotation wraps after the third screenshot');
    assert.equal(jobs.size, 1, 'No intervals accumulate');
});

test('hero pauses for interaction and hidden pages but autoplays despite reduced OS motion', () => {
    const { state, browser, page, advance } = hero();
    state.init();
    state.heroPaused = true;
    advance(16000);
    assert.equal(state.activeSet, 0);
    state.heroPaused = false;
    page.hidden = true;
    advance(16000);
    assert.equal(state.activeSet, 0);
    page.hidden = false;
    browser.reducedMotion = true;
    advance(16000);
    assert.equal(state.activeSet, 2, 'Owner-selected landing autoplay is independent of OS motion');
    state.select(2);
    assert.equal(state.activeSet, 2, 'Manual selection remains immediate');
    browser.reducedMotion = false;
    advance(8000);
    assert.equal(state.activeSet, 0);
});

test('hero destroy clears the rotation interval', () => {
    const { state, jobs, advance } = hero();
    state.init();
    state.destroy();
    assert.equal(jobs.size, 0);
    advance(24000);
    assert.equal(state.activeSet, 0);
});

test('hero screenshots, captions and selectors are rendered from one slide list with reserved dimensions', () => {
    assert.match(source, /'label' => 'Leaderboard', 'image' => 'product-proof-of-work\.png'/, 'The leaderboard capture is labeled as a leaderboard');
    assert.ok(source.includes('not practical-work verification'), 'Leaderboard caption does not claim review');
    assert.ok(source.includes('width="1280" height="800"'), 'Screenshot dimensions are reserved');
});

test('marquee has no manual pause control and pauses only through hover or focus', () => {
    assert.ok(!source.includes('manualPaused'), 'Manual pause state removed');
    assert.ok(!/Pause motion|Resume motion/.test(source), 'Visible pause/resume control removed');
    assert.ok(!source.includes('@keyframes'), 'Marquee keyframes are not duplicated inline in the view');
    assert.match(css, /\.animate-marquee:hover,[\s\S]*?\.animate-marquee:focus-within,[\s\S]*?animation-play-state: paused;/);
    assert.equal((css.match(/@keyframes landing-marquee \{/g) || []).length, 1, 'One authoritative marquee keyframe');
    assert.match(css, /animation: landing-marquee 35s linear infinite;/);
});

test('autoplay is landing-only; other pages retain the static reduced-motion fallback', () => {
    const reduced = css.slice(css.indexOf('@media (prefers-reduced-motion: reduce)'));
    assert.ok(/^<html[^\n]*data-landing-motion="autoplay"/m.test(source), 'Only the landing root opts into autoplay');
    assert.match(reduced, /html:not\(\[data-landing-motion="autoplay"\]\) \.animate-marquee-reverse \{\s*animation: none;/);
    assert.match(reduced, /html:not\(\[data-landing-motion="autoplay"\]\) \*,/);
    assert.match(reduced, /> \[aria-hidden="true"\][\s\S]*?display: none;/);
    assert.match(reduced, /flex-wrap: wrap;/);
    const clones = [...source.matchAll(/<!-- Duplicate for seamless infinite loop -->\s*<div[^>]*aria-hidden="true"/g)];
    assert.equal(clones.length, 2, 'Both duplicate groups are hidden from assistive technology');
    assert.ok(source.includes('tabindex="-1"'), 'Cloned links are excluded from keyboard tab order');
});
