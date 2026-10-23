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
    const globals = { ...timer, window: browser };
    const state = Function(...Object.keys(globals), `return (${componentAttribute('activeStory:')});`)(...Object.values(globals));
    state.$nextTick = callback => ticks.push(callback);
    const flushTicks = () => {
        while (ticks.length) ticks.shift()();
    };
    return {
        state, browser,
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

test('autoplay respects reduced motion including preference changes while open', () => {
    const { state, browser, advance } = carousel();
    browser.reducedMotion = true;
    state.startAutoplay();
    advance(12000);
    assert.equal(state.activeStory, 0);
    browser.reducedMotion = false;
    advance(6000);
    assert.equal(state.activeStory, 1);
    browser.reducedMotion = true;
    advance(12000);
    assert.equal(state.activeStory, 1);
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

function copyButtons() {
    const components = [...source.matchAll(/x-data="([^"]*)"/gs)].filter(match => match[1].includes('copied:'));
    const handlers = [...source.matchAll(/@click="([^"]*navigator\.clipboard[^\"]*)"/gs)];
    assert.equal(components.length, 3);
    assert.equal(handlers.length, components.length);
    return components.map((component, index) => ({ component: decodeAttribute(component[1]), handler: decodeAttribute(handlers[index][1]) }));
}

const AsyncFunction = Object.getPrototypeOf(async function () {}).constructor;

function copyState(button, navigator) {
    const timer = clock();
    const state = Function(`return (${button.component});`)();
    const click = new AsyncFunction('state', 'navigator', 'setTimeout', `with (state) { ${button.handler} }`);
    return { state, timer, click: () => click(state, navigator, timer.setTimeout) };
}

test('each copy button waits for clipboard confirmation before showing success', async () => {
    for (const button of copyButtons()) {
        let resolveWrite;
        let written;
        const pending = new Promise(resolve => { resolveWrite = resolve; });
        const { state, timer, click } = copyState(button, { clipboard: { writeText: value => { written = value; return pending; } } });
        const completion = click();
        assert.equal(state.copied, false, 'A pending clipboard write is not success');
        assert.equal(written, state.code);
        resolveWrite();
        await completion;
        assert.equal(state.copied, true);
        timer.tick(2000);
        assert.equal(state.copied, false);
    }
});

test('each copy button reports clipboard denial and can retry successfully', async () => {
    for (const button of copyButtons()) {
        let rejectWrite;
        const denied = new Promise((_, reject) => { rejectWrite = reject; });
        // Prevent the original fire-and-forget handler from leaking an unhandled rejection during the red run.
        denied.catch(() => {});
        const navigator = { clipboard: { writeText: () => denied } };
        const { state, click } = copyState(button, navigator);
        const completion = click();
        rejectWrite(new Error('NotAllowedError'));
        await completion;
        assert.equal(state.copied, false);
        assert.equal(state.copyError, true);
        navigator.clipboard.writeText = async () => {};
        await click();
        assert.equal(state.copyError, false);
        assert.equal(state.copied, true);
    }
});

test('each copy button handles an unavailable clipboard without reporting success', async () => {
    for (const button of copyButtons()) {
        const { state, click } = copyState(button, {});
        await click();
        assert.equal(state.copied, false);
        assert.equal(state.copyError, true);
    }
});
