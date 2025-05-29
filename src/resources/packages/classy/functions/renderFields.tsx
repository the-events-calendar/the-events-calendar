import React, { Fragment } from 'react';
import { Fill } from '@wordpress/components';
import { PostTitle } from '@tec/common/classy/fields';
import { EventDateTime, EventDetails, EventLocation, EventOrganizer } from '../fields';
import { _x } from '@wordpress/i18n';
import { useSelect } from '@wordpress/data';
import { POST_TYPE_EVENT } from '../constants';

/**
 * Render fields for the Event post type.
 *
 * @since TBD
 *
 * @param {React.ReactNode | null} fields The fields that have been already rendered.
 */
export default function renderFields( fields: React.ReactNode | null ) {
	const postType = useSelect( ( select ) => {
		// @ts-ignore
		return select( 'core/editor' ).getEditedPostAttribute( 'type' );
	}, [] );

	if ( POST_TYPE_EVENT !== postType ) {
		return fields;
	}

	return (
		<Fragment>
			{ /* Render the fields passed to this function first. */ }
			{ fields }

			{/* Portal-render the fields into the Classy form. */ }
			<Fill name="tec.classy.fields">
				<PostTitle
					title={ _x( 'Event Title', 'The title of the event title field.', 'the-events-calendar' ) }
				/>

				<EventDateTime
					title={ _x(
						'Date and Time',
						'The title of the event date and time field.',
						'the-events-calendar'
					) }
				/>

				<EventDetails
					title={ _x( 'Event Details', 'The title of the event details field.', 'the-events-calendar' ) }
				/>

				<EventLocation
					title={ _x( 'Location', 'The title of the event location field.', 'the-events-calendar' ) }
				/>

				<EventOrganizer
					title={ _x( 'Event Organizer', 'The title of the event organizer field.', 'the-events-calendar' ) }
				/>
			</Fill>
		</Fragment>
	);
}
