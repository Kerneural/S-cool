import './bootstrap';
import { invitationAcceptance } from './invitation-acceptance';

document.addEventListener('alpine:init', () => {
    window.Alpine.data('invitationAcceptance', invitationAcceptance);
});
