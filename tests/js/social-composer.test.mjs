import test from 'node:test';
import assert from 'node:assert/strict';
import { socialComposer, validateSocialFiles } from '../../resources/js/social-composer.js';

const messages = { count: 'count', type: 'type', total: 'total' };

test('mixed attachment selection rejects unsupported/oversize files and excess counts before upload', () => {
    assert.equal(validateSocialFiles([{ name: 'ok.webp', size: 5 * 1024 * 1024 }], 'images', [], messages), null);
    assert.equal(validateSocialFiles([{ name: 'script.svg', size: 100 }], 'images', [], messages), 'type');
    assert.equal(validateSocialFiles([{ name: 'notes.pdf', size: 10 * 1024 * 1024 + 1 }], 'documents', [], messages), 'type');
    assert.equal(validateSocialFiles([{ name: 'ok.png', size: 10 }], 'images', Array(4).fill({}), messages), 'count');
});

test('rejected selection keeps existing draft and selected attachments', () => {
    const state = socialComposer({ body: 'Keep this', filesMessage: messages });
    state.images = [{ name: 'existing.png', size: 10 }];
    state.addFiles([{ name: 'danger.exe', size: 100 }]);
    assert.equal(state.error, 'type');
    assert.equal(state.body, 'Keep this');
    assert.equal(state.images.length, 1);
    assert.equal(state.documents.length, 0);
});

test('publication failure unlocks retry and preserves draft instead of silently clearing it', () => {
    const originalXHR = globalThis.XMLHttpRequest;
    const originalFormData = globalThis.FormData;
    let sent;
    globalThis.FormData = class {};
    globalThis.XMLHttpRequest = class {
        upload = {};
        open() {}
        setRequestHeader() {}
        send() { sent = this; }
    };
    try {
        const state = socialComposer({ body: 'Important draft', ajax: true, failed: 'try again' });
        state.nativeForm = { method: 'POST', action: '/posts' };
        let prevented = 0;
        state.submit({ preventDefault() { prevented++; } });
        assert.equal(state.submitting, true);
        state.submit({ preventDefault() { prevented++; } });
        assert.equal(prevented, 2);
        sent.status = 422;
        sent.responseText = JSON.stringify({ errors: { images: ['Image rejected'] } });
        sent.onload();
        assert.equal(state.submitting, false);
        assert.equal(state.body, 'Important draft');
        assert.equal(state.error, 'Image rejected');
        state.submit({ preventDefault() {} });
        sent.onerror();
        assert.equal(state.submitting, false);
        assert.equal(state.body, 'Important draft');
        assert.equal(state.error, 'try again');
    } finally {
        globalThis.XMLHttpRequest = originalXHR;
        globalThis.FormData = originalFormData;
    }
});

test('opening an existing post does not warn about unsaved changes until a value changes', () => {
    const originalFormData = globalThis.FormData;
    let title = 'Saved title';
    globalThis.FormData = class {
        *[Symbol.iterator]() { yield ['title', title]; yield ['body', 'Saved body']; yield ['_token', 'csrf']; }
    };
    try {
        const state = socialComposer({ body: 'Saved body', format: 'plain' });
        state.nativeForm = {};
        state.initialValues = state.formValues();
        assert.equal(!!state.dirty(), false);
        title = 'Changed title';
        assert.equal(!!state.dirty(), true);
        title = 'Saved title';
        assert.equal(!!state.dirty(), false);
    } finally { globalThis.FormData = originalFormData; }
});
