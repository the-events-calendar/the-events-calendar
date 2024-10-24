import { TabPanel, VisuallyHidden } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import * as IntroContent from './tabs/intro';
import * as DisplayContent from './tabs/display';
import * as OrganizerContent from './tabs/organizer';
import * as SettingsContent from './tabs/settings';
import * as VenueContent from './tabs/venue';
import * as TicketsContent from './tabs/tickets';

const OnboardingTabs = ({ closeModal }) => {
	const initialTabs = [
		{
			name: 'intro',
			title: __('Intro', 'the-events-calendar'),
			className: 'tec-events-onboarding__tab--intro',
			disabled: false,
			content: IntroContent },
		{
			name: 'display',
			title: __('Display', 'the-events-calendar'),
			className: 'tec-events-onboarding__tab--display',
			disabled: true,
			content: DisplayContent },
		{
			name: 'settings',
			title: __('Settings', 'the-events-calendar'),
			className: 'tec-events-onboarding__tab--settings',
			disabled: true,
			content: SettingsContent },
		{
			name: 'organizer',
			title: __('Organizer', 'the-events-calendar'),
			className: 'tec-events-onboarding__tab--organizer',
			disabled: true,
			content: OrganizerContent },
		{
			name: 'venue',
			title: __('Venue', 'the-events-calendar'),
			className: 'tec-events-onboarding__tab--venue',
			disabled: true,
			content: VenueContent },
		{
			name: 'tickets',
			title: __('Tickets', 'the-events-calendar'),
			className: 'tec-events-onboarding__tab--tickets',
			disabled: true,
			content: TicketsContent },
	];

	const [tabs, setTabs] = useState(initialTabs);
	const [activeTab, setActiveTab] = useState('intro');

	const moveToNextTab = () => {
		const currentIndex = tabs.findIndex(tab => tab.name === activeTab);
		const nextIndex = currentIndex + 1 < tabs.length ? currentIndex + 1 : 0; // Loop back to first tab if we reach the end.
		const nextTab = tabs[nextIndex];

		// Enable the next tab if it's currently disabled.
		if (nextTab.disabled) {
			const updatedTabs = tabs.map((tab, index) =>
				index === nextIndex ? { ...tab, disabled: false } : tab
			);

			setTabs(updatedTabs);
		}

		// Set the next tab as the active tab.
		setActiveTab(nextTab.name);
	};

	return (
		<TabPanel
			activeClass="active-tab"
			initialTabName="intro"
			className="tec-events-onboarding__tab-panel"
			onSelect={setActiveTab}
			tabs={tabs}
		>
			{(tab) => {
				const newTab = tabs.find(t => t.name === activeTab);
				return (
				<>
					<VisuallyHidden>
						<h2>{newTab.title}</h2>
					</VisuallyHidden>
					{ newTab.content.default({closeModal, moveToNextTab}) }
				</>
			)}}
		</TabPanel>
	);
};

export default OnboardingTabs;
