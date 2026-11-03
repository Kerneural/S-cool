import test from 'node:test';
import assert from 'node:assert/strict';
import { isSendKey, submitOnEnter } from '../../resources/js/submit-on-enter.js';
import { inboxPanel } from '../../resources/js/inbox-panel.js';
import { socialComposer } from '../../resources/js/social-composer.js';

test('Enter sends but Shift+Enter, composition and modifiers preserve editing', () => {
    assert.equal(isSendKey({ key: 'Enter' }), true);
    for (const extra of [{ shiftKey: true }, { isComposing: true }, { keyCode: 229 }, { ctrlKey: true }, { metaKey: true }, { altKey: true }]) {
        assert.equal(isSendKey({ key: 'Enter', ...extra }), false);
    }
    assert.equal(isSendKey({ key: 'a' }), false);
});

test('native submit path is used and upload/inflight states prevent Enter duplicates', () => {
    let requests = 0, prevented = 0, disabled = false;
    const form = { querySelector: () => disabled, requestSubmit: () => requests++ };
    const event = { key: 'Enter', preventDefault: () => prevented++ };
    submitOnEnter(event, form);
    disabled = true;
    submitOnEnter(event, form);
    disabled = false;
    submitOnEnter(event, form, true);
    assert.equal(requests, 1);
    assert.equal(prevented, 3);
    submitOnEnter({ ...event, shiftKey: true }, form);
    assert.equal(prevented, 3);
});

test('only opted-in comment composers use Enter; the latest draft is synchronized before submit', () => {
    const calls = [];
    const form = { querySelector: () => false, requestSubmit: () => calls.push('submit') };
    const event = { key: 'Enter', preventDefault() {} };
    const post = socialComposer();
    assert.equal(post.sendKey(event), false);
    const comment = socialComposer({ enterSends: true });
    comment.$root = { closest: () => form };
    comment.sync = () => calls.push('sync');
    comment.sendKey(event);
    assert.deepEqual(calls, ['sync', 'submit']);
    comment.uploading = true;
    comment.sendKey(event);
    assert.equal(calls.filter(x => x === 'submit').length, 1);
});

test('new-arrival toast replaces the old timer and cleanup prevents late updates', () => {
    const cleared = [], pending = [];
    const panel = inboxPanel('', { removeEventListener() {}, clearTimeout: id => cleared.push(id), setTimeout: fn => { pending.push(fn); return pending.length; } });
    panel.$el = { removeEventListener() {} };
    panel.arrived({ message: true });
    assert.equal(panel.toast, 'Bạn có tin nhắn mới');
    panel.arrived({ message: false });
    assert.equal(panel.toastKind, 'notifications');
    assert.ok(cleared.includes(1));
    panel.destroy();
    assert.ok(cleared.includes(2));
});

test('focus refresh is deduplicated and hidden tabs do not start an extra request', async () => {
    let calls = 0, finish;
    const browser = { document: { hidden: false } };
    const panel = inboxPanel('', browser);
    panel.$wire = { refreshInbox: () => { calls++; return new Promise(resolve => { finish = resolve; }); } };
    const first = panel.refresh();
    await panel.refresh();
    assert.equal(calls, 1);
    finish(); await first;
    browser.document.hidden = true;
    await panel.refresh();
    assert.equal(calls, 1);
});
