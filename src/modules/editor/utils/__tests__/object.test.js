/**
 * Internal dependencies
 */
import { removeEmptyStrings, castBooleanStrings } from '@moderntribe/events/editor/utils/object';

describe( 'Tests for object.js', () => {
	test( 'removeEmptyStrings', () => {
		expect( removeEmptyStrings( {} ) ).toEqual( {} );
		expect( removeEmptyStrings( { a: 1, b: 'sample', c: '' } ) ).toEqual( { a: 1, b: 'sample' } );
		expect( removeEmptyStrings( { a: '', b: '', c: '' } ) ).toEqual( {} );
		expect( removeEmptyStrings( { a: undefined, b: null, c: '' } ) )
			.toEqual( { a: undefined, b: null } );
		expect( removeEmptyStrings( { a: undefined, b: null, c: 'false' } ) )
			.toEqual( { a: undefined, b: null, c: 'false' } );
	} );

	test( 'castBooleanStrings', () => {
		expect( castBooleanStrings( {} ) ).toEqual( {} );
		expect( castBooleanStrings( { a: '0', b: 'no', c: 'false', d: true } ) )
			.toEqual( { a: false, b: false, c: false, d: true } );
		expect( castBooleanStrings( { a: '1', b: 'yes', c: 'true', d: false } ) )
			.toEqual( { a: true, b: true, c: true, d: false } );
	} );
} );
