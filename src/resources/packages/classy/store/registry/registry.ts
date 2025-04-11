import { STORE_NAME, storeConfig } from '../store';
import { createRegistry as wpDataCreateRegistry } from '@wordpress/data';
import {
	StoreDescriptor,
	WPDataRegistry,
} from '@wordpress/data/build-types/registry';

/**
 * Extend the global to let TypeScript know about the global window object.
 */
declare global {
	interface Window {
		wp?: {
			data?: {
				select: Function;
				dispatch: Function;
			};
		};
	}
}

/**
 * The original select function.
 *
 * @since TBD
 *
 * @type {Function}
 */
let originalSelect: Function | null = null;

/**
 * The original dispatch function.
 *
 * @since TBD
 *
 * @type {Function}
 */
let originalDispatch: Function | null = null;

/**
 * An adapter that will route data selections to the appropriate source.
 *
 * @since TBD
 *
 * @param {string|StoreDescriptor} storeSelector The name of the store to select from, or a StoreDescriptor.
 *
 * @returns {any} The selected data.
 */
function classyRegistrySelectAdapter(
	storeSelector: string | StoreDescriptor
): any {
	const storeName =
		typeof storeSelector === 'string' ? storeSelector : storeSelector.name;

	// console.log(
	// 	'classyRegistrySelectAdapter selecting store with name ',
	// 	storeName
	// );

	if ( storeName === STORE_NAME ) {
		return originalSelect( STORE_NAME );
	}

	/**
	 * If the store exists on the `window.wp.data` object, then select from it.
	 */
	const wpDataSelect = window?.wp?.data.select( storeName );
	if ( wpDataSelect ) {
		// @todo reroute requests to the Classy store
		return wpDataSelect;
	}

	console.error(
		`Classy select adapter: store ${ storeName } does not exist on window.wp.data or in Classy registry.`
	);

	return {}; // Return an empty object if the store cannot be selected.
}

/**
 * An adapter that will route data dispatches to the appropriate source.
 *
 * @since TBD
 *
 * @param {string|StoreDescriptor} storeSelector The name of the store to select from, or a StoreDescriptor.
 *
 * @returns {any} The selected data.
 */
function classyRegistryDispatchAdapter(
	storeSelector: string | StoreDescriptor
): any {
	const storeName =
		typeof storeSelector === 'string' ? storeSelector : storeSelector.name;

	// console.log(
	// 	'classyRegistryDispatchAdapter dispatching action to store with name ',
	// 	storeName
	// );

	if ( storeName === STORE_NAME ) {
		return originalDispatch( STORE_NAME );
	}

	/**
	 * If the store exists on the `window.wp.data` object, then dispatch from it.
	 */
	// @ts-ignore
	const wpDataDispatch = window?.wp?.data.dispatch( storeName );

	if ( wpDataDispatch ) {
		// @todo reroute requests to the Classy store.
		return wpDataDispatch;
	}

	console.error(
		`Classy dispatch adapter: store ${ storeName } does not exist on window.wp.data or in Classy registry.`
	);

	return {}; // Return an empty object if the store cannot be selected.
}

/**
 * Creates a registry, separated from the default WordPress one,
 * that will act as a router to handle requests coming from the Classy application components.
 * Requests for the Core stores will be handled by the WordPress registry if available.
 *
 * @since 1.0.0
 *
 * @return {WPDataRegistry} The created Classy registry.
 */
export function createRegistry(): WPDataRegistry {
	const classyRegistry: WPDataRegistry = wpDataCreateRegistry( {
		[ STORE_NAME ]: storeConfig,
	} );

	// Save a reference to the original `select` and `dispatch` functions.
	originalSelect = classyRegistry.select;
	originalDispatch = classyRegistry.dispatch;

	// Replace the default WordPress registry's select and dispatch functions with the adapters.
	classyRegistry.select = classyRegistrySelectAdapter;
	classyRegistry.dispatch = classyRegistryDispatchAdapter;

	return classyRegistry;
}
