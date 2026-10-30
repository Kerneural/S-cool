import test from 'node:test';
import assert from 'node:assert/strict';
import { lessonAuthoring } from '../../resources/js/lesson-authoring.js';

class MockEvent {
    constructor(type, init = {}) {
        this.type = type;
        this.bubbles = init.bubbles ?? false;
    }
}

class MockAbortController {
    constructor() {
        this.signal = { aborted: false };
    }
    abort() {
        this.signal.aborted = true;
    }
}

function createTextareaMock(initialValue = '', start = 0, end = 0) {
    const dispatchedEvents = [];
    let focused = false;
    return {
        value: initialValue,
        selectionStart: start,
        selectionEnd: end,
        setSelectionRange(s, e) {
            this.selectionStart = s;
            this.selectionEnd = e;
        },
        focus() {
            focused = true;
        },
        dispatchEvent(event) {
            dispatchedEvents.push(event);
        },
        get isFocused() {
            return focused;
        },
        get events() {
            return dispatchedEvents;
        },
    };
}

function setup(fetchMock, options = {}, textarea = null) {
    const browser = {
        fetch: fetchMock,
        AbortController: MockAbortController,
        Event: MockEvent,
    };
    const authoring = lessonAuthoring({
        previewUrl: '/preview',
        csrfToken: 'test-csrf-token',
        emptyPlaceholder: 'Chưa có nội dung bài học để xem trước.',
        errorMessage: 'Không thể tải bản xem trước.',
        ...options,
    }, browser);

    if (textarea) {
        authoring.$refs = { textarea };
    }

    return { authoring, browser };
}

test('applyFormat bold wraps selected text and preserves selection range', () => {
    const textarea = createTextareaMock('Hello world', 6, 11);
    const { authoring } = setup(null, {}, textarea);

    authoring.applyFormat('bold');

    assert.equal(textarea.value, 'Hello **world**');
    assert.equal(textarea.selectionStart, 8);
    assert.equal(textarea.selectionEnd, 13);
    assert.equal(textarea.isFocused, true);
    assert.equal(textarea.events.length, 1);
    assert.equal(textarea.events[0].type, 'input');
});

test('applyFormat bold with no selection inserts asterisks and positions cursor inside', () => {
    const textarea = createTextareaMock('Hello ', 6, 6);
    const { authoring } = setup(null, {}, textarea);

    authoring.applyFormat('bold');

    assert.equal(textarea.value, 'Hello ****');
    assert.equal(textarea.selectionStart, 8);
    assert.equal(textarea.selectionEnd, 8);
});

test('applyFormat italic wraps selected text or inserts empty markers', () => {
    const textarea = createTextareaMock('italic text', 0, 11);
    const { authoring } = setup(null, {}, textarea);

    authoring.applyFormat('italic');

    assert.equal(textarea.value, '*italic text*');
    assert.equal(textarea.selectionStart, 1);
    assert.equal(textarea.selectionEnd, 12);
});

test('applyFormat h2, bullet, number, and quote add block prefixes properly', () => {
    const textarea = createTextareaMock('Line 1\nLine 2', 0, 13);
    const { authoring } = setup(null, {}, textarea);

    authoring.applyFormat('bullet');
    assert.equal(textarea.value, '- Line 1\n- Line 2');

    authoring.applyFormat('number');
    assert.equal(textarea.value, '1. Line 1\n2. Line 2');

    const singleLine = createTextareaMock('My Heading', 3, 3);
    const { authoring: singleAuthoring } = setup(null, {}, singleLine);
    singleAuthoring.applyFormat('h2');
    assert.equal(singleLine.value, '## My Heading');

    const quoteLine = createTextareaMock('Quote this', 2, 2);
    const { authoring: quoteAuthoring } = setup(null, {}, quoteLine);
    quoteAuthoring.applyFormat('quote');
    assert.equal(quoteLine.value, '> Quote this');
});

test('applyFormat link inserts link markdown and auto-detects URL vs text', () => {
    const textSelection = createTextareaMock('Click here', 0, 10);
    const { authoring: textAuthoring } = setup(null, {}, textSelection);
    textAuthoring.applyFormat('link');
    assert.equal(textSelection.value, '[Click here](https://example.com)');

    const urlSelection = createTextareaMock('https://scool.dev', 0, 17);
    const { authoring: urlAuthoring } = setup(null, {}, urlSelection);
    urlAuthoring.applyFormat('link');
    assert.equal(urlSelection.value, '[Tiêu đề liên kết](https://scool.dev)');
});

test('fetchPreview displays empty placeholder without invoking fetch for empty content', async () => {
    let fetchCalled = false;
    const textarea = createTextareaMock('   ', 0, 3);
    const { authoring } = setup(async () => {
        fetchCalled = true;
        return { ok: true, json: async () => ({}) };
    }, {}, textarea);

    authoring.setTab('preview');
    await authoring.fetchPreview();

    assert.equal(fetchCalled, false);
    assert.match(authoring.previewHtml, /Chưa có nội dung bài học để xem trước/);
    assert.equal(authoring.loading, false);
    assert.equal(authoring.errorMessage, '');
});

test('fetchPreview queries server and renders sanitized markdown html', async () => {
    const textarea = createTextareaMock('## Hello World', 0, 14);
    let sentBody = null;
    const { authoring } = setup(async (url, opts) => {
        sentBody = JSON.parse(opts.body);
        assert.equal(url, '/preview');
        assert.equal(opts.headers['X-CSRF-TOKEN'], 'test-csrf-token');
        return {
            ok: true,
            json: async () => ({ html: '<h2>Hello World</h2>' }),
        };
    }, {}, textarea);

    authoring.setTab('preview');
    await authoring.fetchPreview();

    assert.deepEqual(sentBody, { content: '## Hello World' });
    assert.equal(authoring.previewHtml, '<h2>Hello World</h2>');
    assert.equal(authoring.loading, false);
    assert.equal(authoring.errorMessage, '');
});

test('fetchPreview caches rendered output and skips redundant fetch', async () => {
    let callCount = 0;
    const textarea = createTextareaMock('Cache me', 0, 8);
    const { authoring } = setup(async () => {
        callCount++;
        return {
            ok: true,
            json: async () => ({ html: '<p>Cache me</p>' }),
        };
    }, {}, textarea);

    await authoring.fetchPreview();
    assert.equal(callCount, 1);

    await authoring.fetchPreview();
    assert.equal(callCount, 1);
});

test('fetchPreview handles server error gracefully', async () => {
    const textarea = createTextareaMock('Faulty text', 0, 11);
    const { authoring } = setup(async () => {
        return { ok: false, status: 500 };
    }, {}, textarea);

    await authoring.fetchPreview();

    assert.equal(authoring.loading, false);
    assert.equal(authoring.errorMessage, 'Không thể tải bản xem trước.');
});
