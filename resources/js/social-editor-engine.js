import { Editor } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import { Markdown } from '@tiptap/markdown';

export function createSocialEditor(element, options) {
    const escape = s => s.replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;');
    const editor = new Editor({
        element,
        extensions: [StarterKit.configure({ heading: false, codeBlock: false, horizontalRule: false, link: { openOnClick: false } }), Markdown.configure({ markedOptions: { gfm: true, breaks: true } })],
        content: options.format === 'markdown' ? options.content : '<p>' + escape(options.content).replaceAll('\n', '<br>') + '</p>',
        ...(options.format === 'markdown' ? { contentType: 'markdown' } : {}),
        editorProps: {
            attributes: { class: 'social-editor-content', role: 'textbox', 'aria-label': options.label, 'aria-multiline': 'true', 'data-placeholder': options.placeholder || '' },
            handlePaste(_view, event) {
                if (!event.clipboardData?.files?.length || !options.files) return false;
                event.preventDefault(); options.files([...event.clipboardData.files]); return true;
            },
            handleDrop(_view, event, _slice, moved) {
                if (moved || !event.dataTransfer?.files?.length || !options.files) return false;
                event.preventDefault(); options.files([...event.dataTransfer.files]); return true;
            },
        },
        onUpdate: () => options.change(editor.getMarkdown()),
    });
    return {
        command(type) {
            const chain = editor.chain().focus();
            if (type === 'bold') chain.toggleBold().run();
            if (type === 'italic') chain.toggleItalic().run();
            if (type === 'list') chain.toggleBulletList().run();
            if (type === 'quote') chain.toggleBlockquote().run();
            if (type === 'link') {
                const url = window.prompt(options.linkPrompt, 'https://');
                if (url === null) return;
                if (url === '') { chain.unsetLink().run(); return; }
                try {
                    const parsed = new URL(url);
                    if (parsed.protocol !== 'https:' || parsed.username || parsed.password) throw new Error();
                    chain.extendMarkRange('link').setLink({ href: url }).run();
                } catch { options.error(options.badLink); }
            }
        },
        emoji(text) { editor.chain().focus().insertContent({ type: 'text', text }).run(); },
        clear() { editor.commands.clearContent(false); },
        destroy() { editor.destroy(); },
    };
}
