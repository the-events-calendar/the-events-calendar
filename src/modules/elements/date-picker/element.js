/**
 * External dependencies
 */
import moment from 'moment';
import { noop } from 'lodash';
import { PropTypes } from 'prop-types';

/**
 * WordPress dependencies
 */
import { Component } from '@wordpress/element';
import {
	DatePicker as WPDatePicker,
	Dropdown,
} from '@wordpress/components';

/**
 * Internal dependencies
 */
import { moment as momentUtil } from '@moderntribe/common/utils';
import './style.pcss';

const { toMoment, toDate, toDatePicker, toDateTime } = momentUtil;

export default class DatePicker extends Component {
	static propTypes = {
		changeDatetime: PropTypes.func,
		datetime: PropTypes.string,
	};

	static defaultProps = {
		changeDatetime: noop,
		datetime: toDatePicker(),
	};

	static getDerivedStateFromProps( nextProps, prevState ) {
		const { datetime } = nextProps;
		if ( datetime === prevState.datetime ) {
			return null;
		}

		return {
			datetime: toDatePicker( toMoment( datetime ) ),
		};
	}

	constructor( props ) {
		super( ...arguments );

		this.changeDatetime = props.changeDatetime.bind( this );

		this.state = {
			...props,
			datetime: toDatePicker( toMoment( props.datetime ) ),
		};
	}

	normalize = ( date = moment() ) => {
		// Convert to moment
		const current = moment( date );
		return current.isValid() ? current : moment();
	};

	renderContent = ( { onToggle, isOpen, onClose } ) => {
		this.onClose = onClose.bind( this );
		const { datetime } = this.state;

		return (
			<WPDatePicker
				key="date-picker"
				currentDate={ moment( datetime ) }
				onChange={ this.onChange }
			/>
		);
	};

	renderToggle = ( { onToggle, isOpen, onClose } ) => {
		const { datetime } = this.state;

		return (
			<button
				type="button"
				className="button-link"
				onClick={ onToggle }
				aria-expanded={ isOpen }
			>
				{ toDate( this.normalize( datetime ) ) }
			</button>
		);
	};

	onChange = ( date ) => {
		this.changeDatetime( toDateTime( this.normalize( date ) ) );
		this.onClose();
	};

	render() {
		return (
			<Dropdown
				className="tribe-editor__datepicker"
				position="bottom left"
				contentClassName="tribe-editor__datepicker-dialog"
				renderToggle={ this.renderToggle }
				renderContent={ this.renderContent }
			/>
		);
	}
}
