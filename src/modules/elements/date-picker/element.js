/**
 * External dependencies
 */
import React from 'react';
import { noop } from 'lodash';
import { PropTypes } from 'prop-types';
import { isValid } from 'date-fns';

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
			datetime: toDatePicker( momentUtil.construct( datetime ) ),
		};
	}

	constructor( props ) {
		super( ...arguments );

		this.changeDatetime = props.changeDatetime.bind( this );

		this.state = {
			...props,
			datetime: toDatePicker( momentUtil.construct( props.datetime ) ),
		};
	}

	normalize = ( date = momentUtil.construct() ) => {
		const current = momentUtil.construct( date );
		return isValid( current ) ? current : momentUtil.construct();
	};

	renderContent = ( { onClose } ) => {
		this.onClose = onClose.bind( this );
		const { datetime } = this.state;

		return (
			<WPDatePicker
				key="date-picker"
				currentDate={ momentUtil.construct( datetime ) }
				onChange={ this.onChange }
			/>
		);
	};

	renderToggle = ( { onToggle, isOpen } ) => {
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
