export function conversationStream(browser = globalThis) {
    return {
        nearBottom: true,
        hasNew: false,
        observer: null,
        signature: '',
        init() {
            this.signature = this.ids();
            this.observer = new browser.MutationObserver(() => {
                const ids = this.ids();
                if (ids === this.signature) return;
                this.signature = ids;
                if (this.nearBottom) this.$nextTick(() => this.jumpToLatest());
                else this.hasNew = true;
            });
            this.observer.observe(this.$refs.stream, { childList: true, subtree: true });
            this.onSent = () => this.$nextTick(() => this.jumpToLatest());
            this.$el.addEventListener('conversation-sent', this.onSent);
            this.$nextTick(() => this.jumpToLatest());
        },
        ids() { return [...this.$refs.stream.querySelectorAll('[data-message-id]')].map(e => e.dataset.messageId).join(','); },
        jumpToLatest() {
            const stream = this.$refs.stream;
            stream.scrollTop = stream.scrollHeight;
            this.hasNew = false;
            this.nearBottom = true;
        },
        destroy() { this.observer?.disconnect(); this.$el.removeEventListener('conversation-sent', this.onSent); },
    };
}
