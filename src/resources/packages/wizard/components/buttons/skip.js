import { Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

const SkipButton = ({SkipToNextTab}) => {
	return (
		<Button
			variant="tertiary"
			onClick={SkipToNextTab}
		>
			{__( "Skip step", "the-events-calendar" )}
		</Button>
	);
};

export default SkipButton;
