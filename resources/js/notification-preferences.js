const fields = ['notify_replies', 'notify_courses'];

export function notificationPreferences(options, browser = globalThis) {
    return {
        values: { ...options.values },
        confirmed: { ...options.values },
        failures: {},
        queue: [],
        saving: false,
        savedMessage: false,
        enhanced: false,
        init() { this.enhanced = typeof browser.fetch === 'function'; },
        setPreference(field, value) {
            if (!fields.includes(field) || typeof value !== 'boolean') return;
            this.values[field] = value;
            delete this.failures[field];
            this.savedMessage = false;
            if (!this.queue.includes(field)) this.queue.push(field);
            return this.flush();
        },
        retry(field) {
            // Retry the intended value, never invert an already changed checkbox.
            return this.setPreference(field, this.values[field]);
        },
        async flush() {
            if (this.saving) return;
            this.saving = true;
            while (this.queue.length) {
                const field = this.queue.shift();
                const intended = this.values[field];
                try {
                    const response = await browser.fetch(options.url, {
                        method: 'PATCH',
                        headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': options.csrf },
                        body: JSON.stringify({ [field]: intended }),
                        signal: browser.AbortSignal?.timeout(15000),
                    });
                    if (!response.ok) throw new Error('Preference request rejected');
                    const data = await response.json();
                    if (data.status !== 'success' || typeof data[field] !== 'boolean' || data[field] !== intended) {
                        throw new Error('Preference acknowledgment invalid');
                    }
                    // Other fields in a response may be stale. Only reconcile this mutation.
                    this.confirmed[field] = data[field];
                    delete this.failures[field];
                } catch {
                    // A newer queued intention supersedes this failure and gets its own attempt.
                    if (!this.queue.includes(field)) this.failures[field] = true;
                }
            }
            this.saving = false;
            this.savedMessage = !Object.keys(this.failures).length
                && fields.every(field => this.values[field] === this.confirmed[field]);
        },
    };
}
