import { Button } from '@wordpress/components';

const SetupButton = ({moveToNextTab}) => (
	<Button
		variant="primary"
		onClick={moveToNextTab}
	>
	Set up my calendar
	</Button>
);

export default SetupButton;
