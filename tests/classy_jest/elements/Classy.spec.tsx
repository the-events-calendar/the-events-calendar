import {
	afterEach,
	beforeEach,
	describe,
	expect,
	jest,
	test,
} from '@jest/globals';
import { Classy } from '../../../src/resources/packages/classy/elements';
import { render } from '@testing-library/react';
import { registerStore } from '../../../src/resources/packages/classy/store';

describe( 'Classy', () => {
	beforeEach( () => {
		global.mockWindowMatchMedia();
	} );

	afterEach( () => {
		jest.resetAllMocks();
		jest.restoreAllMocks();
	} );

	test( 'initial state render for new post', () => {
		registerStore( { title: '' } );

		const { container } = render( <Classy /> );

		expect( container ).toMatchSnapshot();
	} );

	test( 'initial state render with title for existing post', () => {
		registerStore( { title: 'Test Event' } );

		const { container } = render( <Classy /> );

		expect( container ).toMatchSnapshot();
	} );
} );
