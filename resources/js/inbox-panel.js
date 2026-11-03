export function inboxPanel(options = {}, browser = globalThis) {
    return {
        panel: '', toast: '', toastKind: 'messages', timer: null, refreshing: false,
        init() {
            this.onBack = () => this.$wire.backToInbox();
            browser.addEventListener('inbox-back', this.onBack);
        },
        toggle(kind) {
            if (this.panel === kind) { this.closePanel(); return; }
            this.panel = kind;
            this.refresh();
            this.$nextTick(() => (kind === 'messages' ? this.$refs.messagePanel : this.$refs.notificationPanel)?.focus());
        },
        closePanel(restore = true) {
            const kind = this.panel;
            this.panel = '';
            if (restore) this.$refs[kind]?.focus();
        },
        async refresh() {
            if (this.refreshing || browser.document.hidden) return;
            this.refreshing = true;
            try { await this.$wire.refreshInbox(); } finally { this.refreshing = false; }
        },
        arrived(detail) {
            this.toastKind = detail.message ? 'messages' : 'notifications';
            this.toast = detail.message ? (options.newMessage || 'Bạn có tin nhắn mới') : (options.newNotification || 'Bạn có thông báo mới');
            browser.clearTimeout(this.timer);
            this.timer = browser.setTimeout(() => { this.toast = ''; }, 6000);
        },
        destroy() { browser.clearTimeout(this.timer); browser.removeEventListener('inbox-back', this.onBack); },
    };
}
