import * as React from 'react';
import VenueCard from './VenueCard';
import { _x } from '@wordpress/i18n';
import { FetchedVenue } from '../../types/FetchedVenue';

export default function VenueCards( props: {
	venues: FetchedVenue[];
	onEdit: ( id: number ) => void;
	onRemove: ( id: number ) => void;
} ) {
	const { venues, onEdit, onRemove } = props;

	/**
	 * Filters the address separator used in the venue cards.
	 *
	 * @since TBD
	 *
	 * @param {string} $addressSeparator The address separator.
	 */
	const addressSeparator = _x( ', ', 'Address separator', 'the-events-calendar' );

	return (
		<div className="classy__linked-post-cards classy__linked-post-cards--venue">
			{ venues.map( ( venue ) => (
				<VenueCard
					key={ venue.id }
					{ ...venue }
					addressSeparator={ addressSeparator }
					onEdit={ onEdit }
					onRemove={ onRemove }
				/>
			) ) }
		</div>
	);
}
