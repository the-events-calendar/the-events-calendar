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
	/**
	 * Initial collection of tabs.
	 * @type {Array}
	 *
	 * @property {string}   name               - The name of the tab, used as the "slug".
	 * @property {string}   title              - The title of the tab, displayed in the tab list.
	 * @property {string}   className          - The class name for the tab. This changes based on the tab's state.
	 * @property {string}   baseClass          - The base class name for the tab. This remains unchanged for reference.
	 * @property {boolean}  disabled           - Whether the tab is disabled or not.
	 * @property {function} content           - The content to render for the tab.
	 */
	const initialTabs = [
		{
			name: 'intro',
			title: __('Intro', 'the-events-calendar'),
			className: 'tec-events-onboarding__tab-intro',
			baseClass: 'tec-events-onboarding__tab-intro',
			disabled: false,
			content: IntroContent
		},
		{
			name: 'display',
			title: __('Calendar Display', 'the-events-calendar'),
			className: 'tec-events-onboarding__tab-display',
			baseClass: 'tec-events-onboarding__tab-display',
			disabled: true,
			content: DisplayContent
		},
		{
			name: 'settings',
			title: __('Event Settings', 'the-events-calendar'),
			className: 'tec-events-onboarding__tab-settings',
			baseClass: 'tec-events-onboarding__tab-settings',
			disabled: true,
			content: SettingsContent
		},
		{
			name: 'organizer',
			title: __('Event Organizer', 'the-events-calendar'),
			className: 'tec-events-onboarding__tab-organizer',
			baseClass: 'tec-events-onboarding__tab-organizer',
			disabled: true,
			content: OrganizerContent
		},
		{
			name: 'venue',
			title: __('Event Venue', 'the-events-calendar'),
			className: 'tec-events-onboarding__tab-venue',
			baseClass: 'tec-events-onboarding__tab-venue',
			disabled: true,
			content: VenueContent
		},
		{
			name: 'tickets',
			title: __('Tickets', 'the-events-calendar'),
			className: 'tec-events-onboarding__tab-tickets',
			baseClass: 'tec-events-onboarding__tab-tickets',
			disabled: true,
			content: TicketsContent
		},
	];

	/**
	 * State management for tabs.
	 *
	 * @property {Array} tabs - The current state of tabs.
	 */
	const [tabs, setTabs] = useState(initialTabs);

	/**
	 * State management for active tab.
	 *
	 * @property {string} activeTab - The currently active tab.
	 * @default 'intro'
	 */
	const [activeTab, setActiveTab] = useState('intro');

	/**
	 * Class names for completed tab state.
	 *
	 * @type {string}
	 */
	const completedClass = "tec-events-onboarding__tab--completed";

	/**
	 * Class names for active tab state.
	 *
	 * @type {string}
	 */
	const activeClass = "tec-events-onboarding__tab--active";

	/**
	 * "Clean" the tab classes by removing the "active" class from all tabs.
	 */
	const cleanTabClasses = () => {
		const cleanedTabs = tabs.map(tab => (
			// We just want to remove the "active" class from all tabs prior to setting the next one.
			tab.className = tab.className.replace(activeClass, "")
		));

		return setTabs(cleanedTabs);
	};

	/**
	 * Mark a tab as active by name.
	 *
	 * @param {string} tabName - The name of the tab to mark as active.
	 */
	const setAsActive = (tabName) => {
		const activeTabs = tabs.map(tab => {
			if (tab.name === tabName) {
				// Add the active class to the active tab.
				return { ...tab, className: tab.className + " " + activeClass };
			}
			return tab;
		});

		// Update the state with the active tabs.
		setTabs(activeTabs);

		// Set the next tab as the active tab.
		setActiveTab(tabName);
	};

	/**
	 * Mark a tab as completed by name.
	 *
	 * @param {string} tabName - The name of the tab to mark as completed.
	 */
	const completeTab = (tabName) => {
		const completedTabs = tabs.map(tab => {
			if (tab.name === tabName) {
				// Add the completed class to the completed tab.
				return { ...tab, className: tab.className + " " + completedClass };
			}
			return tab;
		});

		setTabs(completedTabs);
	}

	/**
	 * Enable a tab by name.
	 *
	 * @param {string} tabName - The name of the tab to enable.
	 */
	const enableTab = (tabName) => {
		const enabledTabs = tabs.map(tab => {
			if (tab.name === tabName) {
				// Add the active class to the active tab.
				return { ...tab, disabled: false };
			}

			return tab;
		});

		// Update the state with the enabled tabs.
		setTabs(enabledTabs);
	}

	/**
	 * Move to the next tab, completing (saving) the current tab.
	 */
	const moveToNextTab = () => {
		const currentIndex = tabs.findIndex(tab => tab.name === activeTab);
		const currentTab = tabs[currentIndex];
		const nextIndex = currentIndex + 1 < tabs.length ? currentIndex + 1 : 0; // Loop back to first tab if we reach the end. For now.
		const nextTab = tabs[nextIndex];

		// Remove all "active" classes.
		{cleanTabClasses()}

		// Mark the current tab complete.
		{completeTab(currentTab.name)}

		// Enable the next tab before we move to it.
		{enableTab(nextTab.name)}

		// Add the active class to the correct tab.
		setAsActive(nextTab.name);
	};

	/**
	 * Skip to the next tab, without completing/saving the current tab.
	 */
	const SkipToNextTab = () => {
		const currentIndex = tabs.findIndex(tab => tab.name === activeTab);
		const nextIndex = currentIndex + 1 < tabs.length ? currentIndex + 1 : 0; // Loop back to first tab if we reach the end. For now.
		const nextTab = tabs[nextIndex];

		{cleanTabClasses()}

		// Enable the next tab before we move to it.
		{enableTab(nextTab.name)}

		// Add the active class to the correct tab.
		setAsActive(nextTab.name);
	}

	// Set modal class to help enforce active tab styling.
	const activeTabObj = tabs.find(t => t.name === activeTab);
	const modalClass = "tec-events-onboarding__tab-panel " + activeTabObj.baseClass;

	return (
		<>
			<TecIcon.default />
			<TabPanel
				activeClass={activeClass}
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
