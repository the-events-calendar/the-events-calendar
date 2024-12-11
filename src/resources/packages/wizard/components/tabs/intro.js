import SetupButton from '../buttons/setup';
import ExitButton from '../buttons/exit';
import OptInCheckbox from '../input/opt-in';
import Illustration from '../img/wizard-intro-illo.png';


const IntroContent = ({closeModal, tabs, moveToNextTab}) => {

	return (
		<>
			<img src={Illustration} className="tec-events-onboarding__intro-header" alt="Welcome" />
			<h1>Welcome to The Events Calendar</h1>
			<p>Congratulations on installing the best event management solution for WordPress.</p><p>Let’s tailor your experience to your needs.</p>
			<p><SetupButton tabs={tabs} moveToNextTab={ moveToNextTab}/></p>
			<p><ExitButton closeModal={closeModal} /></p>
			<OptInCheckbox />
		</>
	);
};

export default IntroContent;
