/**
 * External dependencies
 */
import React from 'react';
import PropTypes from 'prop-types';
import classNames from 'classnames';
import AutosizeInput from 'react-input-autosize';

/**
 * WordPress dependencies
 */
import { Dashicon } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { wpEditor } from '@moderntribe/common/utils/globals';
import './style.pcss';
const { URLInput } = wpEditor;

/**
 * Module Code
 */

const placeholder = __( 'Add Event Website', 'the-events-calendar' );

const renderUrlInput = ( { isSelected, url, setWebsite } ) => (
	isSelected && (
		<div key="tribe-events-website-url" className="tribe-editor__event-website__url">
			<Dashicon icon="admin-links" />
			<URLInput
				autoFocus={ false }
				value={ url }
				onChange={ setWebsite }
			/>
		</div>
	)
);

const renderLabelInput = ( { isSelected, attributes, setAttributes } ) => {
	const setLabel = event => setAttributes( { urlLabel: event.target.value } );
	const isEmpty = attributes.urlLabel.trim() === '';

	const containerClassNames = classNames( {
		'tribe-editor__event-website__label': true,
		'tribe-editor__event-website__label--selected': isSelected,
	} );

	const inputClassNames = classNames( {
		'tribe-editor__event-website__label-text': true,
		'tribe-editor__event-website__label-text--empty': isEmpty && isSelected,
	} );

	return (
		<div
			key="tribe-events-website-label"
			className={ containerClassNames }
		>
			<AutosizeInput
				id="tribe-events-website-link"
				className={ inputClassNames }
				value={ attributes.urlLabel }
				placeholder={ placeholder }
				onChange={ setLabel }
			/>
		</div>
	);
};

const renderPlaceholder = () => {
	const classes = [
		'tribe-editor__event-website__label',
		'tribe-editor__event-website__label--placeholder',
	];

	return (
		<button className={ classNames( classes ) }>
			{ placeholder }
		</button>
	);
};

const EventWebsite = ( props ) => {
	const { isSelected, attributes } = props;
	const eventWebsite = ( ! isSelected && ! attributes.urlLabel )
		? renderPlaceholder()
		: [ renderLabelInput( props ), renderUrlInput( props ) ];

	return (
		<div className="tribe-editor__block tribe-editor__event-website">
			{ eventWebsite }
		</div>
	);
};

EventWebsite.propTypes = {
	isSelected: PropTypes.bool,
	url: PropTypes.string,
	setWebsite: PropTypes.func,
	attributes: PropTypes.object,
	setAttributes: PropTypes.func,
};

export default EventWebsite;
