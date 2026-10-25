// Per-image loading state for an already authorized <img>. Without JS the image
// renders natively; the frame only hides it while a real request is pending.
export function mediaFrame() {
    return {
        state: 'ready',
        generation: 0,
        image: null,
        observer: null,

        init() {
            this.attach(this.$el.querySelector('img'));
        },

        destroy() {
            this.observer?.disconnect();
            this.image?.removeEventListener('load', this.handleLoad);
            this.image?.removeEventListener('error', this.handleError);
        },

        attach(image) {
            if (!image) {
                this.state = 'missing';
                return;
            }
            this.image = image;
            this.handleLoad = () => this.settle(this.generation);
            this.handleError = () => this.finish(this.generation, 'error');
            image.addEventListener('load', this.handleLoad);
            image.addEventListener('error', this.handleError);
            if (typeof MutationObserver !== 'undefined') {
                this.observer = new MutationObserver(() => this.track());
                this.observer.observe(image, { attributes: true, attributeFilter: ['src', 'srcset'] });
            }
            this.track();
        },

        // Each source starts a new generation so an older load/decode cannot settle it.
        track() {
            const generation = ++this.generation;
            if (!this.image.getAttribute('src')) {
                this.finish(generation, 'missing');
            } else if (this.image.complete) {
                this.image.naturalWidth > 0 ? this.settle(generation) : this.finish(generation, 'error');
            } else {
                this.state = 'pending';
            }
        },

        settle(generation) {
            if (typeof this.image.decode !== 'function') {
                this.finish(generation, 'ready');
                return;
            }
            this.image.decode().then(
                () => this.finish(generation, 'ready'),
                () => this.finish(generation, 'error'),
            );
        },

        finish(generation, state) {
            if (generation === this.generation) {
                this.state = state;
            }
        },
    };
}
