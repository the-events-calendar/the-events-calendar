import * as React from 'react';
import { IconEdit, IconTrash } from '@tec/common/classy/components';
import { Button } from '@wordpress/components';
import { FetchedOrganizer } from '../../types/FetchedOrganizer';
import { decodeEntities } from '@wordpress/html-entities';

export default function OrganizerCard(
	props: FetchedOrganizer & {
		onEdit: ( id: number ) => void;
		onRemove: ( id: number ) => void;
	}
) {
	const { id: objectId, organizer: name, phone, email, website } = props;

	return (
		<div
			className="classy__linked-post-card classy__linked-post-card--organizer"
			key={ objectId }
			data-object-id={ objectId }
		>
			<h4 className="classy-linked-post-card__title">{ decodeEntities( name ) }</h4>

			{ phone && <span className="classy-linked-post-card__detail">{ decodeEntities( phone ) }</span> }

			{ email && <span className="classy-linked-post-card__detail">{ decodeEntities( email ) }</span> }

			{ website && (
				<Button variant="link" className="classy-linked-post-card__detail" target="_blank">
					{ ' ' }
					{ decodeEntities( website ) }{ ' ' }
				</Button>
			) }

			<div className="classy-linked-post-card__actions">
				<Button
					variant="link"
					onClick={ () => props.onEdit( objectId ) }
					className="classy-linked-post-card__action"
				>
					<IconEdit />
				</Button>
				<Button
					variant="link"
					onClick={ () => props.onRemove( objectId ) }
					className="classy-linked-post-card__action"
				>
					<IconTrash />
				</Button>
			</div>
		</div>
	);
}
