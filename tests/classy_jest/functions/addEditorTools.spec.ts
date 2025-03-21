import { addEditorTools } from '../../../src/resources/packages/classy/functions/addEditorTools';
import { afterEach, describe, it, expect, jest } from '@jest/globals';

jest.mock( '@wordpress/i18n', () => ( {
	__: jest.fn( ( text ) => text ),
} ) );

import { registerPlugin } from '@wordpress/plugins';

jest.mock( '@wordpress/plugins', () => ( {
	registerPlugin: jest.fn( ( name: string, settings ) => null ),
} ) );

describe( 'addEditorTools', () => {
	afterEach( () => {
		jest.resetAllMocks();
		jest.restoreAllMocks();
	} );

	it( 'should add editor tools', () => {
		const mockDocument = new DOMParser().parseFromString(
			`<html><body>
                <div class="editor-document-tools">
                    <div class="editor-document-tools__left"></div>
                </div>
            </body> </html>`,
			'text/html'
		);
		const onClick = jest.fn();

		addEditorTools( onClick, mockDocument );

		expect( onClick ).not.toHaveBeenCalled();
		expect(
			mockDocument.querySelectorAll(
				'.editor-document-tools__left .tec-editor-tool'
			).length
		).toBe( 0 );
		expect( registerPlugin ).toHaveBeenCalledWith( 'tec-editor-tools', {
			render: expect.any( Function ),
		} );

		const registerPluginMock = registerPlugin as jest.Mock;
		const registerPluginMockCallSettings = registerPluginMock.mock
			.calls[ 0 ][ 1 ] as { render: Function };
		const renderEditorTools = registerPluginMockCallSettings.render;

		const renderedEditorTools = renderEditorTools();

		expect( renderedEditorTools ).toBeNull();
		expect(
			mockDocument.querySelectorAll(
				'.editor-document-tools__left .tec-editor-tool'
			).length
		).toBe( 1 );
		expect(
			mockDocument.querySelector(
				'.editor-document-tools__left .tec-editor-tool'
			).outerHTML
		).toMatchSnapshot();

		const secondRenderedEditorTools = renderEditorTools();

		expect( secondRenderedEditorTools ).toBeNull();
		expect(
			mockDocument.querySelectorAll(
				'.editor-document-tools__left .tec-editor-tool'
			).length
		).toBe( 1 );

		const button = mockDocument.querySelector(
			'.editor-document-tools__left .tec-editor-tool'
		) as HTMLElement;
		button.click();
		expect( onClick ).toHaveBeenCalledTimes( 1 );
	} );

	it( 'should not add any button if the document does not have the editor tools', () => {
		const mockDocument = new DOMParser().parseFromString(
			`<html><body>
                <div class="editor-document-tools">
                </div>
            </body> </html>`,
			'text/html'
		);
		const onClick = jest.fn();

		addEditorTools( onClick, mockDocument );

		expect( onClick ).not.toHaveBeenCalled();
		expect(
			mockDocument.querySelectorAll(
				'.editor-document-tools__left .tec-editor-tool'
			).length
		).toBe( 0 );
		expect( registerPlugin ).toHaveBeenCalledWith( 'tec-editor-tools', {
			render: expect.any( Function ),
		} );

		const registerPluginMock = registerPlugin as jest.Mock;
		const registerPluginMockCallSettings = registerPluginMock.mock
			.calls[ 0 ][ 1 ] as { render: Function };
		const renderEditorTools = registerPluginMockCallSettings.render;

		const renderedEditorTools = renderEditorTools();

		expect( renderedEditorTools ).toBeNull();
		expect(
			mockDocument.querySelectorAll(
				'.editor-document-tools .tec-editor-tool'
			).length
		).toBe( 0 );
	} );
} );
