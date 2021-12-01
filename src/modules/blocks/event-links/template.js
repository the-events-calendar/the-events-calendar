/**
 * External dependencies
 */
import React from 'react';
import PropTypes from 'prop-types';
import AutosizeInput from 'react-input-autosize';

/**
 * WordPress dependencies
 */
import { PanelBody, ToggleControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { Link as LinkIcon } from '@moderntribe/events/icons';
import { wpEditor } from '@moderntribe/common/utils/globals';
import './style.pcss';
const { InspectorControls } = wpEditor;

/**
 * Module Code
 */

const googleCalendarPlaceholder = __( 'Add to Google Calendar', 'the-events-calendar' );
const iCalExportPlaceholder = __( 'Add to iCalendar', 'the-events-calendar' );

const renderPlaceholder = ( label ) => (
	<button className="tribe-editor__btn--link tribe-editor__btn--placeholder" disabled>
		<LinkIcon />
		{ label }
	</button>
);

const renderGoogleCalendar = ( {
	attributes,
	setGoogleCalendarLabel,
} ) => {
	const { hasiCal, hasGoogleCalendar, googleCalendarLabel } = attributes;

	if ( ! hasGoogleCalendar && ! hasiCal ) {
		return renderPlaceholder( googleCalendarPlaceholder );
	}

	return hasGoogleCalendar && (
		<div className="tribe-editor__btn--link tribe-events-gcal">
			<LinkIcon />
			<AutosizeInput
				name="google-calendar-label"
				className="tribe-editor__btn-input"
				value={ googleCalendarLabel }
				placeholder={ googleCalendarPlaceholder }
				onChange={ setGoogleCalendarLabel }
			/>
		</div>
	);
};

const renderiCal = ( {
	attributes,
	setiCalLabel,
} ) => {
	const { hasiCal, hasGoogleCalendar, iCalLabel } = attributes;

	if ( ! hasGoogleCalendar && ! hasiCal ) {
		return renderPlaceholder( iCalExportPlaceholder );
	}

	return hasiCal && (
		<div className="tribe-editor__btn--link tribe-events-ical">
			<LinkIcon />
			<AutosizeInput
				id="tribe-event-ical"
				name="tribe-event-ical"
				className="tribe-editor__btn-input"
				value={ iCalLabel }
				placeholder={ iCalExportPlaceholder }
				onChange={ setiCalLabel }
			/>
		</div>
	);
};

const renderButtons = ( props ) => (
	<div key="event-links" className="tribe-editor__block tribe-editor__events-link">
		{ renderGoogleCalendar( props ) }
		{ renderiCal( props ) }
	</div>
);

const renderControls = ( {
	attributes,
	isSelected,
	toggleIcalLabel,
	toggleGoogleCalendar,
} ) => {
	const { hasGoogleCalendar, hasiCal } = attributes;

	return (
		isSelected && (
			<InspectorControls key="inspector">
				<PanelBody title={ __( 'Share Settings', 'the-events-calendar' ) }>
					<ToggleControl
						label={ __( 'Google Calendar', 'the-events-calendar' ) }
						checked={ hasGoogleCalendar }
						onChange={ toggleGoogleCalendar }
					/>
					<ToggleControl
						label={ __( 'iCalendar', 'the-events-calendar' ) }
						checked={ hasiCal }
						onChange={ toggleIcalLabel }
					/>
				</PanelBody>
			</InspectorControls>
		)
	);
};

const EventLinks = ( props ) => {
	const { setAttributes } = props;

	const setiCalLabel = e => setAttributes( { iCalLabel: e.target.value } );
	const setGoogleCalendarLabel = e => setAttributes( { googleCalendarLabel: e.target.value } );
	const toggleIcalLabel = value => setAttributes( { hasiCal: value } );
	const toggleGoogleCalendar = value => setAttributes( { hasGoogleCalendar: value } );

	const combinedProps = {
		...props,
		setiCalLabel,
		setGoogleCalendarLabel,
		toggleIcalLabel,
		toggleGoogleCalendar,
	};

	return [
		renderButtons( combinedProps ),
		renderControls( combinedProps ),
	];
};

EventLinks.propTypes = {
	hasGoogleCalendar: PropTypes.bool,
	hasiCal: PropTypes.bool,
	isSelected: PropTypes.bool,
	googleCalendarLabel: PropTypes.string,
	iCalLabel: PropTypes.string,
	setiCalLabel: PropTypes.func,
	setGoogleCalendarLabel: PropTypes.func,
	toggleIcalLabel: PropTypes.func,
	toggleGoogleCalendar: PropTypes.func,
};

export default EventLinks;
