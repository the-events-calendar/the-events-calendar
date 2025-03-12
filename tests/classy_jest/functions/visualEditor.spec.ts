import {
	getElement,
	toggleVisibility,
} from '../../../src/resources/packages/classy/functions/visualEditor';
import { afterEach, describe, expect, it, jest } from '@jest/globals';

describe( 'visualEditor', () => {
	afterEach( () => {
		jest.restoreAllMocks();
		jest.resetAllMocks();
	} );

	it( 'should allow getting the visual editor element', () => {
		const mockDocument = new DOMParser().parseFromString(
			`<html>
				<body>
					<divi id="test" class="editor-visual-editor edit-post-visual-editor"></dividd>
				</body>
			</html>`,
			'text/html'
		);

		const element = getElement( mockDocument );

		expect( element ).not.toBeNull();
		expect( element ).toBe( mockDocument.getElementById( 'test' ) );
	} );

	it( 'should allow return null if visual editor element not in DOM', () => {
		const mockDocument = new DOMParser().parseFromString(
			`<html>
				<body>
					<divi id="test" class="editor-visual-editor not-edit-post-visual-editor"></dividd>
				</body>
			</html>`,
			'text/html'
		);

		const element = getElement( mockDocument );

		expect( element ).toBeNull();
	} );

	it( 'should toggle the visibility of the element', () => {
		const mockDocument = new DOMParser().parseFromString(
			`<html>
				<body>
					<divi id="test" class="editor-visual-editor edit-post-visual-editor"></dividd>
				</body>
			</html>`,
			'text/html'
		);

		const element = mockDocument.getElementById( 'test' );

		expect( element ).not.toBeNull();

		let toggled = toggleVisibility( mockDocument );

		expect( toggled ).toBe( true );
		expect( element.style.height ).toBe( '0px' );
		expect( element.getAttribute( 'aria-hidden' ) ).toBe( 'true' );

		toggled = toggleVisibility( mockDocument );

		expect( toggled ).toBe( true );
		expect( element.style.height ).toBe( '' );
		expect( element.getAttribute( 'aria-hidden' ) ).toBeNull();
	} );

	it( 'should do nothing when toggling visibility if element not in DOM', () => {
		const mockDocument = new DOMParser().parseFromString(
			`<html>
				<body>
					<divi id="test" class="not-editor-visual-editor edit-post-visual-editor"></dividd>
				</body>
			</html>`,
			'text/html'
		);

		let toggled = toggleVisibility( mockDocument );

		expect( toggled ).toBe( false );
	} );
} );
