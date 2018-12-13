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
import { PlainText, InspectorControls } from '@wordpress/editor';

/**
 * Internal dependencies
 */
import {
	date,
	input,
	moment as momentUtil,
} from '@moderntribe/common/utils';
import { TermsList, MetaGroup } from '@moderntribe/events/elements';
import EventDetailsOrganizers from './event-details-organizers/container';

/**
 * Module Code
 */

const { toMoment, toDate, toTime } = momentUtil;

const ClassicEventDetails = ( props ) => {

	const renderTitle = () => {
		const { detailsTitle, setDetailsTitle } = props;

		return (
			<AutosizeInput
				className="tribe-editor__events-section__headline trigger-dashboard-datetime"
				value={ detailsTitle }
				placeholder={ __( 'Details', 'the-events-calendar' ) }
				onChange={ input.sendValue( setDetailsTitle ) }
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
		organizerTitle,
		setOrganizerTitle,
		isSelected,
		allDay,
		setAllDay,
		currencyPosition,
		togglePosition,
		currencySymbol,
		setSymbol,
	} = props;

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
						value={ organizerTitle }
						placeholder={ __( 'Organizer', 'the-events-calendar' ) }
						onChange={ input.sendValue( setOrganizerTitle ) }
					/>
					<EventDetailsOrganizers />
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
						onChange={ togglePosition }
					/>
					<TextControl
						label={ __( ' Currency Symbol', 'the-events-calendar' ) }
						value={ currencySymbol }
						placeholder={ __( 'E.g.: $', 'the-events-calendar' ) }
						onChange={ setSymbol }
					/>
				</PanelBody>
			</InspectorControls>
		),
	];
};

ClassicEventDetails.propTypes = {
	organizerTitle: PropTypes.string,
	url: PropTypes.string,
	start: PropTypes.string,
	end: PropTypes.string,
	separatorDate: PropTypes.string,
	cost: PropTypes.string,
	currencyPosition: PropTypes.string,
	currencySymbol: PropTypes.string,
	detailsTitle: PropTypes.string,
	allDay: PropTypes.bool,
	isSelected: PropTypes.bool,
	setOrganizerTitle: PropTypes.func,
	setDetailsTitle: PropTypes.func,
	setWebsite: PropTypes.func,
	setCost: PropTypes.func,
	toggleDashboardDateTime: PropTypes.func,
	setSymbol: PropTypes.func,
	togglePosition: PropTypes.func,
	setAllDay: PropTypes.func,
};

export default ClassicEventDetails;
