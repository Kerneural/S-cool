export function invitationAcceptance(browser = window) {
    return {
        token: '',
        init() {
            const tokens = new URLSearchParams(browser.location.hash.slice(1)).getAll('token');
            this.token = tokens.length === 1 && tokens[0].length === 64 && /^[a-f0-9]{64}$/.test(tokens[0]) ? tokens[0] : '';
            // Keep the secret only in this page, never in URLs, history or storage.
            browser.history.replaceState(null, '', browser.location.pathname);
        },
    };
}
