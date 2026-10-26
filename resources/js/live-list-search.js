// Progressive GET enhancement: the server remains the sole search/authorization authority.
export function liveListSearch() {
    return {
        loading: false,
        error: '',
        timer: null,
        controller: null,
        sequence: 0,
        form: null,
        initialPath: null,
        scope: null,
        onClick: null,
        onPop: null,
        init() {
            this.form = this.$el;
            this.initialPath = window.location.pathname;
            this.scope = this.form.closest('[data-live-search-surface]');
            this.onClick = (event) => {
                const link = event.target.closest('a[href]');
                if (!link || event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || link.target) return;
                const url = new URL(link.href, window.location.href);
                if (url.origin !== window.location.origin || ![new URL(this.form.action).pathname, this.initialPath].includes(url.pathname)) return;
                event.preventDefault();
                this.load(url, true);
            };
            this.onPop = () => this.load(new URL(window.location.href), false);
            this.scope?.addEventListener('click', this.onClick);
            window.addEventListener('popstate', this.onPop);
        },
        liveSearch() {
            clearTimeout(this.timer);
            // Invalidate an in-flight response immediately, before the debounce fires.
            ++this.sequence;
            this.controller?.abort();
            this.timer = setTimeout(() => this.search(), 300);
        },
        search() {
            clearTimeout(this.timer);
            const query = this.form.elements.namedItem('q')?.value.trim() ?? '';
            if (query.length === 1 || query.length > 100) {
                ++this.sequence;
                this.controller?.abort();
                this.loading = false;
                this.scope?.querySelector('[data-live-search-results]')?.removeAttribute?.('aria-busy');
                this.error = 'Enter 2 to 100 characters, or clear the search.';
                return;
            }
            const url = new URL(this.form.action, window.location.href);
            for (const [name, value] of new FormData(this.form)) {
                if (String(value).trim() !== '') url.searchParams.set(name, String(value));
            }
            return this.load(url, false, true);
        },
        async load(url, push = false, replace = false) {
            clearTimeout(this.timer);
            this.controller?.abort();
            const controller = new AbortController();
            this.controller = controller;
            const sequence = ++this.sequence;
            this.loading = true;
            this.error = '';
            // Only the updating region is marked busy; delivered results stay readable.
            this.scope?.querySelector('[data-live-search-results]')?.setAttribute?.('aria-busy', 'true');
            try {
                const response = await fetch(url, { signal: controller.signal, credentials: 'same-origin', headers: { Accept: 'text/html' } });
                if (sequence !== this.sequence) return;
                const redirected = response.url && new URL(response.url).pathname !== url.pathname;
                if ([401, 403, 404].includes(response.status) || redirected) {
                    // Remove already rendered private snippets when the server revokes access.
                    const target = this.scope?.querySelector('[data-live-search-results]');
                    const tabs = this.scope?.querySelector('[data-live-search-tabs]');
                    if (target) target.innerHTML = '';
                    if (tabs) tabs.innerHTML = '';
                    this.error = 'Search access changed. Sign in or reload this page to continue.';
                    return;
                }
                if (!response.ok) throw new Error('Search unavailable');
                const html = await response.text();
                if (sequence !== this.sequence) return;
                const doc = new DOMParser().parseFromString(html, 'text/html');
                const incoming = doc.querySelector('[data-live-search-results]');
                const target = this.scope?.querySelector('[data-live-search-results]');
                if (!incoming || !target) throw new Error('Search response unavailable');
                target.innerHTML = incoming.innerHTML;
                const input = this.form.elements.namedItem('q');
                const value = url.searchParams.get('q') ?? '';
                if (input && input.value !== value) input.value = value;
                for (const name of ['filter', 'type']) {
                    const field = this.form.elements.namedItem(name);
                    if (field && url.searchParams.has(name)) field.value = url.searchParams.get(name);
                }
                // Tabs are rendered from the same response, preserving counts and active styles.
                const currentTabs = this.scope?.querySelector('[data-live-search-tabs]');
                const newTabs = doc.querySelector('[data-live-search-tabs]');
                if (currentTabs && newTabs) currentTabs.innerHTML = newTabs.innerHTML;
                if (push) window.history.pushState({}, '', url);
                else if (replace) window.history.replaceState({}, '', url);
            } catch (error) {
                if (sequence !== this.sequence || error.name === 'AbortError') return;
                this.error = 'Unable to complete search. Check your connection and retry.';
            } finally {
                if (sequence === this.sequence) {
                    this.loading = false;
                    this.scope?.querySelector('[data-live-search-results]')?.removeAttribute?.('aria-busy');
                }
            }
        },
        destroy() {
            ++this.sequence;
            clearTimeout(this.timer);
            this.controller?.abort();
            this.scope?.removeEventListener('click', this.onClick);
            window.removeEventListener('popstate', this.onPop);
        },
    };
}
