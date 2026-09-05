/**
 * External dependencies
 */
import React from 'react';
import { CheckboxControl } from '@wordpress/components';

/**
 * Internal dependencies
 */
import EventDates from '../template';

const setConfig = ( recurrenceDates ) => {
	global.tribe_editor_config = { events: { recurrenceDates } };
};

const lockedSummary = {
	count: 2,
	dates: [
		{
			label: 'January 5, 2050',
			tooltip: [ 'Wednesday, January 5, 2050 @ 9:00 am – 10:00 am', 'Next occurrence', 'Opens the occurrence in a new tab.' ],
			permalink: 'https://example.test/event/multi/2050-01-05/',
			editLink: 'https://example.test/wp-admin/post.php?post=10000001&action=edit',
			status: 'next',
		},
		{
			label: 'January 12, 2050',
			tooltip: [ 'Wednesday, January 12, 2050 @ 9:00 am – 10:00 am', 'Upcoming', 'Opens the occurrence in a new tab.' ],
			permalink: 'https://example.test/event/multi/2050-01-12/',
			editLink: 'https://example.test/wp-admin/post.php?post=10000002&action=edit',
			status: 'upcoming',
		},
	],
};

const convertibleConfig = {
	enabled: true,
	locked: true,
	isOccurrence: false,
	parentEditLink: '',
	summary: lockedSummary,
	lockEnabled: false,
	settingsUrl: 'https://example.test/wp-admin/edit.php?page=tec-events-settings&tab=general-editing-tab',
	canConvert: true,
	convertUrl: 'https://example.test/wp-admin/admin-post.php',
	convertAction: 'tec_events_recurrence_convert',
	ackField: 'tec_events_recurrence_convert_ack',
	convertNonce: 'abc123',
	postId: 42,
	notice: null,
};

const render = () => renderer.create( <EventDates attributes={ {} } setAttributes={ jest.fn() } start="2050-01-05 09:00:00" /> );

describe( 'Event Dates panel', () => {
	afterEach( () => {
		delete global.tribe_editor_config;
	} );

	test( 'renders the editable rows by default', () => {
		setConfig( { enabled: true, locked: false, isOccurrence: false, summary: { count: 0, dates: [] } } );

		expect( render().toJSON() ).toMatchSnapshot();
	} );

	test( 'renders the occurrence notice', () => {
		setConfig( {
			enabled: true,
			locked: false,
			isOccurrence: true,
			parentEditLink: 'https://example.test/wp-admin/post.php?post=42&action=edit',
			summary: { count: 0, dates: [] },
		} );

		expect( render().toJSON() ).toMatchSnapshot();
	} );

	test( 'renders the locked notice with the settings link when the lock is enabled', () => {
		setConfig( { ...convertibleConfig, lockEnabled: true, canConvert: false, convertNonce: '' } );

		const tree = render();
		const json = JSON.stringify( tree.toJSON() );

		expect( json ).toContain( 'Notice--info' );
		expect( json ).toContain( 'turn off the recurrence lock' );
		expect( json ).not.toContain( 'Convert to individual dates' );
		expect( tree.root.findAllByType( 'form' ) ).toHaveLength( 0 );
		expect( tree.toJSON() ).toMatchSnapshot();
	} );

	test( 'renders the conversion form, disabled until acknowledged, when the lock is disabled', () => {
		setConfig( convertibleConfig );

		const tree = render();
		const json = JSON.stringify( tree.toJSON() );

		expect( json ).toContain( 'Notice--warning' );
		expect( json ).toContain( 'keeps the 2 dates currently scheduled' );

		const form = tree.root.findByType( 'form' );
		expect( form.props.action ).toBe( convertibleConfig.convertUrl );
		expect( form.props.method ).toBe( 'post' );

		const hidden = ( name ) => form.findAll( ( node ) => 'input' === node.type && node.props.name === name );
		expect( hidden( 'action' )[ 0 ].props.value ).toBe( 'tec_events_recurrence_convert' );
		expect( hidden( 'post_id' )[ 0 ].props.value ).toBe( 42 );
		expect( hidden( '_wpnonce' )[ 0 ].props.value ).toBe( 'abc123' );
		expect( hidden( 'tec_events_recurrence_convert_ack' ) ).toHaveLength( 0 );

		const submit = form.findAll( ( node ) => 'button' === node.type && 'submit' === node.props.type )[ 0 ];
		expect( submit.props.disabled ).toBe( true );

		expect( tree.toJSON() ).toMatchSnapshot();

		renderer.act( () => {
			tree.root.findByType( CheckboxControl ).props.onChange( true );
		} );

		const enabled = tree.root.findAll( ( node ) => 'button' === node.type && 'submit' === node.props.type )[ 0 ];
		expect( enabled.props.disabled ).toBe( false );
		expect( hidden( 'tec_events_recurrence_convert_ack' )[ 0 ].props.value ).toBe( '1' );
	} );

	test( 'hides the conversion form from users who cannot convert', () => {
		setConfig( { ...convertibleConfig, canConvert: false, convertNonce: '' } );

		const tree = render();

		expect( tree.root.findAllByType( 'form' ) ).toHaveLength( 0 );
		expect( JSON.stringify( tree.toJSON() ) ).toContain( 'Notice--warning' );
	} );
} );
