import React from 'react';
import { FetchedOrganizer } from '../../../types/FetchedOrganizer';
import EditIcon from '../../components/Icons/Edit';
import TrashIcon from '../../components/Icons/Trash';
import { Button } from '@wordpress/components';

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
			<h4 className="classy-linked-post-card__title">{ name }</h4>
			<span className="classy-linked-post-card__detail">{ phone }</span>
			<span className="classy-linked-post-card__detail">{ email }</span>
			<Button
				variant="link"
				className="classy-linked-post-card__detail"
				target="_blank"
			>
				{ website }
			</Button>

			<div className="classy-linked-post-card__actions">
				<Button
					variant="link"
					onClick={ () => props.onEdit( objectId ) }
					className="classy-linked-post-card__action"
				>
					<EditIcon />
				</Button>
				<Button
					variant="link"
					onClick={ () => props.onRemove( objectId ) }
					className="classy-linked-post-card__action"
				>
					<TrashIcon />
				</Button>
			</div>
		</div>
	);
}
