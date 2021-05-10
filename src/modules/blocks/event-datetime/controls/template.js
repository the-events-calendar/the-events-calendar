/**
 * External dependencies
 */
import React from 'react';
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import {
	PanelBody,
	SelectControl,
	TextControl,
	ToggleControl,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import {
	date,
} from '@moderntribe/common/utils';
import { wpEditor } from '@moderntribe/common/utils/globals';
const { InspectorControls } = wpEditor;

/**
 * Module Code
 */

const { timezonesAsSelectData } = date;

const EventDateTimeControls = ( props ) => {
	const {
		attributes,
		separatorDate,
		separatorTime,
		setSeparatorDate,
		setSeparatorTime,
		setTimeZone,
		setAttributes,
		timeZone,
		isEditable,
	} = props;

	const setShowTimeZone = value => setAttributes( { showTimeZone: value } );

	return isEditable && (
		<InspectorControls key="inspector">
			<PanelBody title={ __( 'Date Time Settings', 'the-events-calendar' ) }>
				<TextControl
					label={ __( 'Date Time Separator', 'the-events-calendar' ) }
					value={ separatorDate }
					onChange={ setSeparatorDate }
					className="tribe-editor__date-time__date-time-separator-setting"
					maxLength="2"
				/>
				<TextControl
					label={ __( 'Time Range Separator', 'the-events-calendar' ) }
					value={ separatorTime }
					onChange={ setSeparatorTime }
					className="tribe-editor__date-time__time-range-separator-setting"
					maxLength="2"
				/>
				<SelectControl
					label={ __( 'Time Zone', 'the-events-calendar' ) }
					value={ timeZone }
					onChange={ setTimeZone }
					options={ timezonesAsSelectData() }
					className="tribe-editor__date-time__time-zone-setting"
				/>
				<ToggleControl
					label={ __( 'Show Time Zone', 'the-events-calendar' ) }
					checked={ attributes.showTimeZone }
					onChange={ setShowTimeZone }
				/>
			</PanelBody>
		</InspectorControls>
	);
};

EventDateTimeControls.propTypes = {
	attributes: PropTypes.object,
	isEditable: PropTypes.bool.isRequired,
	onTimeZoneVisibilityChange: PropTypes.func,
	separatorDate: PropTypes.string,
	separatorTime: PropTypes.string,
	setSeparatorDate: PropTypes.func,
	setSeparatorTime: PropTypes.func,
	setTimeZone: PropTypes.func,
	setAttributes: PropTypes.func,
	timeZone: PropTypes.string,
};

export default EventDateTimeControls;
