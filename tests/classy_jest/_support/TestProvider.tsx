import * as React from 'react';
import { WPDataRegistry } from '@wordpress/data/build-types/registry';
import { createRegistry } from '@wordpress/data';
import {ProviderComponent} from "@tec/common/classy/components/Provider";

/**
 * Test provider component for Classy components in Jest tests.
 *
 * Wraps children in a ProviderComponent with a WordPress data registry. By default,
 * creates a new registry instance. You can optionally provide a custom registry for
 * advanced testing scenarios.
 *
 * @example
 * ```tsx
 * // Basic usage with default registry
 * render(
 *   <TestProvider>
 *     <MyComponent {...props} />
 *   </TestProvider>
 * );
 *
 * // Custom registry example
 * const myRegistry = createRegistry();
 * myRegistry.registerStore('my-store', {
 *   reducer: myReducer,
 *   actions: myActions,
 *   selectors: mySelectors,
 * });
 *
 * render(
 *   <TestProvider registry={myRegistry}>
 *     <MyComponent />
 *   </TestProvider>
 * );
 * ```
 *
 * @param {React.ReactNode} children - Components to render within the provider context
 * @param {WPDataRegistry} [registry=null] - Optional custom registry. Creates new registry if not provided
 *
 * @return {JSX.Element} Provider component with children wrapped
 */
export default function TestProvider( {
	children,
	registry = null,
}: {
	children: React.ReactNode;
	registry?: WPDataRegistry | null;
} ): React.JSX.Element {
	const registryToUse = registry || createRegistry();

	return <ProviderComponent registry={ registryToUse }>{ children as any }</ProviderComponent>;
}
