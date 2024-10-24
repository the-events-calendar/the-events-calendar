import { Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

const NextButton = ({moveToNextTab}) => (
	<Button
		variant="primary"
		onClick= {moveToNextTab}
	>
		{__('Continue', 'the-events-calendar')}
	</Button>
);

export default NextButton;
