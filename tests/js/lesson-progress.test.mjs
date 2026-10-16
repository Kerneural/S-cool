import test from 'node:test';
import assert from 'node:assert/strict';
import { lessonProgress } from '../../resources/js/lesson-progress.js';

function setup(fetch) {
    const events = [];
    const browser = {
        fetch,
        CustomEvent: class { constructor(type, init) { this.type = type; this.detail = init.detail; } },
        dispatchEvent(event) { events.push(event); },
    };
    return { page: lessonProgress({ completed: false, lessonId: 9, url: '/progress', csrf: 'synthetic', errorMessage: 'Try again' }, browser), events };
}

test('completion reflects only the server result and supplies truthful outline summary', async () => {
    const { page, events } = setup(async (url, request) => {
        assert.equal(url, '/progress');
        assert.equal(request.headers['X-CSRF-TOKEN'], 'synthetic');
        assert.deepEqual(JSON.parse(request.body), { completed: 1 });
        return { ok: true, json: async () => ({ lesson_id: 9, completed: true, course_progress_percentage: 50, course_completed_lessons_count: 1, course_published_lessons_count: 2 }) };
    });
    await page.toggleProgress();
    assert.equal(page.completed, true);
    assert.equal(page.loading, false);
    assert.equal(events.length, 1);
    assert.equal(events[0].detail.summary.course_progress_percentage, 50);
});

test('access denial, network, invalid JSON and wrong-lesson responses preserve state and allow retry', async () => {
    for (const fetch of [
        async () => ({ ok: false }),
        async () => { throw new Error('offline'); },
        async () => ({ ok: true, json: async () => { throw new Error('invalid JSON'); } }),
        async () => ({ ok: true, json: async () => ({ lesson_id: 8, completed: true }) }),
        async () => ({ ok: true, json: async () => ({ lesson_id: 9 }) }),
    ]) {
        const { page, events } = setup(fetch);
        await page.toggleProgress();
        assert.equal(page.completed, false);
        assert.equal(page.loading, false);
        assert.equal(page.errorMessage, 'Try again');
        assert.equal(events.length, 0);
    }
});

test('rapid repeat clicks make one request and a later toggle targets the persisted state', async () => {
    let resolve;
    let requests = 0;
    const { page } = setup(() => {
        requests++;
        return new Promise(done => { resolve = done; });
    });
    const first = page.toggleProgress();
    await page.toggleProgress();
    assert.equal(requests, 1);
    assert.equal(page.completed, false);
    resolve({ ok: true, json: async () => ({ lesson_id: 9, completed: true }) });
    await first;
    const second = page.toggleProgress();
    assert.equal(requests, 2);
    resolve({ ok: true, json: async () => ({ lesson_id: 9, completed: false }) });
    await second;
    assert.equal(page.completed, false);
});
