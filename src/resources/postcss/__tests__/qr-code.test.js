/**
 * Tests for qr-code.pcss
 *
 * @since 6.18.5
 */

const fs = require( 'fs' );
const path = require( 'path' );

describe( 'qr-code.pcss', () => {
	const source = fs
		.readFileSync( path.resolve( __dirname, '../qr-code.pcss' ), 'utf8' )
		.replace( /\/\*[\s\S]*?\*\//g, '' );

	test( 'sizes only inline-content Thickboxes, not iframe dialogs like Move Ticket', () => {
		const selectors = source.match( /#TB_window[^\s,{]*/g ) || [];

		expect( selectors.length ).toBeGreaterThan( 0 );
		selectors.forEach( ( selector ) => {
			expect( selector ).toBe( '#TB_window:has(#TB_ajaxContent)' );
		} );
	} );
} );
