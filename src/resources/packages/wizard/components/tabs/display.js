import { __ } from '@wordpress/i18n';
import NextButton from '../buttons/next';
import SkipButton from '../buttons/skip';

const DisplayContent = ({closeModal, moveToNextTab}) => {
	return (
		<>
			<h1>{__("How do you want people to view your calendar?", "the-events-calendar")}</h1>
			<p>{__("Select how you want to display your events on your site. You can choose more than one.", "the-events-calendar")}</p>
			<div>Icons go here</div>
			<p><NextButton moveToNextTab={moveToNextTab}/></p>
			<p><SkipButton moveToNextTab={moveToNextTab}/></p>
		</>
	);
};

export default DisplayContent;
