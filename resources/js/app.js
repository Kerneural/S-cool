import './bootstrap';
import { invitationAcceptance } from './invitation-acceptance';
import { lessonProgress } from './lesson-progress';
import { feedDiscussionMetadata } from './feed-discussion-metadata';
import { notificationPreferences } from './notification-preferences';
import { accountSettings } from './account-settings';
import { liveListSearch } from './live-list-search';

document.addEventListener('alpine:init', () => {
    window.Alpine.data('invitationAcceptance', invitationAcceptance);
    window.Alpine.data('lessonProgress', lessonProgress);
    window.Alpine.data('feedDiscussionMetadata', feedDiscussionMetadata);
    window.Alpine.data('notificationPreferences', notificationPreferences);
    window.Alpine.data('accountSettings', accountSettings);
    window.Alpine.data('liveListSearch', liveListSearch);
});

document.addEventListener('livewire:init', () => {
    window.Livewire.hook('commit', ({ component, fail }) => {
        fail(() => component.el.dispatchEvent(new CustomEvent('search-request-failed')));
    });
});
