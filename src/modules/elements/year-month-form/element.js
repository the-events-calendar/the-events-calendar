/**
 * External dependencies
 */
import React from 'react';
import { PropTypes } from 'prop-types';

/**
 * Internal dependencies
 */
import './style.pcss';

const YearMonthForm = ( { today, date, localeUtils, onChange } ) => {
	const currentYear = today.getFullYear();
	const currentMonth = today.getMonth();
	const toMonth = new Date( currentYear + 10, 11 );
	const months = localeUtils.getMonths();
	const years = [];
	const yearsBack = 5;

	for ( let i = currentYear - yearsBack; i <= toMonth.getFullYear(); i++ ) {
		years.push( i );
	}

	const handleChange = ( e ) => {
		const { year, month } = e.target.form;
		onChange( new Date( year.value, month.value ) );
	};

	/**
	 * @todo: figure out what to do about onChange event (accessibility).
	 */
	return (
		<form className="tribe-editor__year-month-form">
			<select // eslint-disable-line
				className="tribe-editor__year-month-form__month"
				name="month"
				onChange={ handleChange }
				value={ date.getMonth() }
			>
				{ months.map( ( month, monthNum ) => {
					if ( date.getFullYear() === currentYear - yearsBack && monthNum < currentMonth ) {
						return (
							<option key={ month } value={ monthNum } disabled>
								{ month }
							</option>
						);
					}

					return (
						<option key={ month } value={ monthNum }>
							{ month }
						</option>
					);
				} ) }
			</select>
			<select // eslint-disable-line
				className="tribe-editor__year-month-form__year"
				name="year"
				onChange={ handleChange }
				value={ date.getFullYear() }
			>
				{ years.map( year => {
					if ( date.getMonth() < currentMonth && year === currentYear - yearsBack ) {
						return (
							<option key={ year } value={ year } disabled>
								{ year }
							</option>
						);
					}

					return (
						<option key={ year } value={ year }>
							{ year }
						</option>
					);
				} ) }
			</select>
		</form>
	);
};

YearMonthForm.propTypes = {
	today: PropTypes.instanceOf( Date ),
	date: PropTypes.instanceOf( Date ),
	localeUtils: PropTypes.object,
	onChange: PropTypes.func,
};

export default YearMonthForm;
