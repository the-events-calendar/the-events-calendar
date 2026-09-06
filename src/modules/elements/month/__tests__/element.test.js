/**
 * External dependencies
 */
import React from 'react';
import { DayPicker } from 'react-day-picker';

/**
 * Internal dependencies
 */
import Month from '../element';

describe( 'Month element', () => {
	test( 'forwards the disabled flag to the day picker and ignores day clicks', () => {
		const onSelect = jest.fn();
		const tree = renderer.create( <Month disabled={ true } onSelect={ onSelect } from={ new Date( 2050, 0, 5 ) } /> );

		const picker = tree.root.findByType( DayPicker );
		expect( picker.props.disabled ).toBe( true );
		expect( picker.props.className ).toContain( 'tribe-editor__calendars--disabled' );

		renderer.act( () => {
			picker.props.onDayClick( new Date( 2050, 0, 6 ) );
		} );

		expect( onSelect ).not.toHaveBeenCalled();
	} );

	test( 'selects days when enabled', () => {
		const onSelect = jest.fn();
		const tree = renderer.create( <Month onSelect={ onSelect } from={ new Date( 2050, 0, 5 ) } /> );

		const picker = tree.root.findByType( DayPicker );
		expect( picker.props.disabled ).toBe( false );

		renderer.act( () => {
			picker.props.onDayClick( new Date( 2050, 0, 6 ) );
		} );

		expect( onSelect ).toHaveBeenCalledTimes( 1 );
	} );
} );
