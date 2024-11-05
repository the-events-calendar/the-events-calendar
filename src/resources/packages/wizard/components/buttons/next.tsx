import React from "react";
import { Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

const NextButton = ({moveToNextTab, disabled}) => (
	<Button
		variant="primary"
		onClick= {moveToNextTab}
		disabled={disabled}
	>
		{__('Continue', 'the-events-calendar')}
	</Button>
);

export default NextButton;
