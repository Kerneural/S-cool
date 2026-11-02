import test from 'node:test';
import assert from 'node:assert/strict';
import { managedImage } from '../../resources/js/rich-lesson-editor.js';
import { conversationStream } from '../../resources/js/conversation-stream.js';
import { lessonAuthoring } from '../../resources/js/lesson-authoring.js';

test('rich editor permits only managed relative images in the current course', () => {
    const base = 'https://s-cool.tech/communities/example/courses/4/lesson-images';
    const image = '/communities/example/courses/4/lesson-images/' + 'a'.repeat(40) + '.png';
    assert.equal(managedImage(image, base), true);
    for (const url of ['https://tracker.test/a.png', image + '?x=1', image.replace('/4/', '/5/'), 'data:image/png;base64,eA==']) {
        assert.equal(managedImage(url, base), false);
    }
});

test('conversation refresh keeps the scroll position when reading older messages', () => {
    let notify;
    let ids = [{ dataset: { messageId: '1' } }];
    const stream = { scrollTop: 0, scrollHeight: 1000, querySelectorAll: () => ids };
    const ui = conversationStream({ MutationObserver: class {
        constructor(fn) { notify = fn; } observe() {} disconnect() {}
    } });
    ui.$refs = { stream };
    ui.$el = { addEventListener() {}, removeEventListener() {} };
    ui.$nextTick = fn => fn();
    ui.init();
    ui.nearBottom = false;
    stream.scrollTop = 200;
    ids = [...ids, { dataset: { messageId: '2' } }];
    notify();
    assert.equal(stream.scrollTop, 200);
    assert.equal(ui.hasNew, true);
    ui.jumpToLatest();
    assert.equal(stream.scrollTop, 1000);
    assert.equal(ui.hasNew, false);
    ui.destroy();
});

test('invalid upload reports a useful error without starting a request', async () => {
    const editor = lessonAuthoring();
    await editor.uploadImage({ type: 'image/svg+xml', size: 20 });
    assert.match(editor.uploadError, /JPEG, PNG/);
    assert.equal(editor.uploading, false);
});

import { MarkdownManager } from '@tiptap/markdown';
import StarterKit from '@tiptap/starter-kit';
import Image from '@tiptap/extension-image';
import { TableKit } from '@tiptap/extension-table';
import TaskList from '@tiptap/extension-task-list';
import TaskItem from '@tiptap/extension-task-item';

test('Markdown round trip retains headings tables code links tasks and image references', () => {
    const manager = new MarkdownManager({ extensions: [StarterKit, Image, TableKit, TaskList, TaskItem] });
    const fence = String.fromCharCode(96).repeat(3);
    const input = [
        '# Tiêu đề', '', 'Đoạn **quan trọng** và [tài liệu](https://example.test).', '',
        '| Cột A | Cột B |', '| --- | --- |', '| Việt Nam | 123 |', '',
        '- [x] Hoàn tất', '- [ ] Còn lại', '',
        fence + 'sh', 'echo hello', fence, '',
        '![Sơ đồ](/communities/demo/courses/1/lesson-images/' + 'a'.repeat(40) + '.png)',
    ].join(String.fromCharCode(10));
    const output = manager.serialize(manager.parse(input));
    for (const text of ['Tiêu đề', '**quan trọng**', 'https://example.test', 'Cột A', 'Việt Nam', 'echo hello', '[x]', '![Sơ đồ]']) assert.ok(output.includes(text), text);
    assert.deepEqual(manager.parse(output), manager.parse(manager.serialize(manager.parse(output))));
});

test('editor actions keep the root instance when Alpine evaluates a child toolbar button', async () => {
    const calls = [];
    const root = { closest: () => null };
    const textarea = { value: '**Bài cũ**', addEventListener() {}, removeEventListener() {} };
    const ui = lessonAuthoring({ createEditor: (_element, options) => {
        assert.equal(options.content, '**Bài cũ**');
        return { format: type => calls.push(type), destroy() {} };
    } }, { addEventListener() {}, removeEventListener() {} });
    ui.$root = root;
    ui.$el = root;
    ui.$refs = { textarea, rich: {} };
    await ui.init();
    ui.$el = { tagName: 'BUTTON' };
    ui.applyFormat('bold');
    assert.deepEqual(calls, ['bold']);
    assert.equal(textarea.value, '**Bài cũ**');
    assert.equal(ui.dirty, false);
    ui.destroy();
});
