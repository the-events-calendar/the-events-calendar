import React from 'react';
import { FetchedOrganizer } from '../../../types/FetchedOrganizer';
import OrganizerCard from './OrganizerCard';

export default function OrganizerCards( props: {
	organizers: FetchedOrganizer[];
	onEdit: ( id: number ) => void;
	onRemove: ( id: number ) => void;
} ) {
	const { organizers, onEdit, onRemove } = props;

	const cards = organizers.map( ( organizer: FetchedOrganizer ) => {
		return (
			<OrganizerCard
				key={ organizer.id }
				{ ...organizer }
				onEdit={ onEdit }
				onRemove={ onRemove }
			/>
		);
	} );

	return (
		<div className="classy__linked-post-cards classy__linked-post-cards--organizer">
			{ cards }
		</div>
	);
}
