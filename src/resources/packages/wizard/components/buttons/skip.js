import { Button } from '@wordpress/components';

const handleSkip = () => {};

const SkipButton = () => (
	<Button
	  variant="secondary"
	  onClick={ handleSkip }
	>
	  Skip step
	</Button>
);

export default SkipButton;
