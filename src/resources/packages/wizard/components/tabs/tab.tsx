import React, { FunctionComponent, LegacyRef } from "react";

interface TabProps {
	id: string;
	title: string;
	activeTab: number;
	index: number;
	tabPanelId: string;
	handleChange: (event) => void;
	tabRef: LegacyRef<HTMLButtonElement>;
}

const Tab: FunctionComponent<TabProps> = ({
	index,
	tab,
	activeTab,
	handleChange
}) => {
	const handleClick = () => handleChange(index);

	/**
	 * Add classes based on activeTab and completed status.
	 *
	 * @returns string
	 */
	const tabClasses = () => {
		let classes = "tec-events-onboarding__tab";

		if ( tab.disabled ) {
			classes += " tec-events-onboarding__tab--disabled";
		}

		if (activeTab === index) {
			classes += " tec-events-onboarding__tab--active";
		}

		if ( tab.completed ) {
			classes += " tec-events-onboarding__tab--completed";
		}

		return classes;
	}

	return (
	<li role="presentation" className={tabClasses()}>
		<button
			aria-controls={tab.panelId}
			aria-selected={activeTab === index}
			className="tec-events-onboarding__tab-button"
			disabled={tab.disabled}
			id={tab.id}
			onClick={handleClick}
			ref={tab.ref}
			role="tab"
			tabIndex={activeTab === index ? 0 : -1}
		>
		{tab.title}
		</button>
	</li>
	);
};

export default Tab;
