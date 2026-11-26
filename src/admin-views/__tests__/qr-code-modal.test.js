/**
 * Tests for the inline script in qr-code-modal.php
 *
 * @since 6.18.5
 */

const fs = require( 'fs' );
const path = require( 'path' );

const template = fs.readFileSync( path.resolve( __dirname, '../qr-code-modal.php' ), 'utf8' );
const script = template.match( /<script>([\s\S]*?)<\/script>/ )[ 1 ];

const openThickbox = () => {
	document.body.innerHTML = '<div id="TB_window" style="width: 783px"><div id="TB_ajaxContent"></div></div>';
};

// jQuery 3 runs ready callbacks asynchronously, even when the document is already loaded.
const flushReady = () => new Promise( ( resolve ) => setTimeout( resolve ) );

describe( 'qr-code-modal.php', () => {
	beforeAll( () => {
		// jsdom has no layout, so give the window a width for the modal to copy.
		Object.defineProperty( document.documentElement, 'clientWidth', { value: 1600, configurable: true } );
	} );

	afterEach( () => {
		jQuery( window ).off( 'resize' );
		document.body.innerHTML = '';
	} );

	test( 'stops resizing Thickboxes once the QR modal is closed', async () => {
		openThickbox();
		// eslint-disable-next-line no-eval
		eval( script );
		await flushReady();
		expect( jQuery( '#TB_window' ).css( 'width' ) ).toBe( '1600px' );

		jQuery( '#TB_window' ).trigger( 'tb_unload' );
		document.body.innerHTML = '<div id="TB_window" style="width: 783px"></div>';
		jQuery( window ).trigger( 'resize' );

		expect( jQuery( '#TB_window' ).css( 'width' ) ).toBe( '783px' );
	} );
} );
