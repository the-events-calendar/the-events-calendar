import React, { useRef, useState, KeyboardEvent } from "react";
import { __ } from "@wordpress/i18n";
import TabPanel from "./tabs/TabPanel";
import Tab from "./tabs/Tab";
import TecIcon from "./img/tec";
import WelcomeContent from "./tabs/welcome/tab";
import DisplayContent from "./tabs/display/tab";
import SettingsContent from "./tabs/settings/tab";
import VenueContent from "./tabs/venue/tab";
import TicketsContent from "./tabs/tickets/tab";
import OrganizerContent from "./tabs/organizer/tab";

const OnboardingTabs = ({bootData, closeModal}) => {
	/**
	 * An object containing tab information.
	 *
	 * @property title - The title of the tab.
	 * @property disabled - Whether the tab is disabled.
	 * @property completed - Whether the tab is completed.
	 * @property ref - A reference to the tab element.
	 */
	const InitialTabs = {
		1: { id: "welcome", panelId: "welcomePanel", title: __("Welcome", "the-events-calendar" ), disabled: false, completed: false, ref: useRef(null) },
		2: { id: "display", panelId: "displayPanel", title: __("Display", "the-events-calendar" ), disabled: true, completed: false, ref: useRef(null) },
		3: { id: "settings", panelId: "settingsPanel", title: __("Settings", "the-events-calendar" ), disabled: true, completed: false, ref: useRef(null) },
		4: { id: "organizer", panelId: "organizerPanel", title: __("Organizer", "the-events-calendar" ), disabled: true, completed: false, ref: useRef(null) },
		5: { id: "venue", panelId: "venuePanel", title: __("Venue", "the-events-calendar" ), disabled: true, completed: false, ref: useRef(null) },
		6: { id: "tickets", panelId: "ticketsPanel", title: __("Tickets", "the-events-calendar" ), disabled: true, completed: false, ref: useRef(null) }
	};

	/**
		 * State management for active tab.
		 *
		 * @property activeTab - The currently active tab.
		 * @default 1
		 */
	const [ activeTab, setActiveTab ] = useState(1);

	const [ Tabs, setTabs ] = useState(InitialTabs);

	/**
	 * Handle tab click event.
	 *
	 * @param index - The index of the clicked tab.
	 */
	const handleClick = (index: number) => {
		if (Tabs[index].disabled) {
			return;
		}

		setActiveTab(index);
		setTabs(Tabs);
	};

	/**
	 * Count the number of tabs.
	 *
	 * @returns The number of tabs.
	 */
	const countTabs = () => {
		return Object.keys(Tabs).length;
	}

	/**
	 * Enable a tab by index.
	 *
	 * @param index - The index of the tab to enable.
	 */
	const enableTab = (index: number) => {
		Tabs[index].disabled = false;

		setTabs(Tabs);
	}

	/**
	 * Complete a tab by index.
	 *
	 * @param index - The index of the tab to complete.
	 */
	const completeTab = (index: number) => {
		Tabs[index].completed = true;

		setTabs(Tabs);
	}

	/**
	 * Handle keyboard navigation.
	 *
	 * @param event - The keyboard event.
	 */
	const handleKeyPress = (event: KeyboardEvent<HTMLUListElement>) => {
		if (event.key === "ArrowLeft") {
			handleNextTab();
		}

		if (event.key === "ArrowRight") {
			handlePrevTab();
		}
	};

	/**
	 * Move to the next tab.
	 */
	const handleNextTab = () => {
		const tabToSelect = activeTab + 1;

		// Can't move outside the range of Tabs.
		if ( tabToSelect > countTabs() ) {
			return;
		}

		// Can't select a disabled tab.
		if (Tabs[tabToSelect].disabled) {
			return;
		}

		setActiveTab(tabToSelect);
		Tabs[tabToSelect].ref.current.focus();

		setTabs(Tabs);
	};

	/**
	 * Move to the previous tab.
	 */
	const handlePrevTab = () => {
		const tabToSelect = activeTab - 1;

		// Can't move outside the range of Tabs.
		if ( tabToSelect < 0 ) {
			return;
		}

		// Can't select a disabled tab.
		if (Tabs[tabToSelect].disabled) {
			return;
		}

		setActiveTab(tabToSelect);
		Tabs[tabToSelect].ref.current.focus();

		setTabs(Tabs);
	};

	const wrapperClass = "tec-events-onboarding__tabs tec-events-onboarding__tab-" + Tabs[activeTab].id;

	/**
	 * Move to the next tab, upon completing (saving) the current tab.
	 */
	const moveToNextTab = () => {
		if (activeTab < countTabs() ) {
			completeTab(activeTab);
			enableTab(activeTab + 1);
			handleNextTab();
			setTabs(Tabs);
		} else {
			// Need to handle save of last tab.
			closeModal();
		}
	};

	/**
	 * Skip to the next tab, without completing/saving the current tab.
	 */
	const skipToNextTab = () => {
		if (activeTab < countTabs()) {
			enableTab(activeTab + 1);
			handleNextTab();
			setTabs(Tabs);
		} else {
			closeModal();
		}
	}

	return (
		<section className={wrapperClass}>
			<div className="tec-events-onboarding__tabs-header">
				<TecIcon />
				<ul
					role="tablist"
					className="tec-events-onboarding__tabs-list"
					aria-label="Food Tabs"
					onKeyDown={handleKeyPress}
				>
					<Tab
						index={2}
						tab={Tabs[2]}
						activeTab={activeTab}
						handleChange={handleClick}
					/>
					<Tab
						index={3}
						tab={Tabs[3]}
						activeTab={activeTab}
						handleChange={handleClick}
					/>
					<Tab
						index={4}
						tab={Tabs[4]}
						activeTab={activeTab}
						handleChange={handleClick}
					/>
					<Tab
						index={5}
						tab={Tabs[5]}
						activeTab={activeTab}
						handleChange={handleClick}
					/>
					<Tab
						index={6}
						tab={Tabs[6]}
						activeTab={activeTab}
						handleChange={handleClick}
					/>
				</ul>
			</div>
			<TabPanel
				tabIndex={1}
				id={Tabs[1].panelId}
				tabId={Tabs[1].id}
				activeTab={activeTab}
			>
				<WelcomeContent
					closeModal={closeModal}
					moveToNextTab={moveToNextTab}
					bootData={bootData}
				/>
			</TabPanel>
			<TabPanel
				tabIndex={2}
				id={Tabs[2].panelId}
				tabId={Tabs[2].id}
				activeTab={activeTab}
			>
				<DisplayContent
					closeModal={closeModal}
					moveToNextTab={moveToNextTab}
					skipToNextTab={skipToNextTab}
					bootData={bootData}
				/>
			</TabPanel>
			<TabPanel
				tabIndex={3}
				id={Tabs[3].panelId}
				tabId={Tabs[3].id}
				activeTab={activeTab}
			>
				<SettingsContent
					closeModal={closeModal}
					moveToNextTab={moveToNextTab}
					skipToNextTab={skipToNextTab}
					bootData={bootData}
				/>
			</TabPanel>
			<TabPanel
				tabIndex={4}
				id={Tabs[4].panelId}
				tabId={Tabs[4].id}
				activeTab={activeTab}
			>
				<OrganizerContent
					closeModal={closeModal}
					moveToNextTab={moveToNextTab}
					skipToNextTab={skipToNextTab}
					bootData={bootData}
				/>
			</TabPanel>
			<TabPanel
				tabIndex={5}
				id={Tabs[5].panelId}
				tabId={Tabs[5].id}
				activeTab={activeTab}
			>
				<VenueContent
					closeModal={closeModal}
					moveToNextTab={moveToNextTab}
					skipToNextTab={skipToNextTab}
					bootData={bootData}
				/>
			</TabPanel>
			<TabPanel
				tabIndex={6}
				id={Tabs[6].panelId}
				tabId={Tabs[6].id}
				activeTab={activeTab}
			>
				<TicketsContent
					closeModal={closeModal}
					moveToNextTab={moveToNextTab}
					bootData={bootData}
				/>
			</TabPanel>
		</section>
	);
};

export default OnboardingTabs;
