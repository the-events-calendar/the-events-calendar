import { Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

const SetupButton = ({moveToNextTab}) => (
	<Button
		variant="primary"
		onClick={moveToNextTab}
	>
		{__("Set up my calendar", "the-events-calendar")}
	</Button>
);

export default SetupButton;
