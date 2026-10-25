import test from 'node:test';
import assert from 'node:assert/strict';
import { courseOutline } from '../../resources/js/course-outline.js';

const sections = [
    { id: 1, lessons: [{ id: 10, title: 'Must Have Resources', completed: true }, { id: 11, title: 'Prompt Patterns', completed: false }] },
    { id: 2, lessons: [{ id: 20, title: 'Terminal basics', completed: false }, { id: 21, title: 'Đọc log với rg', completed: false }] },
    { id: 3, lessons: [{ id: 30, title: 'Review course', completed: false }] },
];

const outline = () => courseOutline({ sections, currentSection: 2 });

test('only the current section starts open and groups toggle independently', () => {
    const o = outline();
    assert.deepEqual([1, 2, 3].map(id => o.isOpen(id)), [false, true, false]);
    o.onToggle(1, true);
    o.onToggle(2, false);
    assert.deepEqual([1, 2, 3].map(id => o.isOpen(id)), [true, false, false]);
});

test('native toggle echoes of the bound state are ignored', () => {
    const o = outline();
    o.onToggle(2, true);
    assert.deepEqual(o.open, { 2: true });
});

test('title search opens matching sections, ignores case/diacritics and restores expansion on clear', () => {
    const o = outline();
    o.onToggle(3, true);
    o.setQuery('DOC LOG');
    assert.equal(o.matchCount, 1);
    assert.deepEqual([1, 2, 3].map(id => o.isOpen(id)), [false, true, false]);
    assert.equal(o.matches(21), true);
    assert.equal(o.matches(20), false);

    o.setQuery('prompt');
    assert.deepEqual([1, 2, 3].map(id => o.sectionMatches(id)), [1, 0, 0]);
    o.onToggle(1, false);
    assert.equal(o.isOpen(1), false, 'A section can be collapsed while searching');

    o.clear();
    assert.equal(o.searching, false);
    assert.deepEqual([1, 2, 3].map(id => o.isOpen(id)), [false, true, true]);
    assert.equal(o.matchCount, 5);
});

test('no-result search reports zero matches without hiding state permanently', () => {
    const o = outline();
    o.setQuery('nonexistent title');
    assert.equal(o.matchCount, 0);
    assert.deepEqual([1, 2, 3].map(id => o.isOpen(id)), [false, false, false]);
    o.setQuery('   ');
    assert.equal(o.searching, false);
    assert.equal(o.isOpen(2), true);
});

test('section counts follow successful progress events only for rendered lessons', () => {
    const o = outline();
    assert.equal(o.sectionCompleted(1), 1);
    o.progressUpdated({ lessonId: 20, completed: true });
    o.progressUpdated({ lessonId: 21, completed: true });
    o.progressUpdated({ lessonId: 21, completed: false });
    o.progressUpdated({ lessonId: 999, completed: true });
    o.progressUpdated(undefined);
    assert.equal(o.sectionCompleted(2), 1);
    assert.equal(999 in o.completed, false);
});

test('opening a previously hidden mobile outline reveals the current row without moving the page', () => {
    const o = outline();
    const row = { getBoundingClientRect: () => ({ top: 900, bottom: 956, height: 56 }) };
    const list = {
        clientHeight: 0,
        scrollTop: 0,
        querySelector: () => row,
        getBoundingClientRect: () => ({ top: 350, bottom: 830 }),
    };
    o.revealCurrent(list);
    assert.equal(list.scrollTop, 0, 'A hidden panel has no layout to measure');
    list.clientHeight = 480;
    o.revealCurrent(list);
    assert.equal(list.scrollTop, 390, 'Only the list scrolls after the panel becomes visible');
});

test('revealing the current row preserves a visible row and an active search', () => {
    const o = outline();
    let rowTop = 400;
    const list = {
        clientHeight: 480,
        scrollTop: 120,
        querySelector: () => ({ getBoundingClientRect: () => ({ top: rowTop, bottom: rowTop + 50, height: 50 }) }),
        getBoundingClientRect: () => ({ top: 350, bottom: 830 }),
    };
    o.revealCurrent(list);
    assert.equal(list.scrollTop, 120);
    rowTop = 900;
    o.setQuery('prompt');
    o.revealCurrent(list);
    assert.equal(list.scrollTop, 120, 'Do not replace the search result position with the current lesson');
});
