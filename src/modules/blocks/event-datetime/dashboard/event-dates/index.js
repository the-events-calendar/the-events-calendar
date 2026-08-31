/**
 * External dependencies
 */
import React from 'react';

/**
 * Internal dependencies
 */
import { globals } from '@moderntribe/common/utils';
import EventDates from './template';

const { tec, wpHooks } = globals;

const isEnabled = () => Boolean( ( tec().recurrenceDates || {} ).enabled );

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
};

export default hook;
