export function lessonProgress(options, browser = globalThis) {
    return {
        completed: options.completed,
        loading: false,
        errorMessage: '',
        async toggleProgress() {
            if (this.loading) return;
            this.loading = true;
            this.errorMessage = '';
            try {
                const response = await browser.fetch(options.url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': options.csrf,
                    },
                    body: JSON.stringify({ completed: this.completed ? 0 : 1 }),
                });
                if (!response.ok) throw new Error('Progress request rejected');
                const data = await response.json();
                if (typeof data.completed !== 'boolean' || data.lesson_id !== options.lessonId) {
                    throw new Error('Unexpected progress response');
                }
                this.completed = data.completed;
                browser.dispatchEvent(new browser.CustomEvent('lesson-progress-updated', {
                    detail: { lessonId: options.lessonId, completed: data.completed, summary: data },
                }));
            } catch {
                this.errorMessage = options.errorMessage;
            } finally {
                this.loading = false;
            }
        },
    };
}
