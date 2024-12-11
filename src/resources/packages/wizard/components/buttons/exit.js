import { Button, Modal } from '@wordpress/components';

const ExitButton = ({closeModal}) => (
	<Button
	  variant="secondary"
	  onClick={ closeModal }
	>
	  Skip guided setup
	</Button>
);

export default ExitButton;
