import React from "react";
import { CheckboxControl } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import DayViewIcon from '../img/day';
import MonthViewIcon from '../img/month';
import ListViewIcon from '../img/list';
import PhotoViewIcon from '../img/photo';
import MapViewIcon from '../img/map';
import SummaryViewIcon from '../img/summary';
import WeekViewIcon from '../img/week';

const ViewCheckbox = ({view}) => {
	const lowerCaseView = view.toLowerCase();
	const [ isChecked, setChecked ] = useState( false );
	const icons = new Map();
	icons.set('Day', <DayViewIcon />);
	icons.set('Month', <MonthViewIcon />);
	icons.set('List', <ListViewIcon />);
	// pro
	icons.set('Map', <MapViewIcon />);
	icons.set('Photo', <PhotoViewIcon />);
	icons.set('Week', <WeekViewIcon />);
	icons.set('Summary', <SummaryViewIcon />);

	return (
		<div
			alignment="top"
			justify="center"
			spacing={0}
			id={`tec-events-onboarding__checkbox-${lowerCaseView}`}
			className="tec-events-onboarding__checkbox tec-events-onboarding__checkbox--view"
		>
			<CheckboxControl
				__nextHasNoMarginBottom
				aria-describedby={`tec-events-onboarding__checkbox-label-${lowerCaseView}`}
				checked={isChecked}
				onChange={setChecked}
				id={`tec-events-onboarding__checkbox-input-${lowerCaseView}`}
				className="tec-events-onboarding__checkbox-input"
				value={lowerCaseView}
			/>
			<div>
				<label
					id={`tec-events-onboarding__checkbox-label-${lowerCaseView}`}
					htmlFor={`tec-events-onboarding__checkbox-input-${lowerCaseView}`}
					className={isChecked ? "tec-events-onboarding__checkbox-label tec-events-onboarding__checkbox-label--checked" : "tec-events-onboarding__checkbox-label"}
				>
					{icons.get(view)}
					{'all' !== lowerCaseView ? view : __( 'Select all the views', 'the-events-calendar' )}
				</label>
			</div>
		</div>
	);
};

export default ViewCheckbox;
