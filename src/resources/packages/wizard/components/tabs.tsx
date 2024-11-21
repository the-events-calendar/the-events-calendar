import React, { useRef, KeyboardEvent } from "react";
import { __ } from "@wordpress/i18n";
import { useState, useEffect } from "@wordpress/element";
import { useSelect, useDispatch } from "@wordpress/data";
import { SETTINGS_STORE_KEY, MODAL_STORE_KEY } from "../data";
import TecIcon from "./img/tec";
import MemoizedTabPanel from "./tabs/TabPanel";
import Tab from "./tabs/tab";
import WelcomeContent from "./tabs/welcome/tab";
import DisplayContent from "./tabs/display/tab";
import SettingsContent from "./tabs/settings/tab";
import VenueContent from "./tabs/venue/tab";
import TicketsContent from "./tabs/tickets/tab";
import OrganizerContent from "./tabs/organizer/tab";

const OnboardingTabs = () => {
	type TabConfig = {
		id: string;
		title: string;
		content: React.ComponentType;
		ref: React.RefObject<HTMLDivElement>;
	};

	const tabConfig = [
		{ id: "welcome", title: __("Welcome", "the-events-calendar"), content: WelcomeContent, ref: useRef(null) },
		{ id: "display", title: __("Display", "the-events-calendar"), content: DisplayContent, ref: useRef(null) },
		{ id: "settings", title: __("Settings", "the-events-calendar"), content: SettingsContent, ref: useRef(null) },
		{ id: "organizer", title: __("Organizer", "the-events-calendar"), content: OrganizerContent, ref: useRef(null) },
		{ id: "venue", title: __("Venue", "the-events-calendar"), content: VenueContent, ref: useRef(null) },
		{ id: "tickets", title: __("Tickets", "the-events-calendar"), content: TicketsContent, ref: useRef(null) }
	];

	const { closeModal } = useDispatch(MODAL_STORE_KEY);

	const settings = useSelect(select => {
		return select(SETTINGS_STORE_KEY).getSettings();
	}, []);

	const [activeTab, setActiveTab] = useState(0);

	const [tabsState, setTabsState] = useState(() =>
		tabConfig.map((tab: TabConfig, index) => ({
			...tab,
			disabled: index > 0, // Disable all tabs except the first one
		}))
	);

	const handleClick = (index) => {
		if (!tabsState[index].disabled) {
			setActiveTab(index);
		}
	};

	const handleKeyPress = (event) => {
		if (event.key === "ArrowRight") changeTab(1);
		if (event.key === "ArrowLeft") changeTab(-1);
	};

	const changeTab = (direction) => {
		const newIndex = activeTab + direction;
		if (newIndex >= 0 && newIndex < tabsState.length && !tabsState[newIndex].disabled) {
			setActiveTab(newIndex);
			tabsState[newIndex].ref.current.focus();
		}
	};

	const updateTabState = (index, changes) => {
		setTabsState((prevState) =>
			prevState.map((tab, i) => (i === index ? { ...tab, ...changes } : tab))
		);
	};

	const moveToNextTab = () => {
		if (activeTab < tabsState.length - 1) {
			updateTabState(activeTab, { completed: true });
			updateTabState(activeTab + 1, { disabled: false });
			setActiveTab(prevActiveTab => {
				const newTab = prevActiveTab + 1;
				tabsState[newTab].ref.current.focus();  // Set focus here
				return newTab;
			});
		} else {
			closeModal();
		}
	};

	const skipToNextTab = () => {
		if (activeTab < tabsState.length - 1) {
			updateTabState(activeTab + 1, { disabled: false });
			setActiveTab(prevActiveTab => {
				const newTab = prevActiveTab + 1;
				tabsState[newTab].ref.current.focus();  // Set focus here
				return newTab;
			});
		} else {
			closeModal();
		}
	}

	return (
		<section className={`tec-events-onboarding__tabs tec-events-onboarding__tab-${tabsState[activeTab].id}`}>
			<div className="tec-events-onboarding__tabs-header">
				<TecIcon />
				<ul
					role="tablist"
					className="tec-events-onboarding__tabs-list"
					aria-label="Onboarding Tabs"
					onKeyDown={handleKeyPress}
				>
					{tabsState.map((tab, index) => (
						<Tab
							key={tab.id}
							index={index}
							tab={tab}
							activeTab={activeTab}
							handleChange={handleClick}
						/>
					))}
				</ul>
			</div>
			{tabsState.map((tab, index) => (
				<MemoizedTabPanel
					key={tab.id}
					tabIndex={index}
					id={`${tab.id}Panel`}
					tabId={tab.id}
					activeTab={activeTab}
				>
					<tab.content moveToNextTab={moveToNextTab} skipToNextTab={skipToNextTab}  />
				</MemoizedTabPanel>
			))}
		</section>
	);
};

export default OnboardingTabs;
