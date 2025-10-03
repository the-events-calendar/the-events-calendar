import * as React from 'react';
import { Fragment } from 'react';
import { IconEdit, IconTrash } from '@tec/common/classy/components';
import { Button } from '@wordpress/components';
import { FetchedVenue } from '../../types/FetchedVenue';
import { decodeEntities } from '@wordpress/html-entities';

function buildFullAddress( fetchedVenue: FetchedVenue, addressSeparator = ', ' ): string[] {
	const { address, city, state, province, zip, country } = fetchedVenue;

	const line1: string[] = [];
	const line2: string[] = [];
	const line3: string[] = [];

	if ( address ) {
		line1.push( address );
	}

	if ( city ) {
		line2.push( city );
	}

	if ( state && province ) {
		if ( state !== province ) {
			line2.push( state, province );
		} else {
			line2.push( state );
		}
	} else if ( state ) {
		line2.push( state );
	} else if ( province ) {
		line2.push( province );
	}

	if ( zip ) {
		line3.push( zip );
	}

	if ( country ) {
		line3.push( `${ country }` );
	}

	return [ line1.join( addressSeparator ), line2.join( addressSeparator ), line3.join( addressSeparator ) ];
}

export default function VenueCard(
	props: FetchedVenue & {
		onEdit: ( id: number ) => void;
		onRemove: ( id: number ) => void;
		addressSeparator: string;
	}
) {
	const { id: venueId, venue: name, phone, website, addressSeparator } = props;

	const [ addressLine1, addressLine2, addressLine3 ] = buildFullAddress( props, addressSeparator );
	const fullAddress =
		addressLine1 || addressLine2 || addressLine3 ? (
			<Fragment>
				{ addressLine1 && (
					<div className="classy-linked-post-card__detail-line">{ decodeEntities( addressLine1 ) }</div>
				) }
				{ addressLine2 && (
					<div className="classy-linked-post-card__detail-line">{ decodeEntities( addressLine2 ) }</div>
				) }
				{ addressLine3 && (
					<div className="classy-linked-post-card__detail-line">{ decodeEntities( addressLine3 ) }</div>
				) }
			</Fragment>
		) : null;

	return (
		<div className="classy__linked-post-card classy__linked-post-card--venue" data-object-id={ venueId }>
			<h4 className="classy-linked-post-card__title">{ decodeEntities( name ) }</h4>

			{ fullAddress && <span className="classy-linked-post-card__detail">{ fullAddress }</span> }

			{ phone && <span className="classy-linked-post-card__detail">{ decodeEntities( phone ) }</span> }

			{ website && (
				<Button variant="link" className="classy-linked-post-card__detail" href={ website } target="_blank">
					{ decodeEntities( website ) }
				</Button>
			) }

			<div className="classy-linked-post-card__actions">
				<Button
					variant="link"
					onClick={ () => props.onEdit( venueId ) }
					className="classy-linked-post-card__action"
				>
					<IconEdit />
				</Button>
				<Button
					variant="link"
					onClick={ () => props.onRemove( venueId ) }
					className="classy-linked-post-card__action"
				>
					<IconTrash />
				</Button>
			</div>
		</div>
	);
}
