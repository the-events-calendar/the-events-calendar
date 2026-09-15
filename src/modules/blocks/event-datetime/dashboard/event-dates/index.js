/**
 * External dependencies
 */
import React from 'react';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { globals } from '@moderntribe/common/utils';
import EventDates from './template';
import { isDatesLocked } from '../../locked';

const { tec, wpHooks } = globals;

const isEnabled = () => Boolean( ( tec().recurrenceDates || {} ).enabled );

/**
 * The one-line hint under the date headline of a rule-locked Event: the pickers are
 * inert, the dashboard says why.
 *
 * @since TBD
 *
 * @return {JSX.Element} The hint.
 */
const LockHint = () => (
	<p className="tribe-editor__event-dates__lock-hint">
		{ __(
			'Dates locked by Events Calendar Pro recurrence rules. Open the date panel for details.',
			'the-events-calendar'
		) }
	</p>
);

/**
 * Injects the Event Dates panel into the datetime block dashboard.
 *
 * Registered at priority 20, after the Events Calendar Pro recurrence UI (10),
 * and yielding whenever earlier content exists: Pro owns the authoring UI when
 * it is active, in every combination.
 *
 * @since TBD
 *
 * @return {void}
 */
export const hook = () => {
	wpHooks.addFilter(
		'blocks.eventDatetime.dashboardHook',
		'tec/eventDates',
		( content, props ) => {
			if ( content !== null && content !== undefined ) {
				return content;
			}

			return isEnabled() ? <EventDates { ...props } /> : content;
		},
		20
	);

	wpHooks.addFilter(
		'blocks.eventDatetime.contentHook',
		'tec/eventDatesLockHint',
		( content ) => {
			if ( content !== null && content !== undefined ) {
				return content;
			}

			return isEnabled() && isDatesLocked() ? <LockHint /> : content;
		},
		20
	);
};

export default hook;
