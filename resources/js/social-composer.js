const instances = new WeakMap();

export function validateSocialFiles(files, kind, existing = [], messages = {}) {
    const limit = kind === 'images' ? 4 : 3;
    const extensions = kind === 'images' ? /\.(jpe?g|png|webp)$/i : /\.(pdf|docx|xlsx|pptx)$/i;
    if (files.length + existing.length > limit) return messages.count;
    if (files.some(file => !extensions.test(file.name) || file.size > (kind === 'images' ? 5 : 10) * 1024 * 1024)) return messages.type;
    return null;
}

export function socialComposer(options = {}) {
    return {
        formattingOpen: false,
        body: options.body || '', format: options.format || 'plain', ready: false, error: '',
        images: [], documents: [], emojiOpen: false, uploading: false, progress: 0,
        submitting: false, pollEnabled: options.pollEnabled || false, choices: options.choices?.length ? options.choices : ['', ''],
        emojis: ['😀', '😊', '❤️', '👍', '👏', '🎉', '🔥', '💡', '✅', '🙏', '🚀', '🤔'],
        async init() {
            const root = this.$root;
            this.nativeForm = options.wireBody ? null : root.closest('form');
            this.initialValues = this.formValues();
            this.onUnload = event => {
                if (!this.submitting && this.dirty()) { event.preventDefault(); event.returnValue = ''; }
            };
            window.addEventListener('beforeunload', this.onUnload);
            if (this.nativeForm) {
                this.onSubmit = event => this.submit(event);
                this.nativeForm.addEventListener('submit', this.onSubmit);
            }
            try {
                const { createSocialEditor } = await import('./social-editor-engine');
                if (!root.isConnected) return;
                const editor = createSocialEditor(this.$refs.editor, {
                    content: this.body, format: this.format, label: options.label, placeholder: options.placeholder,
                    linkPrompt: options.linkPrompt, badLink: options.badLink,
                    files: options.allowFiles ? files => this.addFiles(files) : null,
                    change: body => { this.body = body; this.format = 'markdown'; this.sync(); },
                    error: error => { this.error = error; },
                });
                instances.set(root, editor); this.ready = true;
                this.$refs.source.required = false;
            } catch { this.ready = false; }
        },
        sync() {
            if (options.wireBody) {
                this.$wire.set(options.wireBody, this.body, false);
                this.$wire.set(options.wireFormat, this.format, false);
            }
            this.$dispatch('social-draft-updated', { body: this.body, hasFiles: !!(this.images.length + this.documents.length) });
        },
        formValues() {
            if (!this.nativeForm) return '[]';
            return JSON.stringify([...new FormData(this.nativeForm)].filter(([key, value]) =>
                !key.startsWith('_') && !['body', 'content_format'].includes(key)
                && typeof value === 'string' && value.trim() !== ''));
        },
        dirty() {
            return this.body !== (options.body || '') || this.format !== (options.format || 'plain')
                || this.images.length || this.documents.length
                || (this.nativeForm && this.formValues() !== this.initialValues)
                || (options.wireBody === 'newCommentBody' && (this.$wire?.commentImages?.length || this.$wire?.commentDocuments?.length));
        },
        command(type) { instances.get(this.$root)?.command(type); },
        emoji(text) {
            if (this.ready) instances.get(this.$root)?.emoji(text);
            else { this.body += text; this.sync(); }
            this.emojiOpen = false;
        },
        addFiles(files) {
            this.error = '';
            const images = files.filter(file => /\.(jpe?g|png|webp)$/i.test(file.name));
            const documents = files.filter(file => !images.includes(file));
            const error = validateSocialFiles(images, 'images', this.images, options.filesMessage)
                || validateSocialFiles(documents, 'documents', this.documents, options.filesMessage);
            const all = [...this.images, ...this.documents, ...files];
            if (error || all.reduce((sum, f) => sum + f.size, 0) > 20 * 1024 * 1024) {
                this.error = error || options.filesMessage.total; return;
            }
            this.images.push(...images.map(file => ({ file, name: file.name, size: file.size, preview: URL.createObjectURL(file) })));
            this.documents.push(...documents.map(file => ({ file, name: file.name, size: file.size })));
            this.syncFiles(); this.sync();
        },
        removeFile(kind, index) {
            const removed = this[kind].splice(index, 1)[0];
            if (removed?.preview) URL.revokeObjectURL(removed.preview);
            this.syncFiles(); this.sync();
        },
        syncFiles() {
            for (const kind of ['images', 'documents']) {
                const transfer = new DataTransfer();
                this[kind].forEach(entry => transfer.items.add(entry.file));
                this.$refs[kind].files = transfer.files;
            }
        },
        async submit(event) {
            if (this.submitting) { event.preventDefault(); return; }
            if (!this.body.trim()) { event.preventDefault(); this.error = options.empty; return; }
            if (!options.ajax) { this.submitting = true; return; }
            event.preventDefault();
            this.error = ''; this.submitting = true; this.uploading = true; this.progress = 0;
            const form = this.nativeForm;
            const data = new FormData(form);
            const request = new XMLHttpRequest();
            request.open(form.method, form.action);
            request.setRequestHeader('Accept', 'application/json');
            request.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            request.upload.onprogress = e => { if (e.lengthComputable) this.progress = Math.round(e.loaded * 100 / e.total); };
            request.onload = () => {
                this.uploading = false;
                let result = {};
                try { result = JSON.parse(request.responseText); } catch {}
                if (request.status >= 200 && request.status < 300 && result.redirect) {
                    location.assign(result.redirect); return;
                }
                this.submitting = false;
                this.error = Object.values(result.errors || {}).flat().join(' ') || options.failed;
            };
            request.onerror = request.onabort = () => { this.submitting = false; this.uploading = false; this.error = options.failed; };
            request.send(data);
        },
        resetDraft() {
            this.body = ''; this.format = 'plain'; this.formattingOpen = false; instances.get(this.$root)?.clear();
            this.$root.querySelectorAll('input[type=file]').forEach(input => { input.value = ''; });
            this.images.forEach(entry => URL.revokeObjectURL(entry.preview));
            this.images = []; this.documents = []; this.pollEnabled = false; this.choices = ['', ''];
            if (options.allowFiles) this.syncFiles();
            this.sync();
        },
        destroy() {
            instances.get(this.$root)?.destroy(); instances.delete(this.$root);
            this.images.forEach(entry => URL.revokeObjectURL(entry.preview));
            window.removeEventListener('beforeunload', this.onUnload);
            this.nativeForm?.removeEventListener('submit', this.onSubmit);
        },
    };
}
