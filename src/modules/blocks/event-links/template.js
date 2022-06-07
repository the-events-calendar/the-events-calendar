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
import { CaretDown as CaretDownIcon, Link as LinkIcon } from '@moderntribe/events/icons';
import { wpEditor } from '@moderntribe/common/utils/globals';
import './style.pcss';
const { InspectorControls } = wpEditor;

/**
 * Module Code
 */

const addToCalendarPlaceholder = __( 'Add to Calendar', 'the-events-calendar' );
const googleCalendarPlaceholder = __( 'Google Calendar', 'the-events-calendar' );
const iCalExportPlaceholder = __( 'iCalendar', 'the-events-calendar' );
const outlook365Placeholder = __( 'Outlook 365', 'the-events-calendar' );
const outlookLivePlaceholder = __( 'Outlook Live', 'the-events-calendar' );

const renderPlaceholder = ( label ) => (
	<button className="tribe-editor__btn--link tribe-editor__btn--placeholder" disabled>
		<LinkIcon />
		{ label }
	</button>
);

const renderGoogleCalendarDropdown = ( {
	attributes,
	setGoogleCalendarLabel,
} ) => {
	const {
		hasiCal,
		hasGoogleCalendar,
		hasOutlook365,
		hasOutlookLive,
		googleCalendarLabel,
	} = attributes;

	if ( ! hasGoogleCalendar && ! hasiCal && ! hasOutlook365 && ! hasOutlookLive ) {
		return renderPlaceholder( googleCalendarPlaceholder );
	}

	return hasGoogleCalendar && (
		<li className="tribe-events-c-subscribe-dropdown__list-item">
			<AutosizeInput
				id="tribe-event-gcal"
				name="google-calendar-label"
				className="tribe-editor__btn-input"
				value={ googleCalendarLabel }
				placeholder={ googleCalendarPlaceholder }
				onChange={ setGoogleCalendarLabel }
			/>
		</li>
	);
};

const renderiCalDropdown = ( {
	attributes,
	setiCalLabel,
} ) => {
	const { hasiCal, hasGoogleCalendar, hasOutlook365, hasOutlookLive, iCalLabel } = attributes;

	if ( ! hasGoogleCalendar && ! hasiCal && ! hasOutlook365 && ! hasOutlookLive ) {
		return renderPlaceholder( iCalExportPlaceholder );
	}

	return hasiCal && (
		<li className="tribe-events-c-subscribe-dropdown__list-item">
			<AutosizeInput
				id="tribe-event-ical"
				name="tribe-event-ical"
				className="tribe-editor__btn-input"
				value={ iCalLabel }
				placeholder={ iCalExportPlaceholder }
				onChange={ setiCalLabel }
			/>
		</li>
	);
};

const renderOutlook365Dropdown = ( {
	attributes,
	setOutlook365Label,
} ) => {
	const { hasiCal, hasGoogleCalendar, hasOutlook365, hasOutlookLive, outlook365Label } = attributes;

	if ( ! hasGoogleCalendar && ! hasiCal && ! hasOutlook365 && ! hasOutlookLive ) {
		return renderPlaceholder( outlook365Placeholder );
	}

	return hasOutlook365 && (
		<li className="tribe-events-c-subscribe-dropdown__list-item">
			<AutosizeInput
				id="tribe-event-outlook-365"
				name="tribe-event-outlook-365"
				className="tribe-editor__btn-input"
				value={ outlook365Label }
				placeholder={ outlook365Placeholder }
				onChange={ setOutlook365Label }
			/>
		</li>
	);
};

const renderOutlookLiveDropdown = ( {
	attributes,
	setOutlookLiveLabel,
} ) => {
	const {
		hasiCal,
		hasGoogleCalendar,
		hasOutlook365,
		hasOutlookLive,
		outlookLiveLabel,
	} = attributes;

	if ( ! hasGoogleCalendar && ! hasiCal && ! hasOutlook365 && ! hasOutlookLive ) {
		return renderPlaceholder( outlookLivePlaceholder );
	}

	return hasOutlookLive && (
		<li className="tribe-events-c-subscribe-dropdown__list-item">
			<AutosizeInput
				id="tribe-event-outlook-live"
				name="tribe-event-outlook-live"
				className="tribe-editor__btn-input"
				value={ outlookLiveLabel }
				placeholder={ outlookLivePlaceholder }
				onChange={ setOutlookLiveLabel }
			/>
		</li>
	);
};

const renderButtons = ( props ) => (
	<div key="event-links" className="tribe-editor__block tribe-editor__events-link">
		<div className="tribe-events tribe-common">
			<div className="tribe-events-c-subscribe-dropdown__container">
				<div className="tribe-events-c-subscribe-dropdown">
					<div className="tribe-common-c-btn-border tribe-events-c-subscribe-dropdown__button">
						<LinkIcon />
						<button className="tribe-events-c-subscribe-dropdown__button-text">
							{ addToCalendarPlaceholder }
						</button>
						<CaretDownIcon />
					</div>
					<div className="tribe-events-c-subscribe-dropdown__content">
						<ul className="tribe-events-c-subscribe-dropdown__list">
							{ renderGoogleCalendarDropdown( props ) }
							{ renderiCalDropdown( props ) }
							{ renderOutlook365Dropdown( props ) }
							{ renderOutlookLiveDropdown( props ) }
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>
);

const renderControls = ( {
	attributes,
	isSelected,
	toggleIcalLabel,
	toggleGoogleCalendar,
	toggleOutlook365Label,
	toggleOutlookLiveLabel,
} ) => {
	const { hasGoogleCalendar, hasiCal, hasOutlook365, hasOutlookLive } = attributes;

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
					<ToggleControl
						label={ __( 'Outlook 365', 'the-events-calendar' ) }
						checked={ hasOutlook365 }
						onChange={ toggleOutlook365Label }
					/>
					<ToggleControl
						label={ __( 'Outlook Live', 'the-events-calendar' ) }
						checked={ hasOutlookLive }
						onChange={ toggleOutlookLiveLabel }
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
	const setOutlook365Label = e => setAttributes( { outlook365Label: e.target.value } );
	const setOutlookLiveLabel = e => setAttributes( { outlookLiveLabel: e.target.value } );
	const toggleIcalLabel = value => setAttributes( { hasiCal: value } );
	const toggleGoogleCalendar = value => setAttributes( { hasGoogleCalendar: value } );
	const toggleOutlook365Label = value => setAttributes( { hasOutlook365: value } );
	const toggleOutlookLiveLabel = value => setAttributes( { hasOutlookLive: value } );

	const combinedProps = {
		...props,
		setiCalLabel,
		setGoogleCalendarLabel,
		setOutlook365Label,
		setOutlookLiveLabel,
		toggleIcalLabel,
		toggleGoogleCalendar,
		toggleOutlook365Label,
		toggleOutlookLiveLabel,
	};

	return [
		renderButtons( combinedProps ),
		renderControls( combinedProps ),
	];
};

EventLinks.propTypes = {
	hasGoogleCalendar: PropTypes.bool,
	hasiCal: PropTypes.bool,
	hasOutlook365: PropTypes.bool,
	hasOutlookLive: PropTypes.bool,
	isSelected: PropTypes.bool,
	googleCalendarLabel: PropTypes.string,
	iCalLabel: PropTypes.string,
	Outlook365Label: PropTypes.string,
	OutlookLiveLabel: PropTypes.string,
	setiCalLabel: PropTypes.func,
	setGoogleCalendarLabel: PropTypes.func,
	setOutlookLiveLabel: PropTypes.func,
	setOutlook365Label: PropTypes.func,
	toggleIcalLabel: PropTypes.func,
	toggleGoogleCalendar: PropTypes.func,
	toggleOutlook365Label: PropTypes.func,
	toggleOutlookLiveLabel: PropTypes.func,
};

export default EventLinks;
