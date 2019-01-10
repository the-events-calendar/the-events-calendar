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
import { InspectorControls } from '@wordpress/editor';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { Link as LinkIcon } from '@moderntribe/events/icons';
import { input } from '@moderntribe/common/utils';
import './style.pcss';

/**
 * Module Code
 */

const googleCalendarPlaceholder = __( 'Google Calendar', 'the-events-calendar' );
const iCalExportPlaceholder = __( 'iCal Export', 'the-events-calendar' );

const renderPlaceholder= ( label ) => (
	<button className="tribe-editor__btn--link tribe-editor__btn--placeholder" disabled>
		<LinkIcon />
		{ label }
	</button>
);

const renderGoogleCalendar = ({
	hasiCal,
	hasGoogleCalendar,
	googleCalendarLabel,
	setGoogleCalendarLabel
}) => {
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
				onChange={ input.sendValue( setGoogleCalendarLabel ) }
			/>
		</div>
	);
};

const renderiCal = ({
	hasiCal,
	hasGoogleCalendar,
	iCalLabel,
	setiCalLabel
}) => {
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
				onChange={ input.sendValue( setiCalLabel ) }
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

const renderControls = ({
	hasGoogleCalendar,
	hasiCal,
	isSelected,
	toggleIcalLabel,
	toggleGoogleCalendar,
}) => (
	isSelected && (
		<InspectorControls key="inspector">
			<PanelBody title={ __( 'Share Settings', 'the-events-calendar' ) }>
				<ToggleControl
					label={ __( 'Google Calendar', 'the-events-calendar' ) }
					checked={ hasGoogleCalendar }
					onChange={ toggleGoogleCalendar }
				/>
				<ToggleControl
					label={ __( 'iCal', 'the-events-calendar' ) }
					checked={ hasiCal }
					onChange={ toggleIcalLabel }
				/>
			</PanelBody>
		</InspectorControls>
	)
);

const EventLinks = ( props ) => (
	[
		renderButtons( props ),
		renderControls( props ),
	]
);

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
