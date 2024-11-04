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
	fieldValues,
}) => (
  <section
    role="tabpanel"
    id={id}
    aria-labelledby={tabId}
    hidden={activeTab !== tabIndex}
    tabIndex={0}
	className="tec-events-onboarding__tabpanel"
  >
    <div className="tec-events-onboarding__tabpanel-content">
		{children}
	</div>
  </section>
);

export default TabPanel;
