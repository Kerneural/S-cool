import test from 'node:test';
import assert from 'node:assert/strict';
import { communitySettings } from '../../resources/js/community-settings.js';

function setup() {
    const listeners = {};
    const browser = {
        FormData: class { constructor(form) { this.form = form; } entries() { return Object.entries(this.form); } },
        confirm: () => false,
        addEventListener: (key, fn) => { listeners[key] = fn; },
        removeEventListener: key => { delete listeners[key]; },
    };
    const state = communitySettings({ discardSettings: 'settings', discardCover: 'cover' }, browser);
    state.$refs = { settingsForm: { name: 'Original', rules: 'Keep' } };
    state.init();
    return { state, browser, listeners };
}
const event = () => ({ prevented: false, preventDefault() { this.prevented = true; } });

test('settings compare actual values so reverting edits clears the dirty warning', () => {
    const { state } = setup();
    state.$refs.settingsForm.name = 'Changed';
    state.settingsChanged();
    assert.equal(state.settingsDirty, true);
    state.$refs.settingsForm.name = 'Original';
    state.settingsChanged();
    assert.equal(state.settingsDirty, false);
});

test('separate cover upload cannot silently discard pending settings', () => {
    const { state, browser } = setup();
    state.settingsDirty = true;
    const cancelled = event();
    state.submitCover(cancelled);
    assert.equal(cancelled.prevented, true);
    assert.equal(state.coverSubmitting, false);
    browser.confirm = () => true;
    state.submitCover(event());
    assert.equal(state.coverSubmitting, true);
    const duplicate = event();
    state.submitCover(duplicate);
    assert.equal(duplicate.prevented, true);
});

test('settings save warns about an unuploaded file and blocks duplicate submit', () => {
    const { state, browser } = setup();
    state.coverDirty = true;
    const cancelled = event();
    state.submitSettings(cancelled);
    assert.equal(cancelled.prevented, true);
    assert.equal(state.settingsSubmitting, false);
    browser.confirm = () => true;
    state.submitSettings(event());
    assert.equal(state.settingsSubmitting, true);
    const duplicate = event();
    state.submitSettings(duplicate);
    assert.equal(duplicate.prevented, true);
});

test('navigation warns only for pending changes and teardown removes its listener', () => {
    const { state, listeners } = setup();
    const clean = event();
    listeners.beforeunload(clean);
    assert.equal(clean.prevented, false);
    state.coverDirty = true;
    const dirty = event();
    listeners.beforeunload(dirty);
    assert.equal(dirty.prevented, true);
    state.coverSubmitting = true;
    const submitted = event();
    listeners.beforeunload(submitted);
    assert.equal(submitted.prevented, false);
    state.destroy();
    assert.deepEqual(listeners, {});
});
