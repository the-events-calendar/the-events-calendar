import { __ } from '@wordpress/i18n';
import NextButton from '../buttons/next';
import SkipButton from '../buttons/skip';
import ViewCheckbox from '../input/view-checkbox';

const DisplayContent = ({closeModal, moveToNextTab, skipToNextTab}) => {
	const views = [
		'Month',
		'Day',
		'List',
		'Week',
		'Map',
		'Photo',
		'Summary',
	];
	return (
		<>
			<h1>{__("How do you want people to view your calendar?", "the-events-calendar")}</h1>
			<p>{__("Select how you want to display your events on your site. You can choose more than one.", "the-events-calendar")}</p>
			<div className="tec-events-onboarding__grid--view-checkbox">
				{views.map((view) => (
					<ViewCheckbox key={view} view={view} />
				))}
			</div>
			<p><NextButton moveToNextTab={moveToNextTab}/></p>
			<p><SkipButton skipToNextTab={skipToNextTab}/></p>
		</>
	);
};

export default DisplayContent;
