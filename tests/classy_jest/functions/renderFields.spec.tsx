import * as React from 'react';
import { render } from '@testing-library/react';
import { beforeEach, afterEach, describe, expect, it, jest } from '@jest/globals';
import renderFields from '../../../src/resources/packages/classy/functions/renderFields.tsx';
import { RegistryProvider } from '@wordpress/data';
import { getRegistry } from '@tec/common/classy/store';

describe( 'renderFields', () => {
	beforeEach( () => {
		jest.resetModules();
	} );

	afterEach( () => {
		jest.resetModules();
	} );

	it( 'renders nothing if post is not Event', async () => {
		// Create the Classy registry; this will register the default stores as well.
		const registry = getRegistry();

		// Render the component inside a RegistryProvider.
		const { container } = render(
			<RegistryProvider value={ registry }>
				{ /* Here call the filter that will trigger the render fields function. */ }
			</RegistryProvider>
		);
	} );
} );
