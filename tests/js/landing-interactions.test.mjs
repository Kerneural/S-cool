import test from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import { execFileSync } from 'node:child_process';

// Re-run the same interaction scenarios against a revision with LANDING_SOURCE_REF.
const viewPath = 'resources/views/welcome.blade.php';
const source = process.env.LANDING_SOURCE_REF
    ? execFileSync('git', ['show', `${process.env.LANDING_SOURCE_REF}:${viewPath}`], { encoding: 'utf8' })
    : readFileSync(new URL(`../../${viewPath}`, import.meta.url), 'utf8');

const decodeAttribute = value => value.replace(/&quot;/g, '"').replace(/&#039;|&apos;/g, "'").replace(/&amp;/g, '&');

function componentAttribute(marker) {
    const attributes = [...source.matchAll(/x-data="([^"]*)"/gs)];
    const match = attributes.find(attribute => attribute[1].includes(marker));
    assert.ok(match, `Landing component containing ${marker} exists`);
    return decodeAttribute(match[1]);
}

function clock() {
    let now = 0;
    let sequence = 0;
    const jobs = new Map();
    const schedule = (callback, delay, interval = false) => {
        const id = ++sequence;
        jobs.set(id, { callback, at: now + delay, interval: interval ? delay : 0 });
        return id;
    };
    return {
        setTimeout: (callback, delay) => schedule(callback, delay),
        clearTimeout: id => jobs.delete(id),
        setInterval: (callback, delay) => schedule(callback, delay, true),
        clearInterval: id => jobs.delete(id),
        requestAnimationFrame: callback => schedule(callback, 16),
        tick(duration) {
            const until = now + duration;
            while (true) {
                const next = [...jobs].filter(([, job]) => job.at <= until).sort((a, b) => a[1].at - b[1].at)[0];
                if (!next) break;
                const [id, job] = next;
                now = job.at;
                if (job.interval) job.at += job.interval;
                else jobs.delete(id);
                job.callback();
            }
            now = until;
        },
    };
}

function carousel() {
    const timer = clock();
    const ticks = [];
    const browser = { reducedMotion: false, matchMedia: () => ({ matches: browser.reducedMotion }) };
    const page = { hidden: false };
    const globals = { ...timer, window: browser, document: page };
    const state = Function(...Object.keys(globals), `return (${componentAttribute('activeStory:')});`)(...Object.values(globals));
    state.$nextTick = callback => ticks.push(callback);
    const flushTicks = () => {
        while (ticks.length) ticks.shift()();
    };
    return {
        state, browser, page,
        advance(duration) {
            flushTicks();
            timer.tick(duration);
            flushTicks();
            timer.tick(16);
        },
    };
}

test('rapid next, previous and dot clicks cannot overrun or leave a stale wrap reset', () => {
    const { state, advance } = carousel();
    state.activeStory = state.stories.length - 1;
    state.next();
    state.next();
    assert.ok(state.activeStory <= state.stories.length, 'Next remains within the real cards plus boundary clone');
    state.prev();
    state.goTo(2);
    advance(600);
    assert.ok(state.activeStory >= 0 && state.activeStory < state.stories.length);
    state.goTo(2);
    advance(1000);
    assert.equal(state.activeStory, 2, 'A completed wrap cannot overwrite a later selection');
    state.next();
    advance(600);
    assert.equal(state.activeStory, 3, 'Navigation remains usable after the wrap');
});

test('previous from the first card settles at the last card under rapid input', () => {
    const { state, advance } = carousel();
    state.prev();
    state.prev();
    state.next();
    advance(600);
    assert.equal(state.activeStory, state.stories.length - 1);
    state.prev();
    advance(600);
    assert.equal(state.activeStory, state.stories.length - 2);
});

test('a dot selection during a wrap is either deferred or survives the pending callback', () => {
    const { state, advance } = carousel();
    state.activeStory = state.stories.length - 1;
    state.next();
    state.goTo(2);
    const accepted = state.activeStory === 2;
    advance(600);
    assert.equal(state.activeStory, accepted ? 2 : 0, 'An accepted dot selection must not be overwritten by a stale wrap');
});

test('landing carousel autoplays independently of OS motion preference changes', () => {
    const { state, browser, advance } = carousel();
    browser.reducedMotion = true;
    state.startAutoplay();
    advance(6000);
    assert.equal(state.activeStory, 1);
    browser.reducedMotion = false;
    advance(6000);
    assert.equal(state.activeStory, 2);
    browser.reducedMotion = true;
    advance(6000);
    assert.equal(state.activeStory, 3);
});

test('autoplay pauses during interaction and stops when the component is destroyed', () => {
    const { state, advance } = carousel();
    state.isPaused = true;
    state.startAutoplay();
    advance(12000);
    assert.equal(state.activeStory, 0);
    state.isPaused = false;
    advance(6000);
    assert.equal(state.activeStory, 1);
    state.destroy();
    advance(12000);
    assert.equal(state.activeStory, 1);
});

test('autoplay does not advance while the page is hidden', () => {
    const { state, page, advance } = carousel();
    state.startAutoplay();
    page.hidden = true;
    advance(18000);
    assert.equal(state.activeStory, 0);
    page.hidden = false;
    advance(6000);
    assert.equal(state.activeStory, 1);
    state.destroy();
});

test('product cards show real product visuals instead of fabricated API snippets', () => {
    assert.ok(!source.includes('navigator.clipboard'), 'No copy-to-clipboard code samples remain');
    assert.ok(!/ProofOfWork::submit|Community::create|->evaluate\(\)/.test(source), 'No invented API calls are presented');
    assert.match(source, /'eyebrow' => 'Practical review'/, 'Practical review is explained as its own pillar');
});

test('learning journey offers the five real stages and never labels the leaderboard as review', () => {
    const stages = [...source.matchAll(/'name' => '(\w+)',\n\s*'title'/g)].map(match => match[1]);
    assert.deepEqual(stages, ['Discover', 'Discuss', 'Learn', 'Practice', 'Review']);
    const journey = source.slice(source.indexOf('$journeyStages = ['), source.indexOf('<!-- 3. Product pillars'));
    assert.ok(!journey.includes('product-proof-of-work.png'), 'The leaderboard capture is not used for Practice/Review');
    assert.ok(journey.includes('Illustration — simplified view, not a screenshot or real data.'));
});
