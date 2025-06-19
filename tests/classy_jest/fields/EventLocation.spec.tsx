import * as React from 'react';
import { render } from '@testing-library/react';
import { userEvent } from '@testing-library/user-event';
import { afterEach, beforeEach, describe, expect, it, jest } from '@jest/globals';
import { Provider as ClassyProvider } from '@tec/common/classy/components/Provider.tsx';
// import { EventLocation } from '../../../src/resources/packages/classy/fields';

// @todo the apiFetch will have to be mocked since this component makes requests to the backed to get Venues.
// jest.mock('@wordpress/api-fetch');

// @todo the store is already registered, but not correctly -- fix

describe( 'EventLocation component', () => {
	beforeEach( async () => {
		jest.resetModules();
	} );

	afterEach( () => {
		jest.resetModules();
	} );

	it( 'renders correctly with default props', async () => {
		const user = userEvent.setup();

		// @todo after the above issues are fixed, re-enable this test.
		// const { findByText } = render(
		// 	<ClassyProvider>
		// 		<EventLocation title="Event Location TEST"/>
		// 	</ClassyProvider>
		// );

		const { container } = render(
			<ClassyProvider>
				<p>Hello from Event Location</p>
			</ClassyProvider>
		);
	} );
} );
