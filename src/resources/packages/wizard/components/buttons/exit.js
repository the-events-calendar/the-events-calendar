import { Button, Modal } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

const ExitButton = ({closeModal}) => (
	<Button
		variant="secondary"
		onClick={closeModal}
	>
		{__('Skip guided setup', 'the-events-calendar')}
	</Button>
);

export default ExitButton;
