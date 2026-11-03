import { inboxPanel } from './inbox-panel';
import { socialComposer } from './social-composer';
import { communitySettings } from './community-settings';
import './bootstrap';
import { conversationStream } from './conversation-stream';
import { invitationAcceptance } from './invitation-acceptance';
import { lessonProgress } from './lesson-progress';
import { feedDiscussionMetadata } from './feed-discussion-metadata';
import { notificationPreferences } from './notification-preferences';
import { accountSettings } from './account-settings';
import { liveListSearch } from './live-list-search';
import { landingReveal } from './landing-reveal';
import { courseOutline } from './course-outline';
import { mediaFrame } from './media-frame';
import { lessonAuthoring } from './lesson-authoring';

document.addEventListener('alpine:init', () => {
    window.Alpine.data('socialComposer', socialComposer);
    window.Alpine.data('inboxPanel', inboxPanel);
    window.Alpine.data('invitationAcceptance', invitationAcceptance);
    window.Alpine.data('lessonProgress', lessonProgress);
    window.Alpine.data('feedDiscussionMetadata', feedDiscussionMetadata);
    window.Alpine.data('notificationPreferences', notificationPreferences);
    window.Alpine.data('accountSettings', accountSettings);
    window.Alpine.data('liveListSearch', liveListSearch);
    window.Alpine.data('courseOutline', courseOutline);
    window.Alpine.data('mediaFrame', mediaFrame);
    window.Alpine.data('lessonAuthoring', lessonAuthoring);
    window.Alpine.data('conversationStream', conversationStream);
    window.Alpine.data('communitySettings', communitySettings);
});

if (document.querySelector('[data-reveal]')) {
    const stopLandingReveal = landingReveal();
    window.addEventListener('pagehide', stopLandingReveal, { once: true });
}

document.addEventListener('livewire:init', () => {
    window.Livewire.hook('commit', ({ component, fail }) => {
        fail(() => component.el.dispatchEvent(new CustomEvent('search-request-failed')));
    });
});
