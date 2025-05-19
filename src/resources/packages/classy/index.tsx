import React, { Fragment } from 'react';
import { addFilter } from '@wordpress/hooks';
import { Fill } from '@wordpress/components';
import { PostTitle } from '@tec/common/classy/fields';
import {
	EventDateTime,
	EventDetails,
	EventOrganizer,
	EventLocation,
} from './fields';
import { _x } from '@wordpress/i18n';

addFilter(
	'tec.classy.render',
	'tec.classy.events',
	( fields: React.ReactNode | null ) => {
		return (
			<Fragment>
				{ fields }
				<Fill name="tec.classy.fields">
					<PostTitle
						title={ _x(
							'Event Title',
							'The title of the event title field.',
							'the-events-calendar'
						) }
					/>

					<EventDateTime
						title={ _x(
							'Date and Time',
							'The title of the event date and time field.',
							'the-events-calendar'
						) }
					/>

					<EventDetails
						title={ _x(
							'Event Details',
							'The title of the event details field.',
							'the-events-calendar'
						) }
					/>

					<EventLocation
						title={ _x(
							'Location',
							'The title of the event location field.',
							'the-events-calendar'
						) }
					/>

					<EventOrganizer
						title={ _x(
							'Event Organizer',
							'The title of the event organizer field.',
							'the-events-calendar'
						) }
					/>
				</Fill>
			</Fragment>
		);
	}
);
