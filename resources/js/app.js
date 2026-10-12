import './bootstrap';

import Alpine from 'alpinejs';
import { invitationAcceptance } from './invitation-acceptance';

window.Alpine = Alpine;

Alpine.data('invitationAcceptance', invitationAcceptance);

Alpine.start();
