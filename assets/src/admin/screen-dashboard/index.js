import jQuery from 'jquery';

import connectButton from './connect-button';
import connectSettings from './connect-settings';
import welcome from './welcome';

jQuery(() => {
	connectButton();
	connectSettings();
	welcome();
});
