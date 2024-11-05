import React, { useRef, useState, KeyboardEvent } from "react";
import { __ } from "@wordpress/i18n";
import MemoizedTabPanel from "./tabs/TabPanel";
import Tab from "./tabs/Tab";
import TecIcon from "./img/tec";
import WelcomeContent from "./tabs/welcome/tab";
import DisplayContent from "./tabs/display/tab";
import SettingsContent from "./tabs/settings/tab";
import VenueContent from "./tabs/venue/tab";
import TicketsContent from "./tabs/tickets/tab";
import OrganizerContent from "./tabs/organizer/tab";


const OnboardingTabs = ({ bootData, closeModal }) => {
	const tabConfig = [
		{ id: "welcome", title: __("Welcome", "the-events-calendar"), content: WelcomeContent, dataKey: "optin", ref: useRef(null) },
		{ id: "display", title: __("Display", "the-events-calendar"), content: DisplayContent, dataKey: "activeViews", ref: useRef(null) },
		{ id: "settings", title: __("Settings", "the-events-calendar"), content: SettingsContent, ref: useRef(null) },
		{ id: "organizer", title: __("Organizer", "the-events-calendar"), content: OrganizerContent, dataKey: "organizer", ref: useRef(null) },
		{ id: "venue", title: __("Venue", "the-events-calendar"), content: VenueContent, dataKey: "venue", ref: useRef(null) },
		{ id: "tickets", title: __("Tickets", "the-events-calendar"), content: TicketsContent, dataKey: "eventTickets", ref: useRef(null) }
	];

    const [activeTab, setActiveTab] = useState(0);
    const [tabsState, setTabsState] = useState(() =>
        tabConfig.map((tab: Object, index) => ({
            ...tab,
            disabled: index > 0 && !bootData[tab.dataKey],
            completed: !!bootData[tab.dataKey],
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
            changeTab(1);
        } else {
            closeModal();
        }
    };

	const skipToNextTab = () => {
		if (activeTab < tabsState.length - 1) {
			updateTabState(activeTab + 1, { disabled: false });
            changeTab(1);
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
                    aria-label="Food Tabs"
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
                    <tab.content
                        closeModal={closeModal}
                        moveToNextTab={moveToNextTab}
                        skipToNextTab={skipToNextTab}
                        bootData={bootData}
                    />
                </MemoizedTabPanel>
            ))}
        </section>
    );
};

export default OnboardingTabs;
