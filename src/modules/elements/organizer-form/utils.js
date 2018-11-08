/**
 * External dependencies
 */
import { get } from 'lodash';

export function toFields( organizer = {} ) {
	const title = get( organizer, 'title', {} );
	const meta = get( organizer, 'meta', {} );
	return {
		title: get( title, 'rendered', '' ),
		email: get( meta, '_OrganizerEmail', '' ),
		phone: get( meta, '_OrganizerPhone', '' ),
		website: get( meta, '_OrganizerWebsite', '' ),
	};
}

export function toOrganizer( fields ) {
	const { title, email, phone, website } = fields;
	return {
		title,
		status: 'draft',
		meta: {
			_OrganizerEmail: email,
			_OrganizerPhone: phone,
			_OrganizerWebsite: website,
		},
	};
}
