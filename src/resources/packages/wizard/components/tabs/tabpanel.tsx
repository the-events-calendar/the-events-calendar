import React, { FunctionComponent } from 'react';

interface TabPanelProps {
	id: string;
	tabId: string;
	tabIndex: number;
	activeTab: number;
}

const TabPanel: FunctionComponent< TabPanelProps > = ( { children, id, tabId, tabIndex, activeTab } ) => {
	return (
		<section
			role="tabpanel"
			id={ id }
			aria-labelledby={ tabId }
			aria-hidden={ activeTab !== tabIndex }
			hidden={ activeTab !== tabIndex }
			tabIndex={ activeTab === tabIndex ? 0 : -1 }
			className={ `tec-events-onboarding__tabpanel tec-events-onboarding__tabpanel-${ tabId } ${
				activeTab === tabIndex ? 'active' : ''
			}` }
		>
			{ children }
		</section>
	);
};

// Explicitly type the memoized component
const MemoizedTabPanel = React.memo( TabPanel ) as React.FC< TabPanelProps >;

export default MemoizedTabPanel;
