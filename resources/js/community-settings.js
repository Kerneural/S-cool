export function communitySettings(options, browser = globalThis) {
    return {
        coverName: '', settingsDirty: false, coverDirty: false, settingsSubmitting: false, coverSubmitting: false,
        initialSettings: '', beforeLeave: null,
        snapshot() {
            return JSON.stringify([...new browser.FormData(this.$refs.settingsForm).entries()]);
        },
        init() {
            this.initialSettings = this.snapshot();
            this.beforeLeave = event => {
                if ((this.settingsDirty || this.coverDirty) && !this.settingsSubmitting && !this.coverSubmitting) {
                    event.preventDefault();
                    event.returnValue = '';
                }
            };
            browser.addEventListener('beforeunload', this.beforeLeave);
        },
        settingsChanged() { this.settingsDirty = this.snapshot() !== this.initialSettings; },
        submitCover(event) {
            if (this.coverSubmitting) { event.preventDefault(); return; }
            if (this.settingsDirty && !browser.confirm(options.discardSettings)) { event.preventDefault(); return; }
            this.coverSubmitting = true;
        },
        submitSettings(event) {
            if (this.settingsSubmitting) { event.preventDefault(); return; }
            if (this.coverDirty && !browser.confirm(options.discardCover)) { event.preventDefault(); return; }
            this.settingsSubmitting = true;
        },
        destroy() { browser.removeEventListener('beforeunload', this.beforeLeave); },
    };
}
