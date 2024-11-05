import React, { FunctionComponent } from "react";

interface TabPanelProps {
	id: string;
	tabId: string;
	tabIndex: number;
	activeTab: number;
}

const TabPanel: FunctionComponent<TabPanelProps> = ({
	children,
	id,
	tabId,
	tabIndex,
	activeTab,
}) => {
	const isActive = activeTab === tabIndex;

	return (
		<section
			role="tabpanel"
			id={id}
			aria-labelledby={tabId}
			aria-hidden={!isActive}
			hidden={!isActive}
			tabIndex={isActive ? 0 : -1}
			className={`tec-events-onboarding__tabpanel ${isActive ? "active" : ""}`}
		>
			<div className="tec-events-onboarding__tabpanel-content">
				{children}
			</div>
		</section>
	);
};

// Explicitly type the memoized component
const MemoizedTabPanel = React.memo(TabPanel) as React.FC<TabPanelProps>;

export default MemoizedTabPanel;
