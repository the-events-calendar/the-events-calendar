import { dispatch, select, subscribe } from '@wordpress/data';
import { contextNotices, watchContext, IDENTITY_NOTICE, STATUS_NOTICE, CONTEXT_FIELD } from '../recurrence-admin';

jest.mock( '@wordpress/dom-ready', () => jest.fn() );
jest.mock( '@wordpress/data', () => ( { dispatch: jest.fn(), select: jest.fn(), subscribe: jest.fn() } ) );

const context = {
	postId: 10000001,
	locked: false,
	isOccurrence: true,
	heading: 'Editing occurrence',
	schedule: 'dates',
	scheduleLabel: 'Multiple dates',
	start: 'January 12, 2050 · 9:00 am · UTC',
	end: 'January 12, 2050 · 10:00 am · UTC',
	scope: 'Content is shared. Date changes move only this occurrence.',
	parentEditLink: '/wp-admin/post.php?post=1&action=edit',
	datesLink: '/wp-admin/edit.php?tec_event=1&tec_dates=all',
	status: { show: true, title: 'Events Calendar Pro is inactive', message: 'Existing dates are preserved.', label: 'Reactivate Pro', url: '/wp-admin/plugins.php?nonce=test' },
};

test( 'preserves identity, date, scope, and explicit event and occurrence destinations', () => {
	const [ identity ] = contextNotices( context );
	expect( identity.text ).toContain( context.start );
	expect( identity.text ).toContain( context.scope );
	expect( identity.actions.map( ( action ) => action.url ) ).toEqual( [ context.parentEditLink, context.datesLink ] );
	expect( contextNotices( context ) ).toHaveLength( 1 );
	const locked = contextNotices( { ...context, locked: true, schedule: 'rules' } );
	expect( locked ).toHaveLength( 1 );
	expect( locked[ 0 ].type ).toBe( 'info' );
	expect( locked[ 0 ].actions[ 2 ].url ).toBe( context.status.url );
} );

test( 'keeps recovery contextual and capability checked without a separate Pro warning', () => {
	const restricted = contextNotices( { ...context, locked: true, status: { ...context.status, url: '', guidance: 'Ask your administrator about recurrence editing.' } } );
	expect( restricted ).toHaveLength( 1 );
	expect( restricted[ 0 ].actions.map( ( action ) => action.url ) ).toEqual( [ context.parentEditLink, context.datesLink ] );
	expect( restricted[ 0 ].text ).toContain( 'Ask your administrator' );
	const single = contextNotices( { ...context, isOccurrence: false, schedule: 'single', status: { show: false } } );
	expect( single ).toHaveLength( 1 );
	expect( single[ 0 ].actions ).toEqual( [] );
} );

test( 'keeps notices after saves, refreshes dates and Pro availability, and avoids dispatch loops', () => {
	const notices = { createNotice: jest.fn(), removeNotice: jest.fn() };
	let post = { id: context.postId };
	let onChange;
	const unsubscribe = jest.fn();
	subscribe.mockImplementation( ( callback ) => { onChange = callback; return unsubscribe; } );
	select.mockReturnValue( { getCurrentPost: () => post } );
	dispatch.mockReturnValue( notices );
	expect( watchContext( context ) ).toBe( unsubscribe );
	expect( notices.createNotice ).toHaveBeenCalledTimes( 1 );
	expect( notices.createNotice ).toHaveBeenCalledWith( 'info', expect.any( String ), expect.objectContaining( { id: IDENTITY_NOTICE, isDismissible: false } ) );
	onChange();
	expect( notices.createNotice ).toHaveBeenCalledTimes( 1 );
	post = { ...post, [ CONTEXT_FIELD ]: { ...context, start: 'February 1, 2050 · All Day · UTC', status: { show: false } } };
	onChange();
	expect( notices.createNotice ).toHaveBeenCalledTimes( 2 );
	expect( notices.createNotice.mock.calls[ 1 ][ 1 ] ).toContain( 'February 1, 2050' );
	expect( notices.removeNotice ).toHaveBeenCalledWith( STATUS_NOTICE );
	onChange();
	expect( notices.createNotice ).toHaveBeenCalledTimes( 2 );
} );
