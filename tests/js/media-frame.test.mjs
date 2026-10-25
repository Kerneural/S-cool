import test from 'node:test';
import assert from 'node:assert/strict';
import { mediaFrame } from '../../resources/js/media-frame.js';

// Minimal <img> stand-in: real EventTarget, controllable complete/naturalWidth/decode.
function fakeImage({ src = '/cover', complete = false, naturalWidth = 0, decode } = {}) {
    const image = new EventTarget();
    image.attrs = { src };
    image.getAttribute = name => image.attrs[name] ?? null;
    image.complete = complete;
    image.naturalWidth = naturalWidth;
    if (decode !== undefined) image.decode = decode;
    return image;
}

function frameFor(image) {
    const frame = mediaFrame();
    frame.$el = { querySelector: () => image };
    frame.init();
    return frame;
}

const flush = () => new Promise(resolve => setImmediate(resolve));

test('a pending request settles to ready on load', async () => {
    const image = fakeImage();
    const frame = frameFor(image);
    assert.equal(frame.state, 'pending');
    image.complete = true;
    image.naturalWidth = 640;
    image.dispatchEvent(new Event('load'));
    await flush();
    assert.equal(frame.state, 'ready');
});

test('a network or 404 error settles to the stable fallback', () => {
    const image = fakeImage();
    const frame = frameFor(image);
    image.dispatchEvent(new Event('error'));
    assert.equal(frame.state, 'error');
});

test('cached images use complete plus a positive naturalWidth', async () => {
    const cached = frameFor(fakeImage({ complete: true, naturalWidth: 320 }));
    await flush();
    assert.equal(cached.state, 'ready');

    const broken = frameFor(fakeImage({ complete: true, naturalWidth: 0 }));
    assert.equal(broken.state, 'error', 'A complete but undecodable image is not ready');
});

test('missing image or source goes straight to the fallback', () => {
    const frame = mediaFrame();
    frame.$el = { querySelector: () => null };
    frame.init();
    assert.equal(frame.state, 'missing');
    assert.equal(frameFor(fakeImage({ src: '' })).state, 'missing');
});

test('decode rejection settles to error instead of an endless overlay', async () => {
    const image = fakeImage({ decode: () => Promise.reject(new Error('EncodingError')) });
    const frame = frameFor(image);
    image.complete = true;
    image.naturalWidth = 10;
    image.dispatchEvent(new Event('load'));
    await flush();
    assert.equal(frame.state, 'error');
});

test('a replaced source cannot be settled by the older decode', async () => {
    let resolveOld;
    const image = fakeImage({ decode: () => new Promise(resolve => { resolveOld = resolve; }) });
    const frame = frameFor(image);
    image.complete = true;
    image.naturalWidth = 10;
    image.dispatchEvent(new Event('load'));

    // New source starts loading before the old decode finishes.
    image.attrs.src = '/next-cover';
    image.complete = false;
    image.naturalWidth = 0;
    frame.track();
    resolveOld();
    await flush();
    assert.equal(frame.state, 'pending', 'The stale decode must not reveal the new request');
});

test('destroy removes listeners so a detached image cannot change state', () => {
    const image = fakeImage();
    const frame = frameFor(image);
    frame.destroy();
    image.dispatchEvent(new Event('error'));
    assert.equal(frame.state, 'pending');
});
