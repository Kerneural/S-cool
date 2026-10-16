import test from 'node:test';
import assert from 'node:assert/strict';
import { notificationPreferences } from '../../resources/js/notification-preferences.js';

const ack = data => ({ ok: true, json: async () => ({ status: 'success', ...data }) });
const tick = () => new Promise(resolve => setImmediate(resolve));

function setup() {
    const calls = [];
    const browser = { fetch: (url, options) => new Promise((resolve, reject) => {
        calls.push({ body: JSON.parse(options.body), resolve, reject });
    }) };
    const state = notificationPreferences({ url: '/preferences', csrf: 'synthetic', values: { notify_replies: true, notify_courses: true } }, browser);
    state.init();
    return { state, calls };
}

test('rapid changes are serialized and converge after stale snapshots without overwriting another field', async () => {
    const { state, calls } = setup();
    const complete = state.setPreference('notify_replies', false);
    state.setPreference('notify_courses', false);
    state.setPreference('notify_replies', true);
    assert.equal(calls.length, 1); // Reversed network completion cannot reorder writes.
    assert.equal(state.savedMessage, false);
    calls[0].resolve(ack({ notify_replies: false, notify_courses: true }));
    await tick();
    assert.equal(state.saving, true);
    assert.deepEqual(calls[1].body, { notify_courses: false });
    assert.equal(state.values.notify_replies, true);
    calls[1].resolve(ack({ notify_replies: false, notify_courses: false }));
    await tick();
    assert.deepEqual(calls[2].body, { notify_replies: true });
    calls[2].resolve(ack({ notify_replies: true, notify_courses: true })); // Stale other-field snapshot.
    await complete;
    assert.deepEqual(state.confirmed, { notify_replies: true, notify_courses: false });
    assert.deepEqual(state.values, state.confirmed);
    assert.equal(state.saving, false);
    assert.equal(state.savedMessage, true);
});

test('failure retains intended value, allows other field to save, and retries exact failed choice', async () => {
    const { state, calls } = setup();
    const complete = state.setPreference('notify_replies', false);
    state.setPreference('notify_courses', false);
    calls[0].reject(new Error('offline'));
    await tick();
    calls[1].resolve(ack({ notify_courses: false }));
    await complete;
    assert.equal(state.values.notify_replies, false);
    assert.equal(state.confirmed.notify_replies, true);
    assert.equal(state.failures.notify_replies, true);
    assert.equal(state.savedMessage, false);
    const retry = state.retry('notify_replies');
    assert.deepEqual(calls[2].body, { notify_replies: false });
    calls[2].resolve(ack({ notify_replies: false }));
    await retry;
    assert.deepEqual(state.failures, {});
    assert.equal(state.savedMessage, true);
});

test('a pending failed older mutation cannot roll back a newer intention', async () => {
    const { state, calls } = setup();
    const complete = state.setPreference('notify_replies', false);
    state.setPreference('notify_replies', true);
    calls[0].reject(new Error('offline'));
    await tick();
    assert.deepEqual(calls[1].body, { notify_replies: true });
    calls[1].resolve(ack({ notify_replies: true }));
    await complete;
    assert.equal(state.values.notify_replies, true);
    assert.deepEqual(state.failures, {});
});

test('HTTP rejection and malformed or mismatched acknowledgments never report saved', async () => {
    for (const response of [
        { ok: false },
        { ok: true, json: async () => ({ status: 'success' }) },
        ack({ notify_replies: 'false' }),
        ack({ notify_replies: true }),
        { ok: true, json: async () => { throw new Error('invalid JSON'); } },
    ]) {
        const state = notificationPreferences({ values: { notify_replies: true, notify_courses: true } }, { fetch: async () => response });
        await state.setPreference('notify_replies', false);
        assert.equal(state.savedMessage, false);
        assert.equal(state.failures.notify_replies, true);
    }
});
