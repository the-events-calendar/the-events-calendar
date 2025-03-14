import { whenEditorIsReady } from '../../../src/resources/packages/classy/functions/whenEditorIsReady';
import { select, subscribe } from '@wordpress/data';
import { beforeEach, describe, expect, it, jest } from '@jest/globals';

// Define a type for the callback that will be used to subscribe to the function.
type SubscribeCallback = () => void;

// Mock WordPress dependencies
jest.mock( '@wordpress/data', () => ( {
	subscribe: jest.fn(),
	select: jest.fn(),
	dispatch: jest.fn(),
} ) );

describe( 'whenEditorIsReady', () => {
	let mockUnsubscribe: jest.Mock;
	let subscribeCallback: SubscribeCallback;

	beforeEach( () => {
		jest.clearAllMocks();

		// Setup mock unsubscribe function.
		mockUnsubscribe = jest.fn();

		// Capture the callback passed to subscribe.
		( subscribe as jest.Mock ).mockImplementation(
			( callback: SubscribeCallback ) => {
				subscribeCallback = callback;
				return mockUnsubscribe;
			}
		);
	} );

	it( 'should resolve when __unstableIsEditorReady returns true', async () => {
		const mockCoreEditor = {
			__unstableIsEditorReady: jest.fn().mockReturnValue( true ),
		};

		( select as jest.Mock ).mockImplementation( ( store ) => {
			if ( store === 'core/editor' ) return mockCoreEditor;
			return {};
		} );

		const readyPromise: Promise< void > = whenEditorIsReady();
		// Manually trigger the subscribe callback.
		subscribeCallback();
		await readyPromise;

		expect( mockCoreEditor.__unstableIsEditorReady ).toHaveBeenCalled();
		expect( mockUnsubscribe ).toHaveBeenCalledTimes( 1 );
	} );

	it( 'should not resolve when conditions are not met', async () => {
		const mockCoreEditor = {
			__unstableIsEditorReady: jest.fn().mockReturnValue( false ),
		};

		( select as jest.Mock ).mockImplementation( ( store: string ) => {
			if ( store === 'core/editor' ) return mockCoreEditor;
			return {};
		} );

		const readyPromise: Promise< void > = whenEditorIsReady();
		// Manually trigger the subscribe callback
		subscribeCallback();

		// Put the readyPromise and a new promise that will resolve in 50ms in a race.
		// The new promise should be resolved first.
		expect(
			Promise.race( [
				readyPromise,
				new Promise( ( resolve ) =>
					setTimeout( () => resolve( 'not-ready' ), 50 )
				),
			] )
		).resolves.toBe( 'not-ready' );
		expect( mockCoreEditor.__unstableIsEditorReady ).toHaveBeenCalled();
		expect( mockUnsubscribe ).not.toHaveBeenCalled();
	} );

	it( 'should eventually resolve after conditions become true', async () => {
		const mockCoreEditor = {
			__unstableIsEditorReady: jest.fn().mockReturnValue( false ),
		};

		( select as jest.Mock ).mockImplementation( ( store: string ) => {
			if ( store === 'core/editor' ) return mockCoreEditor;
			return {};
		} );

		const readyPromise: Promise< void > = whenEditorIsReady();

		// Trigger the callback once with conditions not met.
		subscribeCallback();

		// Change the return value of the __unstableIsEditorReady function to return true.
		mockCoreEditor.__unstableIsEditorReady.mockReturnValue( true );

		// Trigger the callback again now that conditions are met.
		subscribeCallback();

		// Wait for promise to resolve.
		await readyPromise;

		expect( mockCoreEditor.__unstableIsEditorReady ).toHaveBeenCalledTimes(
			2
		);
		expect( mockUnsubscribe ).toHaveBeenCalledTimes( 1 );
	} );
} );
