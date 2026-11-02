import { Editor } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import Image from '@tiptap/extension-image';
import { Markdown } from '@tiptap/markdown';
import { TableKit } from '@tiptap/extension-table';
import TaskList from '@tiptap/extension-task-list';
import TaskItem from '@tiptap/extension-task-item';

export function managedImage(url, uploadUrl) {
    return typeof url === 'string' && typeof uploadUrl === 'string'
        && url.startsWith(new URL(uploadUrl, 'http://local').pathname + '/')
        && /^[a-zA-Z0-9]{40}\.(?:jpe?g|png|webp)$/.test(url.split('/').pop())
        && !url.includes('?') && !url.includes('#');
}

export function createLessonEditor(element, options) {
    const PrivateImage = Image.extend({
        renderHTML({ HTMLAttributes }) {
            return managedImage(HTMLAttributes.src, options.uploadUrl)
                ? ['img', { ...HTMLAttributes, loading: 'lazy' }]
                : ['span', { class: 'text-muted' }, '[' + (HTMLAttributes.alt || 'Ảnh ngoài ứng dụng') + ']'];
        },
        parseHTML() {
            return [{ tag: 'img[src]', getAttrs: node => managedImage(node.getAttribute('src'), options.uploadUrl) ? null : false }];
        },
    });
    const editor = new Editor({
        element,
        extensions: [
            StarterKit.configure({ heading: { levels: [1, 2, 3, 4, 5, 6] }, link: { openOnClick: false } }),
            PrivateImage, TableKit, TaskList, TaskItem, Markdown.configure({ markedOptions: { gfm: true, breaks: true } }),
        ],
        content: options.content,
        contentType: 'markdown',
        editorProps: {
            attributes: { class: 'lesson-prose lesson-rich-content', role: 'textbox', 'aria-label': 'Nội dung bài học — soạn thảo trực quan', 'aria-multiline': 'true' },
            handlePaste(_view, event) {
                const images = [...(event.clipboardData?.files || [])].filter(f => f.type.startsWith('image/'));
                if (!images.length) return false;
                event.preventDefault();
                options.upload(images[0]);
                return true;
            },
            handleDrop(_view, event, _slice, moved) {
                const images = [...(event.dataTransfer?.files || [])].filter(f => f.type.startsWith('image/'));
                if (moved || !images.length) return false;
                event.preventDefault();
                options.upload(images[0]);
                return true;
            },
        },
        onUpdate: () => options.change(editor.getMarkdown()),
    });
    return {
        setContent: markdown => editor.commands.setContent(markdown, { contentType: 'markdown', emitUpdate: false }),
        insertImage: (url, alt) => editor.chain().focus().setImage({ src: url, alt }).run(),
        format(type) {
            const chain = editor.chain().focus();
            const actions = {
                h2: () => chain.toggleHeading({ level: 2 }), bold: () => chain.toggleBold(),
                italic: () => chain.toggleItalic(), bullet: () => chain.toggleBulletList(),
                number: () => chain.toggleOrderedList(), quote: () => chain.toggleBlockquote(),
                code: () => chain.toggleCodeBlock(), table: () => chain.insertTable({ rows: 3, cols: 3, withHeaderRow: true }),
                undo: () => chain.undo(), redo: () => chain.redo(),
            };
            if (type === 'link') {
                const url = window.prompt('Địa chỉ liên kết HTTPS', editor.getAttributes('link').href || 'https://');
                if (url === null) return;
                if (url === '') { chain.unsetLink().run(); return; }
                if (!/^https:\/\/\S+$/i.test(url)) { options.error('Liên kết phải bắt đầu bằng https://'); return; }
                chain.extendMarkRange('link').setLink({ href: url }).run();
            } else actions[type]?.().run();
        },
        destroy: () => editor.destroy(),
    };
}
