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

const icons = new Map();
icons.set('day', <DayViewIcon />);
icons.set('month', <MonthViewIcon />);
icons.set('list', <ListViewIcon />);
// pro
icons.set('map', <MapViewIcon />);
icons.set('photo', <PhotoViewIcon />);
icons.set('week', <WeekViewIcon />);
icons.set('summary', <SummaryViewIcon />);

const ViewCheckbox = ({view, checked}) => {
	const [ isChecked, setChecked ] = useState( checked );

	return (
		<div
			id={`tec-events-onboarding__checkbox-${view}`}
			className="tec-events-onboarding__checkbox tec-events-onboarding__checkbox--view"
		>
			<CheckboxControl
				__nextHasNoMarginBottom
				aria-describedby={`tec-events-onboarding__checkbox-label-${view}`}
				checked={isChecked}
				onChange={setChecked}
				id={`tec-events-onboarding__checkbox-input-${view}`}
				className="tec-events-onboarding__checkbox-input"
				value={view}
			/>
			<div>
				<label
					id={`tec-events-onboarding__checkbox-label-${view}`}
					htmlFor={`tec-events-onboarding__checkbox-input-${view}`}
					className={isChecked ? "tec-events-onboarding__checkbox-label tec-events-onboarding__checkbox-label--checked" : "tec-events-onboarding__checkbox-label"}
				>
					{icons.get(view)}
					{'all' !== view ? view : __( 'Select all the views', 'the-events-calendar' )}
				</label>
			</div>
		</div>
	);
};

export default ViewCheckbox;
