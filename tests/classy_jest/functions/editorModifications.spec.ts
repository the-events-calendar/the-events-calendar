import {
	hideInserterToggle,
	hideZoomOutButton,
} from '../../../src/resources/packages/classy/functions/editorModifications';
import { afterEach, describe, expect, it, jest } from '@jest/globals';

jest.mock( '@wordpress/i18n', () => ( {
	__: jest.fn( ( text ) => text ),
} ) );

describe( 'hideZoomOutButton', () => {
	afterEach( () => {
		jest.restoreAllMocks();
	} );

	it( 'should hide zoom out buttons', () => {
		const mockDocument = new DOMParser().parseFromString(
			`
		<html>
			<body>
			  <button id="button1" class="components-button" aria-label="Zoom Out">Button 1</button>
			  <button id="button2" class="components-button" aria-label="Zoom In">Button 2</button>
			  <button id="button3" class="components-button" aria-label="Zoom Out" data-test="target">Button 3</button>
			  <button id="button4" class="some-other-button"></button>
			</body>
		</html>
    	`,
			'text/html'
		);

		const hidden = hideZoomOutButton( mockDocument );

		expect( hidden ).toBe( 2 );
		expect( mockDocument.getElementById( 'button1' ).style.display ).toBe(
			'none'
		);
		expect( mockDocument.getElementById( 'button2' ).style.display ).toBe(
			''
		);
		expect( mockDocument.getElementById( 'button3' ).style.display ).toBe(
			'none'
		);
		expect( mockDocument.getElementById( 'button4' ).style.display ).toBe(
			''
		);
	} );

	it( 'should return 0 when there are no buttons to hide', () => {
		const mockDocument = new DOMParser().parseFromString(
			`
		<html>
			<body>
			  <button id="button1" class="components-button" aria-label="Zoom In">Button 1</button>
			  <button id="button2" class="components-button" aria-label="Zoom Out Slightly">Button 2</button>
			  <button id="button3" class="some-other-button"></button>
			</body>
		</html>
    	`,
			'text/html'
		);

		const hidden = hideZoomOutButton( mockDocument );

		expect( hidden ).toBe( 0 );
		expect( mockDocument.getElementById( 'button1' ).style.display ).toBe(
			''
		);
		expect( mockDocument.getElementById( 'button2' ).style.display ).toBe(
			''
		);
		expect( mockDocument.getElementById( 'button3' ).style.display ).toBe(
			''
		);
	} );
} );

describe( 'hideInserterToggle', () => {
	afterEach( () => {
		jest.restoreAllMocks();
	} );

	it( 'should hide inserter toggle buttons', () => {
		const mockDocument = new DOMParser().parseFromString(
			`
		  <html>
			<body>
			  <button id="button1" class="editor-document-tools__inserter-toggle"></button>
			  <button id="button2" class="editor-document-tools__inserter-toggle"></button>
			  <button id="button3" class="some-other-button"></button>
			</body>
		  </html>
		`,
			'text/html'
		);

		const hidden = hideInserterToggle( mockDocument );

		expect( hidden ).toBe( 2 );
		expect( mockDocument.getElementById( 'button1' ).style.display ).toBe(
			'none'
		);
		expect( mockDocument.getElementById( 'button2' ).style.display ).toBe(
			'none'
		);
		expect( mockDocument.getElementById( 'button3' ).style.display ).toBe(
			''
		);
	} );

	it( 'should return 0 when there are no buttons to hide', () => {
		const mockDocument = new DOMParser().parseFromString(
			`
		  <html>
			<body>
			  <button id="button1" class="some-other-button"></button>
			  <button id="button2" class="some-other-button"></button>
			</body>
		  </html>
		`,
			'text/html'
		);

		const hidden = hideInserterToggle( mockDocument );

		expect( hidden ).toBe( 0 );
		expect( mockDocument.getElementById( 'button1' ).style.display ).toBe(
			''
		);
		expect( mockDocument.getElementById( 'button2' ).style.display ).toBe(
			''
		);
	} );
} );
