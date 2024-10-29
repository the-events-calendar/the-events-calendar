import { TabPanel, VisuallyHidden } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import * as IntroContent from './tabs/intro';
import * as DisplayContent from './tabs/display';
import * as OrganizerContent from './tabs/organizer';
import * as SettingsContent from './tabs/settings';
import * as VenueContent from './tabs/venue';
import * as TicketsContent from './tabs/tickets';
import * as TecIcon from './icons/tec';

const OnboardingTabs = ({ closeModal }) => {
	const initialTabs = [
		{
			name: 'intro',
			title: __('Intro', 'the-events-calendar'),
			className: 'tec-events-onboarding__tab-intro',
			disabled: false,
			content: IntroContent,
			current: true
		},
		{
			name: 'display',
			title: __('Calendar Display', 'the-events-calendar'),
			className: 'tec-events-onboarding__tab-display',
			disabled: true,
			content: DisplayContent,
			current: false
		},
		{
			name: 'settings',
			title: __('Event Settings', 'the-events-calendar'),
			className: 'tec-events-onboarding__tab-settings',
			disabled: true,
			content: SettingsContent,
			current: false
		},
		{
			name: 'organizer',
			title: __('Event Organizer', 'the-events-calendar'),
			className: 'tec-events-onboarding__tab-organizer',
			disabled: true,
			content: OrganizerContent,
			current: false
		},
		{
			name: 'venue',
			title: __('Event Venue', 'the-events-calendar'),
			className: 'tec-events-onboarding__tab-venue',
			disabled: true,
			content: VenueContent,
			current: false
		},
		{
			name: 'tickets',
			title: __('Tickets', 'the-events-calendar'),
			className: 'tec-events-onboarding__tab-tickets',
			disabled: true,
			content: TicketsContent,
			current: false
		},
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
				index === currentIndex ? { ...tab, current: false, className: tab.className.replace("tec-events-onboarding__tab--active", "") + " tec-events-onboarding__tab--completed" }
				: index === nextIndex ? { ...tab, disabled: false, current: true, className: tab.className + " tec-events-onboarding__tab--active" }
				: { ...tab }
			);

			setTabs(updatedTabs);
		}

		// Set the next tab as the active tab.
		setActiveTab(nextTab.name);
	};

	const SkipToNextTab = () => {
		const currentIndex = tabs.findIndex(tab => tab.name === activeTab);
		const nextIndex = currentIndex + 1 < tabs.length ? currentIndex + 1 : 0; // Loop back to first tab if we reach the end.
		const nextTab = tabs[nextIndex];

		// Enable the next tab if it's currently disabled.
		if (nextTab.disabled) {
			const updatedTabs = tabs.map((tab, index) =>
				index === nextIndex
				? { ...tab, disabled: false, current: true, className: tab.className + " tec-events-onboarding__tab--active" }
				: { ...tab, current: false, className: tab.className.replace("tec-events-onboarding__tab--active", "")}
			);

			setTabs(updatedTabs);
		}

		// Set the next tab as the active tab.
		setActiveTab(nextTab.name);
	}

	const activeTabObj = tabs.find(t => t.name === activeTab);
	const activeName = activeTabObj ? activeTabObj.className.replace("tec-events-onboarding__tab--active", "").replace("tec-events-onboarding__tab--completed", "") : "";
	const modalClass ="tec-events-onboarding__tab-panel " + activeName;

	return (
		<>
			<TecIcon.default />
			<TabPanel
				activeClass="tec-events-onboarding__tab--active"
				initialTabName="intro"
				className={modalClass}
				onSelect={setActiveTab}
				selectOnMove={false}
				tabs={tabs}
			>
				{(tab) => {
					const newTab = tabs.find(t => t.name === activeTab);
					return (
					<>
						<VisuallyHidden>
							<h2>{newTab.title}</h2>
						</VisuallyHidden>
						{ newTab.content.default({closeModal, moveToNextTab, SkipToNextTab}) }
					</>
				)}}
			</TabPanel>
		</>
	);
};

export default OnboardingTabs;
