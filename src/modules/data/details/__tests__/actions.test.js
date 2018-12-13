/**
 * Internal dependencies
 */
import { actions } from '@moderntribe/events/data/details';

describe( '[STORE] - Details actions', () => {
	test( 'Enable isLoading action', () => {
		expect( actions.enableDetailsIsLoading( 'events' ) ).toMatchSnapshot();
	} );

	test( 'Disable isLoading action', () => {
		expect( actions.disableDetailsIsLoading( 'events' ) ).toMatchSnapshot();
	} );

	test( 'Set details actions', () => {
		expect( actions.setDetails( 'events', { id: 20, title: 'Modern Tribe' } ) ).toMatchSnapshot();
	} );

	test( 'Set post type action', () => {
		expect( actions.setDetailsPostType( 'events', 'tribe_events' ) ).toMatchSnapshot();
	} );
} );
