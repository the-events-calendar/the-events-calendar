import React from "react";
import { Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

const SkipButton = ({skipToNextTab}) => {
	return (
		<Button
			variant="tertiary"
			onClick={skipToNextTab}
		>
			{__( "Skip step", "the-events-calendar" )}
		</Button>
	);
};

export default SkipButton;
