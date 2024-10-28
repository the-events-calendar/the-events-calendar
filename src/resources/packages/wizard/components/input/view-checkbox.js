import { CheckboxControl } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import * as DayViewIcon from '../icons/day';
import * as MonthViewIcon from '../icons/month';
import * as ListViewIcon from '../icons/list';
import * as PhotoViewIcon from '../icons/photo';
import * as MapViewIcon from '../icons/map';
import * as SummaryViewIcon from '../icons/summary';
import * as WeekViewIcon from '../icons/week';

const ViewCheckbox = ({view}) => {
	const [ isChecked, setChecked ] = useState( false );
	const icons = new Map();
	icons.set('Day', DayViewIcon.default());
	icons.set('Month', MonthViewIcon.default());
	icons.set('List', ListViewIcon.default());
	// pro
	icons.set('Map', MapViewIcon.default());
	icons.set('Photo', PhotoViewIcon.default());
	icons.set('Week', WeekViewIcon.default());
	icons.set('Summary', SummaryViewIcon.default());

	return (
		<div
			alignment="top"
			justify="center"
			spacing={0}
			id={"tec-events-onboarding__checkbox-" + view}
			className="tec-events-onboarding__checkbox tec-events-onboarding__checkbox--view"
		>
			<CheckboxControl
				__nextHasNoMarginBottom
				aria-describedby={"tec-events-onboarding__checkbox-label--" + view}
				checked={isChecked}
				onChange={setChecked}
				id={"tec-events-onboarding__checkbox-input-" + view}
			/>
			<div className="tec-events-onboarding__checkbox-label">
				<label
					id={"tec-events-onboarding__checkbox-label--" + view}
					htmlFor={"tec-events-onboarding__checkbox-input-" + view}
				>
					{icons.get(view)}
					{view}
				</label>
			</div>
		</div>
	);
};

export default ViewCheckbox;
