/**
 * External dependencies
 */
import React, { Fragment } from 'react';
import PropTypes from 'prop-types';
import classNames from 'classnames';
import AutosizeInput from 'react-input-autosize';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { ToggleControl, TextControl, PanelBody } from '@wordpress/components';

/**
 * Internal dependencies
 */
import { date, moment as momentUtil } from '@moderntribe/common/utils';
import { wpEditor } from '@moderntribe/common/utils/globals';
import { TermsList, MetaGroup } from '@moderntribe/events/elements';
import EventDetailsOrganizers from './event-details-organizers/container';
const { PlainText, InspectorControls } = wpEditor;

/**
 * Module Code
 */

const { toMoment, toDate, toTime } = momentUtil;

const ClassicEventDetails = ( props ) => {
	const renderTitle = () => {
		const { attributes, setAttributes } = props;
		const setDetailsTitle = ( e ) => setAttributes( { detailsTitle: e.target.value } );

		return (
			<AutosizeInput
				className="tribe-editor__events-section__headline trigger-dashboard-datetime"
				value={ attributes.detailsTitle }
				placeholder={ __( 'Details', 'the-events-calendar' ) }
				onChange={ setDetailsTitle }
			/>
		);
	};

	const renderStart = () => {
		const { start, allDay, toggleDashboardDateTime, separatorDate } = props;

		return (
			<div>
				<button
					className="tribe-editor__btn--label trigger-dashboard-datetime"
					onClick={ toggleDashboardDateTime }
				>
					<strong>{ __( 'Start: ', 'the-events-calendar' ) }</strong>
					{ toDate( toMoment( start ), date.FORMATS.WP.date ) }
					{ ! allDay && (
						<Fragment>
							<span>{ ' '.concat( separatorDate, ' ' ) }</span>
							<span>{ toTime( toMoment( start ), date.FORMATS.WP.time ) }</span>
						</Fragment>
					) }
				</button>
			</div>
		);
	};

	const renderEnd = () => {
		const { end, allDay, toggleDashboardDateTime, separatorDate } = props;

		return (
			<div>
				<button
					className="tribe-editor__btn--label trigger-dashboard-datetime"
					onClick={ toggleDashboardDateTime }
				>
					<strong>{ __( 'End: ', 'the-events-calendar' ) }</strong>
					{ toDate( toMoment( end ), date.FORMATS.WP.date ) }
					{ ! allDay && (
						<Fragment>
							<span>{ ' '.concat( separatorDate, ' ' ) }</span>
							<span>{ toTime( toMoment( end ), date.FORMATS.WP.time ) }</span>
						</Fragment>
					) }
				</button>
			</div>
		);
	};

	const renderWebsite = () => {
		const { url, setWebsite } = props;

		return (
			<div>
				<strong>{ __( 'Website: ', 'the-events-calendar' ) }</strong><br />
				<PlainText
					id="tribe-event-url"
					value={ url }
					placeholder={ __( 'Enter url', 'the-events-calendar' ) }
					onChange={ setWebsite }
				/>
			</div>
		);
	};

	const renderCost = () => {
		const { setCost, cost, currencyPosition, currencySymbol } = props;
		const textClassName = classNames( [
			'tribe-editor__event-cost__value',
			`tribe-editor-cost-symbol-position-${ currencyPosition }`,
		] );

		return (
			<div className="tribe-editor__event-cost">
				<strong>{ __( 'Price: ', 'the-events-calendar' ) }</strong><br />
				{ 'prefix' === currencyPosition && <span>{ currencySymbol }</span> }
				<PlainText
					className={ textClassName }
					value={ cost }
					placeholder={ __( 'Enter price', 'the-events-calendar' ) }
					onChange={ setCost }
				/>
				{ 'suffix' === currencyPosition && <span>{ currencySymbol }</span> }
			</div>
		);
	};

	const {
		attributes,
		isSelected,
		allDay,
		setAllDay,
		currencyPosition,
		setCurrencyPosition,
		currencySymbol,
		currencyCode,
		setSymbol,
		setAttributes,
	} = props;

	const setOrganizerTitle = ( e ) => setAttributes( { organizerTitle: e.target.value } );

	return [
		(
			<div
				key="event-details-box"
				className="tribe-editor__block tribe-editor__event-details"
			>
				<MetaGroup groupKey="event-details">
					{ renderTitle() }
					{ renderStart() }
					{ renderEnd() }
					{ renderWebsite() }
					{ renderCost() }
					<TermsList
						slug="tribe_events_cat"
						label={ __( 'Event Category:', 'the-events-calendar' ) }
					/>
					<TermsList
						slug="post_tag"
						label={ __( 'Event Tags:', 'the-events-calendar' ) }
					/>
				</MetaGroup>
				<MetaGroup groupKey="organizer">
					<AutosizeInput
						className="tribe-editor__events-section__headline"
						value={ attributes.organizerTitle }
						placeholder={ __( 'Organizer', 'the-events-calendar' ) }
						onChange={ setOrganizerTitle }
					/>
					<EventDetailsOrganizers setAttributes={ setAttributes } />
				</MetaGroup>
			</div>
		),
		(
			isSelected &&
			<InspectorControls key="inspector">
				<PanelBody title={ __( 'Date Time Settings', 'the-events-calendar' ) }>
					<ToggleControl
						label={ __( 'Is All Day Event', 'the-events-calendar' ) }
						checked={ allDay }
						onChange={ setAllDay }
					/>
				</PanelBody>
				<PanelBody title={ __( 'Price Settings', 'the-events-calendar' ) }>
					<ToggleControl
						label={ __( 'Show symbol before', 'the-events-calendar' ) }
						checked={ 'prefix' === currencyPosition }
						onChange={ setCurrencyPosition }
					/>
					<TextControl
						label={ __( ' Currency Symbol', 'the-events-calendar' ) }
						value={ currencySymbol }
						placeholder={ __( 'E.g.: $', 'the-events-calendar' ) }
						onChange={ setSymbol }
					/>
					<TextControl
						label={ __( ' Currency Code', 'the-events-calendar' ) }
						value={ currencyCode }
						placeholder={ __( 'E.g.: USD', 'the-events-calendar' ) }
						onChange={ setCode }
					/>
				</PanelBody>
			</InspectorControls>
		),
	];
};

ClassicEventDetails.propTypes = {
	url: PropTypes.string,
	start: PropTypes.string,
	end: PropTypes.string,
	separatorDate: PropTypes.string,
	cost: PropTypes.string,
	currencyPosition: PropTypes.string,
	currencySymbol: PropTypes.string,
	currencyCode: PropTypes.string,
	allDay: PropTypes.bool,
	isSelected: PropTypes.bool,
	setWebsite: PropTypes.func,
	setCost: PropTypes.func,
	toggleDashboardDateTime: PropTypes.func,
	setSymbol: PropTypes.func,
	setCode: PropTypes.func,
	setCurrencyPosition: PropTypes.func,
	setAllDay: PropTypes.func,
};

export default ClassicEventDetails;
