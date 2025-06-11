/**
 * External dependencies
 */
import React from 'react';
import { omit, noop } from 'lodash';
import { DayPicker, addToRange } from 'react-day-picker';
import PropTypes from 'prop-types';
import classNames from 'classnames';
import moment from 'moment';

/**
 * WordPress dependencies
 */
import { Component } from '@wordpress/element';

/**
 * Internal dependencies
 */
import './style.pcss';

const today = new Date();
const currentYear = today.getFullYear();
const currentMonth = today.getMonth();
const yearsBack = 5;
const fromMonth = new Date( currentYear - yearsBack, currentMonth );
const toMonth = new Date( currentYear + 10, 11 );
const getDatesBetween = ( startDate, endDate ) => {
	// Remove the time part by setting to midnight
	const start = new Date( startDate );
	start.setHours( 0, 0, 0, 0 );
	const end = new Date( endDate );
	end.setHours( 0, 0, 0, 0 );

	const currentDate = new Date( start );
	currentDate.setDate( currentDate.getDate() + 1 ); // Start from the day after startDate
	const dates = [];

	while ( currentDate < end ) {
		dates.push( new Date( currentDate ) );
		currentDate.setDate( currentDate.getDate() + 1 );
	}

	return dates;
};

export default class Month extends Component {
	static propTypes = {
		withRange: PropTypes.bool,
		onSelect: PropTypes.func,
		from: PropTypes.instanceOf( Date ),
		to: PropTypes.instanceOf( Date ),
		month: PropTypes.instanceOf( Date ),
		setVisibleMonth: PropTypes.func,
	};

	static defaultProps = {
		onSelect: noop,
		from: today,
		to: undefined,
		month: fromMonth,
		setVisibleMonth: noop,
	};

	constructor() {
		super( ...arguments );

		this.state = {
			toMonth,
			from: null,
			to: null,
		};
	}

	selectDay = ( day ) => {
		const { withRange } = this.props;
		let range = {};

		if ( withRange ) {
			range = addToRange( day, this.state );

			// if the range was unselected we fallback to the first available day
			if ( range.from === null && range.to === null ) {
				range.from = today;
				range.to = undefined;
			}

			if ( range.to && moment( range.to ).isSame( range.from ) ) {
				range.to = undefined;
			}
		} else {
			range.from = day;
			range.to = undefined;
		}

		this.setState( this.maybeUpdate( range ), () => {
			this.onSelectCallback();
		} );
	};

	maybeUpdate = ( range ) => ( state ) => {
		if ( state.from === range.from && state.to === range.to ) {
			return null;
		}
		return range;
	};

	onSelectCallback = () => {
		const { onSelect } = this.props;
		onSelect( omit( this.state, [ 'withRange' ] ) );
	};

	getSelectedDays = () => {
		const { withRange, from, to } = this.props;
		if ( withRange ) {
			return { from, to };
		}
		return from;
	};

	render() {
		const { from, to, month, withRange, setVisibleMonth } = this.props;
		const containerClass = classNames( { 'tribe-editor__calendars--range': withRange } );
		const modifiers = {
			selected: this.getSelectedDays(),
		};

		if ( withRange && from && to ) {
			modifiers.range_start = from;
			modifiers.range_middle = getDatesBetween( from, to );
			modifiers.range_end = to;
		}

		return (
			<DayPicker
				mode={ withRange ? 'range' : 'single' }
				className={ containerClass }
				startMonth={ fromMonth }
				endMonth={ this.state.toMonth }
				month={ month }
				numberOfMonths={ 2 }
				modifiers={ modifiers }
				onDayClick={ this.selectDay }
				onMonthChange={ setVisibleMonth }
				captionLayout="dropdown"
			/>
		);
	}
}
