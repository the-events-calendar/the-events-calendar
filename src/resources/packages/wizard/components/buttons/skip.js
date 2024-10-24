import { Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

const SkipButton = ({moveToNextTab}) => {
	const handleSkip = ({moveToNextTab}) => {
		{moveToNextTab}
	};

	return (
		<Button
			variant="secondary"
			onClick={moveToNextTab}
		>
			{__( "Skip step", "the-events-calendar" )}
		</Button>
	);
};

export default SkipButton;
