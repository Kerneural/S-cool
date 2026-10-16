import './bootstrap';
import { invitationAcceptance } from './invitation-acceptance';
import { lessonProgress } from './lesson-progress';
import { feedDiscussionMetadata } from './feed-discussion-metadata';

document.addEventListener('alpine:init', () => {
    window.Alpine.data('invitationAcceptance', invitationAcceptance);
    window.Alpine.data('lessonProgress', lessonProgress);
    window.Alpine.data('feedDiscussionMetadata', feedDiscussionMetadata);
});
