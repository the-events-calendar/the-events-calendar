import { Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

const handleSkip = () => {};

const SkipButton = () => (
	<Button
		variant="secondary"
		onClick={ handleSkip }
	>
	  __( "Skip step", "the-events-calendar" )
	</Button>
);

export default SkipButton;
