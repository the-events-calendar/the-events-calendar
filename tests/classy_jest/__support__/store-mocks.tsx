import { WPDataRegistry } from '@wordpress/data/build-types/registry';
import { createReduxStore, register, withRegistry } from '@wordpress/data';
import { render } from '@testing-library/react';

/**
 * Usage example:
 *
 * ```ts
 * afterEach(() => {
 * 	jest.resetAllMocks();
 * 	jest.restoreAllMocks();
 *
 * 	// After the mocks and modules resets.
 * 	resetAllStores();
 * });
 *
 * test('mock a store', () => {
 * 	const store = registerMockStore('core/editor', {
 * 		selectors: {
 * 			getEditedPostAttribute (state, attribute: string){
 * 				if (attribute === 'title') {
 * 					return '';
 * 				}
 *
 * 				return null;
 * 			}
 * 		}
 * 	});
 *
 * 	expect(select('core/editor').getEditedPostAttribute('title')).toBe('');
 * });
 *
 * test('use a real store', () => {
 * 	registerStoreIfNotRegistered('acme/store', store);
 *
 * 	expect(select('acme/store').getSomeValue()).toBe(23);
 * 	expect(select('core/editor')).toBe(undefined);
 * });
 * ```
 */

type MockStoreDescriptor = {
	reducer?: ( state: Object ) => Object;
	selectors: { [ key: string ]: ( state: Object, ...args: any ) => Object };
};

/**
 * Stores original stores in an object.
 */
let originalStores: Object = {};

/**
 * Holds the fetched registry instance.
 */
let fetchedRegister: WPDataRegistry | null = null;

/**
 * Fetches the default WordPress data registry.
 *
 * @returns {WPDataRegistry} The fetched registry instance.
 */
export function fetchDefaultRegistry(): WPDataRegistry {
	if ( fetchedRegister !== null ) {
		return fetchedRegister;
	}

	const RegistryFetcher = function ( { registry } ) {
		fetchedRegister = registry;
		return null;
	};

	const RegisterFetcherWithRegistry = withRegistry( RegistryFetcher );
	render( <RegisterFetcherWithRegistry /> );

	return fetchedRegister;
}

/**
 * Unregisters a store from the default WordPress data registry.
 *
 * @param {string} storeName The name of the store to unregister.
 */
export function unregisterStore( storeName: string ): void {
	const registry = fetchDefaultRegistry();
	// @ts-ignore
	const store = registry.stores.storeName ?? null;
	// @ts-ignore
	delete registry.stores[ storeName ];
	originalStores[ storeName ] = store;
}

/**
 * Resets all stores in the default WordPress data registry to their original state.
 */
export function resetAllStores(): void {
	const registry = fetchDefaultRegistry();

	Object.keys( originalStores ).forEach( ( storeName ) => {
		const originalStore = originalStores[ storeName ];
		// @ts-ignore
		delete registry.stores[ storeName ];
		if ( originalStore === null ) {
			// @ts-ignore
			delete registry.stores[ storeName ];
		} else {
			// @ts-ignore
			registry.stores[ storeName ] = originalStore;
		}
	} );

	originalStores = {};
	fetchedRegister = null;
}

/**
 * Registers a mock store with the default WordPress data registry.
 *
 * @param {string} storeName The name of the store to register.
 * @param {MockStoreDescriptor} storeDescriptor The descriptor for the mock store.
 * @returns {any} The created mock store instance.
 */
export function registerMockStore(
	storeName: string,
	storeDescriptor: any
): any {
	unregisterStore( storeName );

	if ( ! storeDescriptor.reducer ) {
		storeDescriptor.reducer = ( state ) => state;
	}
	const store = createReduxStore( storeName, storeDescriptor );
	register( store );

	return store;
}

/**
 * Registers a store with the default WordPress data registry if it is not already registered.
 *
 * @param {string} storeName The name of the store to register.
 * @param {Object} store The store instance to register.
 */
export function registerStoreIfNotRegistered(
	storeName: string,
	store: Object
): void {
	const registry = fetchDefaultRegistry();
	// @ts-ignore
	if ( registry.stores.hasOwnProperty( storeName ) ) {
		return;
	}
	register( store );
}
