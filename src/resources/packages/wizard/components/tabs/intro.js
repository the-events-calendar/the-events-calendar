import { __ } from '@wordpress/i18n';
import SetupButton from '../buttons/setup';
import ExitButton from '../buttons/exit';
import OptInCheckbox from '../input/opt-in';
import Illustration from '../img/wizard-intro-illo.png';

const IntroContent = ({closeModal, moveToNextTab}) => {

	return (
		<>
			<img src={Illustration} className="tec-events-onboarding__intro-header" alt="Welcome" />
			<h1>{__("Welcome to The Events Calendar", "the-events-calendar")}</h1>
			<p>{__("Congratulations on installing the best event management solution for WordPress. Let’s tailor your experience to your needs.", "the-events-calendar")}</p>
			<p><SetupButton moveToNextTab={moveToNextTab}/></p>
			<p><ExitButton closeModal={closeModal} /></p>
			<OptInCheckbox />
		</>
	);
};

export default IntroContent;
