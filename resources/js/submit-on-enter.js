export function isSendKey(event) {
    return event.key === 'Enter' && !event.shiftKey && !event.ctrlKey
        && !event.metaKey && !event.altKey && !event.isComposing && event.keyCode !== 229;
}

export function submitOnEnter(event, form, busy = false) {
    if (!isSendKey(event)) return false;
    event.preventDefault();
    if (!busy && form && !form.querySelector('button[type="submit"]:disabled')) form.requestSubmit();
    return true;
}
