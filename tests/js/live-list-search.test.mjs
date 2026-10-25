import test from 'node:test';
import assert from 'node:assert/strict';
import { liveListSearch } from '../../resources/js/live-list-search.js';

function fixture(t) {
    const original = { fetch: globalThis.fetch, window: globalThis.window, DOMParser: globalThis.DOMParser };
    t.after(() => Object.assign(globalThis, original));
    const history = [];
    globalThis.window = { location: { href: 'http://127.0.0.1:8080/communities', origin: 'http://127.0.0.1:8080' }, history: { replaceState: (...args) => history.push(args[2]), pushState: (...args) => history.push(args[2]) } };
    globalThis.DOMParser = class { parseFromString(html) { return { querySelector: (selector) => selector === '[data-live-search-results]' ? { innerHTML: html } : null }; } };
    const region = { innerHTML: 'retained results' };
    const input = { value: 'docker' };
    const component = liveListSearch();
    component.$el = { elements: { namedItem: (name) => name === 'q' ? input : null } };
    component.form = component.$el;
    component.scope = { querySelector: (selector) => selector === '[data-live-search-results]' ? region : null, removeEventListener() {} };
    return { component, region, input, history };
}

test('an older response cannot overwrite a newer query even when abort is ignored', async (t) => {
    const { component, region } = fixture(t);
    const pending = [];
    globalThis.fetch = () => new Promise((resolve) => pending.push(resolve));
    const older = component.load(new URL('http://127.0.0.1:8080/communities?q=dock'));
    const newer = component.load(new URL('http://127.0.0.1:8080/communities?q=python'));
    pending[1]({ ok: true, text: async () => 'Python result' });
    await newer;
    pending[0]({ ok: true, text: async () => 'Stale Docker result' });
    await older;
    assert.equal(region.innerHTML, 'Python result');
    assert.equal(component.loading, false);
});

test('network failure preserves existing results and a successful retry clears the error', async (t) => {
    const { component, region, input } = fixture(t);
    globalThis.fetch = async () => { throw new TypeError('offline'); };
    const url = new URL('http://127.0.0.1:8080/communities?q=docker');
    await component.load(url);
    assert.match(component.error, /retry/);
    assert.equal(region.innerHTML, 'retained results');
    assert.equal(input.value, 'docker');
    globalThis.fetch = async () => ({ ok: true, text: async () => 'Docker result' });
    await component.load(url);
    assert.equal(component.error, '');
    assert.equal(region.innerHTML, 'Docker result');
});

test('new input invalidates an in-flight response before its debounce fires', async (t) => {
    const { component, region } = fixture(t);
    let resolve;
    globalThis.fetch = () => new Promise((r) => { resolve = r; });
    const pending = component.load(new URL('http://127.0.0.1:8080/communities?q=dock'));
    component.liveSearch();
    clearTimeout(component.timer);
    resolve({ ok: true, text: async () => 'Outdated result' });
    await pending;
    assert.equal(region.innerHTML, 'retained results');
});

test('HTTP server failure retains results and reports a recoverable error', async (t) => {
    const { component, region } = fixture(t);
    globalThis.fetch = async () => ({ ok: false, status: 500 });
    await component.load(new URL('http://127.0.0.1:8080/communities?q=docker'));
    assert.match(component.error, /retry/);
    assert.equal(region.innerHTML, 'retained results');
});

test('access denial and a login redirect remove previously rendered private results', async (t) => {
    const { component, region } = fixture(t);
    for (const response of [{ ok: false, status: 403 }, { ok: true, url: 'http://127.0.0.1:8080/login' }]) {
        region.innerHTML = 'private snippet';
        globalThis.fetch = async () => response;
        await component.load(new URL('http://127.0.0.1:8080/communities?q=docker'));
        assert.match(component.error, /access changed/);
        assert.equal(region.innerHTML, '');
    }
});
