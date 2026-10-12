import test from 'node:test';
import assert from 'node:assert/strict';
import { invitationAcceptance } from '../../resources/js/invitation-acceptance.js';

function browserWith(hash) {
    const browser = { location: { hash, pathname: '/invitations/4' } };
    browser.history = { replaceState(state, title, path) {
        assert.equal(state, null);
        assert.equal(path, '/invitations/4');
        browser.location.hash = '';
    } };
    return browser;
}

test('a full email link enables checkout and removes the fragment from history', () => {
    const token = 'a'.repeat(64);
    const browser = browserWith(`#token=${token}`);
    const page = invitationAcceptance(browser);
    page.init();
    assert.equal(page.token, token);
    assert.equal(browser.location.hash, '');
    // A reload cannot recover the secret; the original email link is required.
    const reloaded = invitationAcceptance(browser);
    reloaded.init();
    assert.equal(reloaded.token, '');
});

test('bare, malformed and ambiguous links fail closed', () => {
    for (const hash of ['', '#token=short', `#token=${'A'.repeat(64)}`, `#token=${'a'.repeat(64)}%0A`, `#token=${'a'.repeat(64)}&token=${'b'.repeat(64)}`]) {
        const page = invitationAcceptance(browserWith(hash));
        page.init();
        assert.equal(page.token, '');
    }
});
