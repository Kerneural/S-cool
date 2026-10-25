// Lesson rail state: which sections are open, a title-only filter over the
// lessons already rendered for this viewer, and completion synced from the
// existing lesson-progress-updated event. Native <details> keep it usable
// without JavaScript; this only adds search and live counts.
const normalize = value => String(value ?? '')
    .normalize('NFD')
    .replace(/[̀-ͯ]/g, '')
    .replace(/đ/g, 'd')
    .replace(/Đ/g, 'D')
    .toLowerCase()
    .trim();

export function courseOutline({ sections = [], currentSection = null } = {}) {
    const titles = {};
    const completed = {};
    const lessonsBySection = {};
    sections.forEach(section => {
        lessonsBySection[section.id] = section.lessons.map(lesson => lesson.id);
        section.lessons.forEach(lesson => {
            titles[lesson.id] = normalize(lesson.title);
            completed[lesson.id] = Boolean(lesson.completed);
        });
    });

    return {
        query: '',
        open: currentSection === null ? {} : { [currentSection]: true },
        searchOpen: {},
        saved: null,
        completed,
        panelOpen: false,

        queueCurrent(list) {
            // Let Alpine apply visibility and <details open> before measuring.
            // A one-shot task also works when background tabs suspend frames.
            setTimeout(() => this.revealCurrent(list), 0);
        },
        revealCurrent(list) {
            // Mobile initializes with display:none. Measure only after opening,
            // and scroll the rail itself rather than scrollIntoView on the page.
            if (this.searching || !list?.clientHeight) return;
            const row = list.querySelector('[aria-current="page"]');
            if (!row) return;
            const bounds = list.getBoundingClientRect();
            const current = row.getBoundingClientRect();
            if (!current.height || (current.top >= bounds.top && current.bottom <= bounds.bottom)) return;
            list.scrollTop = Math.max(0, list.scrollTop + current.top - bounds.top - list.clientHeight / 3);
        },
        get searching() {
            return normalize(this.query) !== '';
        },
        setQuery(value) {
            const wasSearching = this.searching;
            this.query = value;
            if (!wasSearching && this.searching) {
                this.saved = { ...this.open };
                this.searchOpen = {};
            } else if (wasSearching && !this.searching) {
                this.open = this.saved ?? this.open;
                this.saved = null;
                this.searchOpen = {};
            }
        },
        clear() {
            this.setQuery('');
        },
        matches(lessonId) {
            return !this.searching || titles[lessonId].includes(normalize(this.query));
        },
        sectionMatches(sectionId) {
            return (lessonsBySection[sectionId] ?? []).filter(id => this.matches(id)).length;
        },
        get matchCount() {
            return Object.keys(titles).filter(id => this.matches(id)).length;
        },
        isOpen(sectionId) {
            if (this.searching) {
                return this.searchOpen[sectionId] ?? this.sectionMatches(sectionId) > 0;
            }
            return Boolean(this.open[sectionId]);
        },
        // Native <details> report every open/close, including ones caused by
        // our own :open binding; only record a change the user actually made.
        onToggle(sectionId, isNowOpen) {
            if (isNowOpen === this.isOpen(sectionId)) return;
            if (this.searching) {
                this.searchOpen = { ...this.searchOpen, [sectionId]: isNowOpen };
            } else {
                this.open = { ...this.open, [sectionId]: isNowOpen };
            }
        },
        sectionCompleted(sectionId) {
            return (lessonsBySection[sectionId] ?? []).filter(id => this.completed[id]).length;
        },
        progressUpdated(detail) {
            if (!detail || !(detail.lessonId in this.completed)) return;
            this.completed = { ...this.completed, [detail.lessonId]: Boolean(detail.completed) };
        },
    };
}
