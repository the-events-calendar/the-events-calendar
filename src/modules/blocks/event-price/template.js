/**
 * External dependencies
 */
import React, { Fragment } from 'react';
import PropTypes from 'prop-types';
import classNames from 'classnames';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import {
	CheckboxControl,
	TextControl,
	PanelBody,
} from '@wordpress/components';

/**
 * Internal dependencies
 */
import { Dashboard } from '@moderntribe/events/elements';
import { range } from '@moderntribe/common/utils';
import './style.pcss';
import { wpEditor } from '@moderntribe/common/utils/globals';
const { InspectorControls } = wpEditor;

/**
 * Module Code
 */

const renderCurrency = ( { showCurrencySymbol, currencySymbol } ) => (
	showCurrencySymbol && (
		<span className="tribe-editor__event-price__currency">
			{ currencySymbol }
		</span>
	)
);

const renderPlaceholder = ( { showCost, currencySymbol, currencyPosition } ) => {
	let placeholder = __( 'Add Price', 'the-events-calendar' );

	placeholder = ( 'prefix' === currencyPosition )
		? currencySymbol + ' ' + placeholder
		: placeholder + ' ' + currencySymbol;

	return ! showCost && (
		<span className="tribe-editor__event-price__label">{ placeholder }</span>
	);
};

const renderCost = ( { showCost, isFree, cost } ) => {
	const parsed = range.parser( cost );

	let value = parsed;

	if ( isFree ) {
		value = __( 'Free', 'the-events-calendar' );
	}

	return showCost && (
		<span className="tribe-editor__event-price__cost">{ value }</span>
	);
};

const renderDescription = ( { showCostDescription, attributes } ) => (
	showCostDescription && (
		<span className="tribe-editor__event-price__description">{ attributes.costDescription }</span>
	)
);

const renderLabel = ( props ) => {
	const { currencyPosition, open } = props;
	const containerClass = classNames(
		'tribe-editor__event-price__price',
		`tribe-editor__event-price__price--${ currencyPosition }`,
	);

	/**
	 * @todo: Change div to button.
	 */
	return (
		<div // eslint-disable-line
			className={ containerClass }
			onClick={ open }
		>
			{ renderCurrency( props ) }
			{ renderPlaceholder( props ) }
			{ renderCost( props ) }
			{ renderDescription( props ) }
		</div>
	);
};

const renderDashboard = ( {
	isOpen,
	cost,
	setCost,
	attributes,
	setAttributes,
} ) => {
	const setDescription = event => setAttributes( { costDescription: event.target.value } );

	return (
		<Dashboard isOpen={ isOpen }>
			<Fragment>
				<section className="tribe-editor__event-price__dashboard">
					<input
						className={ classNames(
							'tribe-editor__event-price__input',
							'tribe-editor__event-price__input--price',
						) }
						name="description"
						type="text"
						placeholder={ __( 'Fixed Price or Range', 'the-events-calendar' ) }
						onChange={ setCost }
						value={ cost }
					/>
					<input
						className={ classNames(
							'tribe-editor__event-price__input',
							'tribe-editor__event-price__input--description',
						) }
						name="description"
						type="text"
						placeholder={ __( 'Description', 'the-events-calendar' ) }
						onChange={ setDescription }
						value={ attributes.costDescription }
					/>
				</section>
				<footer className="tribe-editor__event-price__dashboard__footer">
					{ __( 'Enter 0 as price for free events', 'the-events-calendar' ) }
				</footer>
			</Fragment>
		</Dashboard>
	);
};

const renderUI = ( props ) => (
	<section key="event-price-box" className="tribe-editor__block">
		<div className="tribe-editor__event-price">
			{ renderLabel( props ) }
			{ renderDashboard( props ) }
		</div>
	</section>
);

const renderControls = ( {
	isSelected,
	currencySymbol,
	currencyCode,
	currencyPosition,
	setCurrencyPosition,
	setCode,
	setSymbol,
} ) => (
	isSelected && (
		<InspectorControls key="inspector">
			<PanelBody title={ __( 'Price Settings', 'the-events-calendar' ) }>
				<TextControl
					className="tribe-editor__event-price__currency-symbol-setting"
					label={ __( ' Currency Symbol', 'the-events-calendar' ) }
					value={ currencySymbol }
					placeholder={ __( 'E.g.: $', 'the-events-calendar' ) }
					onChange={ setSymbol }
				/>
				<TextControl
					className="tribe-editor__event-price__currency-code-setting"
					label={ __( ' Currency Code', 'the-events-calendar' ) }
					value={ currencyCode }
					placeholder={ __( 'E.g.: USD', 'the-events-calendar' ) }
					onChange={ setCode }
				/>
				<CheckboxControl
					label={ __( 'Currency symbol follows price', 'the-events-calendar' ) }
					checked={ 'suffix' === currencyPosition }
					onChange={ setCurrencyPosition }
				/>
			</PanelBody>
		</InspectorControls>
	)
);

const EventPrice = ( props ) => ( [
	renderUI( props ),
	renderControls( props ),
] );

EventPrice.propTypes = {
	isOpen: PropTypes.bool,
	cost: PropTypes.string,
	currencyPosition: PropTypes.oneOf( [ 'prefix', 'suffix', '' ] ),
	currencySymbol: PropTypes.string,
	currencyCode: PropTypes.string,
	showCurrencySymbol: PropTypes.bool,
	showCost: PropTypes.bool,
	showCostDescription: PropTypes.bool,
	isFree: PropTypes.bool,
	setCost: PropTypes.func,
	setSymbol: PropTypes.func,
	setCode: PropTypes.func,
	setCurrencyPosition: PropTypes.func,
	onKeyDown: PropTypes.func,
	onClick: PropTypes.func,
	open: PropTypes.func,
	attributes: PropTypes.object,
	setAttributes: PropTypes.func,
};

export default EventPrice;
