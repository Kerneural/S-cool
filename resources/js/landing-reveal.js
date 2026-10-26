// Replay section reveals when the landing content re-enters the viewport. Content is only
// hidden after an observer is running, and every element is shown again on teardown,
// reduced-motion activation (outside the owner-selected autoplay landing) or setup failure.
export function landingReveal(root = document, browser = window) {
    const elements = [...root.querySelectorAll('[data-reveal]')];
    const html = root.documentElement;
    const autoplay = html?.dataset.landingMotion === 'autoplay';
    const media = browser.matchMedia?.('(prefers-reduced-motion: reduce)');
    if (!elements.length || !html || (!autoplay && media?.matches) || typeof browser.IntersectionObserver === 'undefined') {
        return () => {};
    }

    let observer = null;
    const revealAll = () => elements.forEach(element => element.classList.add('is-revealed'));
    const teardown = () => {
        observer?.disconnect();
        observer = null;
        media?.removeEventListener?.('change', onMotionChange);
        revealAll();
        html.classList.remove('landing-reveal-ready');
    };
    const onMotionChange = event => { if (event.matches) teardown(); };

    try {
        observer = new browser.IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-revealed');
                } else {
                    entry.target.classList.remove('is-revealed');
                }
            });
        }, { rootMargin: '0px 0px -8% 0px', threshold: 0.1 });
        elements.forEach(element => observer.observe(element));
        if (!autoplay) media?.addEventListener?.('change', onMotionChange);
        html.classList.add('landing-reveal-ready');
    } catch {
        teardown();
        return () => {};
    }

    return teardown;
}
