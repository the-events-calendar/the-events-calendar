/**
 * Import external dependencies
 */
import { omit, noop } from 'lodash';
import DayPicker, { DateUtils } from 'react-day-picker';
import PropTypes from 'prop-types';
import classNames from 'classnames';
import moment from 'moment/moment';

/**
 * Wordpress dependencies
 */
import { Component } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { YearMonthForm } from '@moderntribe/events/elements';
import './style.pcss';

const today = new Date();
const currentYear = today.getFullYear();
const currentMonth = today.getMonth();
const yearsBack = 5;
const fromMonth = new Date( currentYear - yearsBack, currentMonth );
const toMonth = new Date( currentYear + 10, 11 );

export default class Month extends Component {
	static propTypes = {
		withRange: PropTypes.bool,
		onSelectDay: PropTypes.func,
		from: PropTypes.instanceOf( Date ),
		to: PropTypes.instanceOf( Date ),
		month: PropTypes.instanceOf( Date ),
		setVisibleMonth: PropTypes.func,
	};

	static defaultProps = {
		onSelectDay: noop,
		from: today,
		to: undefined,
		month: fromMonth,
		setVisibleMonth: noop,
	};

	constructor() {
		super( ...arguments );

		this.state = {
			toMonth: toMonth,
			from: null,
			to: null,
		};
	}

	selectDay = ( day ) => {
		const { withRange } = this.props;
		let range = {};

		if ( withRange ) {
			range = DateUtils.addDayToRange( day, this.state );

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
		const { onSelectDay } = this.props;
		onSelectDay( omit( this.state, [ 'withRange' ] ) );
	};

	getSelectedDays = () => {
		const { withRange, from, to } = this.props;
		if ( withRange ) {
			return [ from, { from, to }];
		}
		return from;
	};

	getCaptionElement = ( { date, localeUtils } ) => {
		const { month, setVisibleMonth } = this.props;

		if ( date.getMonth() !== month.getMonth() ) {
			return this.renderCaption( date, localeUtils );
		}

		return (
			<YearMonthForm
				today={ today }
				date={ date }
				localeUtils={ localeUtils }
				onChange={ setVisibleMonth }
			/>
		);
	};

	renderCaption = ( date, localeUtils ) => (
		<div className={ 'tribe-editor__daypicker-caption' } role="heading">
			<div>
				{ localeUtils.formatMonthTitle( date ) }
			</div>
		</div>
	);

	render() {
		const { from, to, month, withRange, setVisibleMonth } = this.props;
		const modifiers = withRange ? { start: from, end: to } : {};
		const containerClass = classNames( { 'tribe-editor__calendars--range': withRange } );
		return (
			<DayPicker
				className={ containerClass }
				fromMonth={ fromMonth }
				toMonth={ this.state.toMonth }
				month={ month }
				numberOfMonths={ 2 }
				modifiers={ modifiers }
				selectedDays={ this.getSelectedDays() }
				onDayClick={ this.selectDay }
				onMonthChange={ setVisibleMonth }
				captionElement={ this.getCaptionElement }
			/>
		);
	}
}
