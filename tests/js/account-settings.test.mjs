import test from 'node:test';
import assert from 'node:assert/strict';
import { accountSettings } from '../../resources/js/account-settings.js';

function setup(url, options = {}) {
    const listeners = {};
    const browser = {
        location: { href: url },
        history: {
            pushState: (_, __, value) => { browser.location.href = value.href; },
            replaceState: (_, __, value) => { browser.location.href = value.href; },
        },
        addEventListener: (event, callback) => { listeners[event] = callback; },
        removeEventListener: event => { delete listeners[event]; },
    };
    const state = accountSettings({ initial: 'profile', ...options }, browser);
    state.init();
    return { state, browser, listeners };
}

test('direct query and legacy hash links select their sections and query takes precedence', () => {
    assert.equal(setup('https://example.test/profile#notifications').state.currentTab, 'notifications');
    assert.equal(setup('https://example.test/profile?section=security#notifications').state.currentTab, 'security');
    assert.equal(setup('https://example.test/profile?section=invalid').state.currentTab, 'profile');
});

test('section clicks remove stale hashes and back to bare profile restores default', () => {
    const { state, browser, listeners } = setup('https://example.test/profile#notifications');
    state.setTab('security');
    assert.equal(browser.location.href, 'https://example.test/profile?section=security');
    browser.location.href = 'https://example.test/profile';
    listeners.popstate();
    assert.equal(state.currentTab, 'profile');
    browser.location.href += '#notifications';
    listeners.hashchange();
    assert.equal(state.currentTab, 'notifications');
    state.destroy();
    assert.deepEqual(listeners, {});
});

test('server validation or saved sensitive form overrides stale URL and survives reload', () => {
    const { state, browser } = setup('https://example.test/profile?section=notifications#profile', { initial: 'security', forceInitial: true });
    assert.equal(state.currentTab, 'security');
    assert.equal(browser.location.href, 'https://example.test/profile?section=security');
});
