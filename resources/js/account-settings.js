const sections = ['profile', 'security', 'notifications'];

export function accountSettings(options, browser = globalThis) {
    return {
        currentTab: options.initial,
        onHistory: null,
        sectionFromUrl() {
            const url = new URL(browser.location.href);
            const section = url.searchParams.get('section');
            if (sections.includes(section)) return section;
            const hash = url.hash.slice(1);
            return sections.includes(hash) ? hash : 'profile';
        },
        init() {
            if (options.forceInitial) {
                const url = new URL(browser.location.href);
                url.searchParams.set('section', options.initial);
                url.hash = '';
                browser.history.replaceState(null, '', url);
            } else {
                this.currentTab = this.sectionFromUrl();
            }
            this.onHistory = () => { this.currentTab = this.sectionFromUrl(); };
            browser.addEventListener('popstate', this.onHistory);
            browser.addEventListener('hashchange', this.onHistory);
        },
        destroy() {
            browser.removeEventListener('popstate', this.onHistory);
            browser.removeEventListener('hashchange', this.onHistory);
        },
        setTab(section) {
            if (!sections.includes(section)) return;
            const url = new URL(browser.location.href);
            url.searchParams.set('section', section);
            url.hash = '';
            if (url.href !== browser.location.href) browser.history.pushState(null, '', url);
            this.currentTab = section;
        },
    };
}
