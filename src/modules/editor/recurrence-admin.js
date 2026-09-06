/**
 * External dependencies
 */
import domReady from '@wordpress/dom-ready';
import { dispatch, select, subscribe } from '@wordpress/data';
import { __ } from '@wordpress/i18n';

export const IDENTITY_NOTICE = 'tec-events-editing-context';
export const STATUS_NOTICE = 'tec-events-pro-availability';
export const CONTEXT_FIELD = 'tec_recurrence_admin';

/**
 * Builds persistent editor notices from the same read-only model as the event list.
 *
 * @since TBD
 * @param {Object} data Current server-provided editing context.
 * @return {Array} Notices, independent of Pro's editing scope and save notifications.
 */
export const contextNotices = ( data ) => {
	const actions = [];
	if ( data.isOccurrence && data.parentEditLink ) {
		actions.push( { label: __( 'Edit event details', 'the-events-calendar' ), url: data.parentEditLink } );
	}
	if ( data.schedule !== 'single' ) {
		actions.push( { label: __( 'View all dates', 'the-events-calendar' ), url: data.datesLink } );
	}
	const notices = [
		{
			id: IDENTITY_NOTICE,
			type: 'info',
			text: [ data.heading, data.scheduleLabel, data.start, data.end, data.scope ]
				.filter( Boolean )
				.join( ' · ' ),
			actions,
		},
	];
	if ( data.status?.show ) {
		notices.push( {
			id: STATUS_NOTICE,
			type: 'warning',
			text: `${ data.status.title } — ${ data.status.message }`,
			actions: data.status.url ? [ { label: data.status.label, url: data.status.url } ] : [],
		} );
	}
	return notices;
};

/**
 * Refreshes context after saves using an edit-only field on the existing post response.
 * A signature prevents notice dispatches from recursively triggering the subscription.
 *
 * @since TBD
 * @param {Object} initial Initial PHP editor configuration.
 * @return {Function} Subscription cleanup.
 */
export const watchContext = ( initial ) => {
	let signature;
	const update = () => {
		const editor = select( 'core/editor' );
		const notices = dispatch( 'core/notices' );
		if ( ! editor?.getCurrentPost || ! notices?.createNotice ) {
			return;
		}
		const post = editor.getCurrentPost();
		const context = post?.[ CONTEXT_FIELD ];
		const data = context?.heading ? context : initial;
		if ( ! data?.heading || ( post?.id && post.id !== data.postId ) ) {
			return;
		}
		const next = JSON.stringify( data );
		if ( signature === next ) {
			return;
		}
		signature = next;
		const items = contextNotices( data );
		if ( ! items.some( ( item ) => item.id === STATUS_NOTICE ) ) {
			notices.removeNotice( STATUS_NOTICE );
		}
		items.forEach( ( { id, type, text, actions } ) => {
			notices.createNotice( type, text, { id, actions, isDismissible: false } );
		} );
	};
	const unsubscribe = subscribe( update );
	update();
	return unsubscribe;
};

domReady( () => {
	const initial = window.tribe_editor_config?.events?.recurrenceAdmin;
	if ( initial ) {
		watchContext( initial );
	}
} );
