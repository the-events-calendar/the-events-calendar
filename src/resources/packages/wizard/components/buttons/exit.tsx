import React from "react";
import { Button, Modal } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

const ExitButton = ({closeModal}) => (
	<Button
		variant="tertiary"
		onClick={closeModal}
	>
		{__('Skip guided setup', 'the-events-calendar')}
	</Button>
);

export default ExitButton;
