/**
 * External dependencies
 */
import React, { PureComponent } from 'react';
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
import { InspectorControls } from '@wordpress/editor';

/**
 * Internal dependencies
 */
import {
	date,
} from '@moderntribe/common/utils';

/**
 * Module Code
 */

const { timezonesAsSelectData } = date;

class EventDateTimeControls extends PureComponent {
	static propTypes = {
		isEditable: PropTypes.bool.isRequired,
		onTimeZoneVisibilityChange: PropTypes.func,
		separatorDate: PropTypes.string,
		separatorTime: PropTypes.string,
		setSeparatorDate: PropTypes.func,
		setSeparatorTime: PropTypes.func,
		setTimeZone: PropTypes.func,
		showTimeZone: PropTypes.bool,
		timeZone: PropTypes.string,
	};

	render() {
		const {
			onTimeZoneVisibilityChange,
			separatorDate,
			separatorTime,
			setSeparatorDate,
			setSeparatorTime,
			setTimeZone,
			showTimeZone,
			timeZone,
			isEditable,
		} = this.props;

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
						checked={ showTimeZone }
						onChange={ onTimeZoneVisibilityChange }
					/>
				</PanelBody>
			</InspectorControls>
		)
	}
}

export default EventDateTimeControls;
