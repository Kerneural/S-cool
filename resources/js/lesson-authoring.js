export function lessonAuthoring(options = {}, browser = globalThis) {
    return {
        tab: options.initialTab || 'edit',
        previewHtml: '',
        loading: false,
        errorMessage: '',
        lastRenderedContent: null,
        abortController: null,
        previewUrl: options.previewUrl || '',
        csrfToken: options.csrfToken || '',
        emptyPlaceholder: options.emptyPlaceholder || 'Chưa có nội dung bài học để xem trước.',
        loadingMessage: options.loadingMessage || 'Đang tải bản xem trước...',
        defaultErrorMessage: options.errorMessage || 'Không thể tải bản xem trước. Vui lòng thử lại.',

        getTextarea() {
            if (this.$refs && this.$refs.textarea) {
                return this.$refs.textarea;
            }
            if (this.$el && typeof this.$el.querySelector === 'function') {
                return this.$el.querySelector('textarea');
            }
            return null;
        },

        setTab(newTab) {
            this.tab = newTab;
            if (newTab === 'preview') {
                this.fetchPreview();
            }
        },

        async fetchPreview() {
            const textarea = this.getTextarea();
            const content = textarea ? textarea.value : '';

            if (!content || !content.trim()) {
                this.previewHtml = `<div class="italic text-slate-400 dark:text-navy-300 py-6 text-center">${this.emptyPlaceholder}</div>`;
                this.lastRenderedContent = content;
                this.loading = false;
                this.errorMessage = '';
                return;
            }

            if (this.lastRenderedContent === content && this.previewHtml) {
                return;
            }

            if (!this.previewUrl) {
                this.errorMessage = this.defaultErrorMessage;
                return;
            }

            if (this.abortController) {
                this.abortController.abort();
            }

            if (typeof browser.AbortController === 'function') {
                this.abortController = new browser.AbortController();
            }

            this.loading = true;
            this.errorMessage = '';

            const csrf = this.csrfToken
                || (typeof browser.document !== 'undefined' && browser.document.querySelector ? browser.document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') : '')
                || '';

            try {
                const response = await browser.fetch(this.previewUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ content }),
                    signal: this.abortController ? this.abortController.signal : undefined,
                });

                if (!response.ok) {
                    throw new Error('Preview request rejected with status ' + response.status);
                }

                const data = await response.json();
                if (typeof data.html !== 'string') {
                    throw new Error('Invalid preview response structure');
                }

                this.previewHtml = data.html;
                this.lastRenderedContent = content;
            } catch (err) {
                if (err && err.name === 'AbortError') {
                    return;
                }
                this.errorMessage = this.defaultErrorMessage;
            } finally {
                this.loading = false;
            }
        },

        applyFormat(type) {
            if (this.tab !== 'edit') {
                this.tab = 'edit';
            }

            const textarea = this.getTextarea();
            if (!textarea) return;

            const val = textarea.value;
            const start = textarea.selectionStart ?? val.length;
            const end = textarea.selectionEnd ?? val.length;
            const selectedText = val.substring(start, end);

            let newVal = val;
            let newSelectionStart = start;
            let newSelectionEnd = end;

            if (type === 'bold') {
                const replacement = selectedText ? `**${selectedText}**` : '****';
                newVal = val.substring(0, start) + replacement + val.substring(end);
                if (selectedText) {
                    newSelectionStart = start + 2;
                    newSelectionEnd = end + 2;
                } else {
                    newSelectionStart = start + 2;
                    newSelectionEnd = start + 2;
                }
            } else if (type === 'italic') {
                const replacement = selectedText ? `*${selectedText}*` : '**';
                newVal = val.substring(0, start) + replacement + val.substring(end);
                if (selectedText) {
                    newSelectionStart = start + 1;
                    newSelectionEnd = end + 1;
                } else {
                    newSelectionStart = start + 1;
                    newSelectionEnd = start + 1;
                }
            } else if (type === 'link') {
                if (selectedText.startsWith('http://') || selectedText.startsWith('https://')) {
                    const replacement = `[Tiêu đề liên kết](${selectedText})`;
                    newVal = val.substring(0, start) + replacement + val.substring(end);
                    newSelectionStart = start + 1;
                    newSelectionEnd = start + 1 + 'Tiêu đề liên kết'.length;
                } else if (selectedText) {
                    const replacement = `[${selectedText}](https://example.com)`;
                    newVal = val.substring(0, start) + replacement + val.substring(end);
                    newSelectionStart = start + selectedText.length + 3;
                    newSelectionEnd = newSelectionStart + 'https://example.com'.length;
                } else {
                    const replacement = '[Tiêu đề liên kết](https://example.com)';
                    newVal = val.substring(0, start) + replacement + val.substring(end);
                    newSelectionStart = start + 1;
                    newSelectionEnd = start + 1 + 'Tiêu đề liên kết'.length;
                }
            } else if (['h2', 'bullet', 'number', 'quote'].includes(type)) {
                const lineStart = val.lastIndexOf('\n', start - 1) + 1;
                let lineEnd = val.indexOf('\n', end);
                if (lineEnd === -1) lineEnd = val.length;

                const block = val.substring(lineStart, lineEnd);
                const lines = block.split('\n');

                let formattedLines = [];
                if (type === 'h2') {
                    formattedLines = lines.map(line => {
                        const stripped = line.replace(/^#+\s*/, '');
                        return `## ${stripped}`;
                    });
                } else if (type === 'bullet') {
                    formattedLines = lines.map(line => {
                        const stripped = line.replace(/^(\d+\.\s*|[-*]\s*)/, '');
                        return `- ${stripped}`;
                    });
                } else if (type === 'number') {
                    formattedLines = lines.map((line, idx) => {
                        const stripped = line.replace(/^(\d+\.\s*|[-*]\s*)/, '');
                        return `${idx + 1}. ${stripped}`;
                    });
                } else if (type === 'quote') {
                    formattedLines = lines.map(line => line.startsWith('> ') ? line : `> ${line}`);
                }

                const replacement = formattedLines.join('\n');
                newVal = val.substring(0, lineStart) + replacement + val.substring(lineEnd);

                if (start === end) {
                    const delta = formattedLines[0].length - lines[0].length;
                    newSelectionStart = start + delta;
                    newSelectionEnd = newSelectionStart;
                } else {
                    newSelectionStart = lineStart;
                    newSelectionEnd = lineStart + replacement.length;
                }
            }

            textarea.value = newVal;

            if (typeof textarea.setSelectionRange === 'function') {
                textarea.setSelectionRange(newSelectionStart, newSelectionEnd);
            }
            if (typeof textarea.focus === 'function') {
                textarea.focus();
            }

            if (typeof browser.Event === 'function') {
                textarea.dispatchEvent(new browser.Event('input', { bubbles: true }));
            }
        },
    };
}
