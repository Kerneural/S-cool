const richEditors = new WeakMap();

export function lessonAuthoring(options = {}, browser = globalThis) {
    return {
        tab: options.initialTab || 'edit',
        previewHtml: '',
        loading: false,
        errorMessage: '',
        lastRenderedContent: null,
        abortController: null,
        previewRequestId: 0,
        previewUrl: options.previewUrl || '',
        csrfToken: options.csrfToken || '',
        emptyPlaceholder: options.emptyPlaceholder || 'Chưa có nội dung bài học để xem trước.',
        loadingMessage: options.loadingMessage || 'Đang tải bản xem trước...',
        defaultErrorMessage: options.errorMessage || 'Không thể tải bản xem trước. Vui lòng thử lại.',

        mode: 'visual',
        visualReady: false,
        dirty: false,
        submitting: false,
        saveLabel: options.saveLabel || 'Chưa có thay đổi',
        expanded: false,
        uploading: false,
        uploadProgress: 0,
        uploadError: '',
        uploadUrl: options.uploadUrl || '',

        async init() {
            const textarea = this.getTextarea();
            this.initialContent = textarea?.value || '';
            this.onInput = () => { this.dirty = textarea.value !== this.initialContent; };
            textarea?.addEventListener('input', this.onInput);
            this.beforeLeave = event => {
                if (this.submitting || (!this.dirty && !this.uploading)) return;
                event.preventDefault();
                event.returnValue = '';
            };
            browser.addEventListener('beforeunload', this.beforeLeave);
            this.form = this.$el.closest('form');
            this.onSubmit = event => {
                if (this.uploading) { event.preventDefault(); this.uploadError = 'Chờ ảnh tải lên xong rồi lưu bài học.'; return; }
                this.submitting = true;
                this.dirty = false;
            };
            this.form?.addEventListener('submit', this.onSubmit);
            try {
                const { createLessonEditor } = options.createEditor
                    ? { createLessonEditor: options.createEditor }
                    : await import('./rich-lesson-editor');
                const editor = createLessonEditor(this.$refs.rich, {
                    content: textarea.value, uploadUrl: this.uploadUrl,
                    change: markdown => {
                        textarea.value = markdown;
                        textarea.dispatchEvent(new browser.Event('input', { bubbles: true }));
                    },
                    upload: file => this.uploadImage(file),
                    error: message => { this.uploadError = message; },
                });
                richEditors.set(this.$root, editor);
                this.visualReady = true;
            } catch {
                this.mode = 'source';
                this.uploadError = 'Không tải được trình soạn thảo trực quan. Bạn vẫn có thể sửa và lưu Markdown.';
            }
        },

        chooseMode(mode) {
            if (mode === 'visual') richEditors.get(this.$root)?.setContent(this.getTextarea().value);
            this.mode = mode;
        },

        async uploadImage(file) {
            if (!file || this.uploading) return;
            this.uploadError = '';
            if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type) || file.size > 2 * 1024 * 1024) {
                this.uploadError = 'Chọn ảnh JPEG, PNG hoặc WebP tối đa 2 MB.';
                return;
            }
            const textarea = this.getTextarea();
            const selection = { start: textarea.selectionStart, end: textarea.selectionEnd };
            this.uploading = true;
            this.uploadProgress = 0;
            try {
                const result = await new Promise((resolve, reject) => {
                    const request = new browser.XMLHttpRequest();
                    this.uploadRequest = request;
                    request.open('POST', this.uploadUrl);
                    request.setRequestHeader('Accept', 'application/json');
                    request.setRequestHeader('X-CSRF-TOKEN', this.csrfToken);
                    request.upload.onprogress = e => { if (e.lengthComputable) this.uploadProgress = Math.round(e.loaded / e.total * 100); };
                    request.onload = () => {
                        let data;
                        try { data = JSON.parse(request.responseText); } catch { reject(new Error('Máy chủ trả về phản hồi không hợp lệ.')); return; }
                        if (request.status < 200 || request.status >= 300) {
                            reject(new Error(data.errors?.image?.[0] || 'Không tải được ảnh. Kiểm tra quyền truy cập và thử lại.'));
                            return;
                        }
                        const base = new URL(this.uploadUrl, browser.location.href).pathname;
                        if (typeof data.url !== 'string' || !data.url.startsWith(base + '/') || !/\/[a-zA-Z0-9]{40}\.(?:jpe?g|png|webp)$/.test(data.url)) {
                            reject(new Error('Địa chỉ ảnh không hợp lệ.')); return;
                        }
                        resolve(data.url);
                    };
                    request.onerror = () => reject(new Error('Mất kết nối. Chọn lại ảnh để thử tải lên.'));
                    request.onabort = () => reject(new Error('Đã hủy tải ảnh.'));
                    request.timeout = 60000;
                    request.ontimeout = () => reject(new Error('Tải ảnh quá lâu. Vui lòng thử lại.'));
                    const form = new browser.FormData();
                    form.append('image', file);
                    request.send(form);
                });
                if (this.mode === 'visual' && this.visualReady) richEditors.get(this.$root)?.insertImage(result, file.name.replace(/\.[^.]+$/, ''));
                else {
                    const alt = file.name.replace(/[\[\]\\r\n]/g, '').replace(/\.[^.]+$/, '');
                    const image = '\n![' + alt + '](' + result + ')\n';
                    textarea.setRangeText(image, selection.start, selection.end, 'end');
                    textarea.dispatchEvent(new browser.Event('input', { bubbles: true }));
                }
            } catch (error) { this.uploadError = error.message; }
            finally { this.uploading = false; this.uploadRequest = null; if (this.$refs.imageInput) this.$refs.imageInput.value = ''; }
        },

        destroy() {
            richEditors.get(this.$root)?.destroy();
            richEditors.delete(this.$root);
            this.uploadRequest?.abort();
            browser.removeEventListener('beforeunload', this.beforeLeave);
            this.form?.removeEventListener('submit', this.onSubmit);
            this.getTextarea()?.removeEventListener('input', this.onInput);
        },

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
            const requestId = ++this.previewRequestId;

            // Invalidate every earlier response, including when the new content is
            // empty or cached. Abort alone cannot guard a response already parsing.
            this.abortController?.abort();
            this.abortController = null;
            this.loading = false;
            this.errorMessage = '';

            if (!content || !content.trim()) {
                this.previewHtml = `<div class="italic text-slate-400 py-6 text-center">${this.emptyPlaceholder}</div>`;
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
                if (requestId !== this.previewRequestId) return;
                if (typeof data.html !== 'string') {
                    throw new Error('Invalid preview response structure');
                }

                this.previewHtml = data.html;
                this.lastRenderedContent = content;
            } catch (err) {
                if (requestId !== this.previewRequestId) return;
                if (err && err.name === 'AbortError') {
                    return;
                }
                this.errorMessage = this.defaultErrorMessage;
            } finally {
                if (requestId === this.previewRequestId) this.loading = false;
            }
        },

        destroy() {
            ++this.previewRequestId;
            this.abortController?.abort();
            this.abortController = null;
        },

        applyFormat(type) {
            if (this.visualReady && this.mode === 'visual') { richEditors.get(this.$root)?.format(type); return; }
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
