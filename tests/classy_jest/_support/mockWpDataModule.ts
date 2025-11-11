// @ts-nocheck
// eslint-disable-next-line @typescript-eslint/triple-slash-reference
/// <reference types="jest" />

/**
 * Creates mocked versions of WordPress data module functions for testing.
 *
 * This function mocks the @wordpress/data package's select, useSelect, and useDispatch
 * functions to allow for controlled testing of components that use WordPress data stores.
 *
 * @example
 * // Usage in tests - see tests/classy_jest/fields/EventCost.spec.tsx for full implementation
 * import mockWpDataModule from '../_support/mockWpDataModule';
 *
 * const {mockSelect, mockUseDispatch} = mockWpDataModule();
 *
 * // Mock the select function to return specific store data.
 * mockSelect.mockImplementation((store: string): any => {
 *   if (store === 'core/editor') {
 *     return {
 *       getEditedPostAttribute: (attribute: string): any => {
 *         return attribute === 'meta' ? {} : null;
 *       },
 *     };
 *   }
 *   if (store === 'tec/classy') {
 *     return {
 *       getCurrencyOptions: () => currencyOptions,
 *       getDefaultCurrency: () => currencyOptions[0],
 *     };
 *   }
 * });
 *
 * // Mock the useDispatch hook to return action creators.
 * mockUseDispatch.mockImplementation((store: string): Function[] => {
 *   if (store === 'core/editor') {
 *     return {
 *       editPost: jest.fn(),
 *     };
 *   }
 * });
 *
 * @returns {Object} An object containing the mocked functions:
 * @returns {jest.MockedFunction} mockSelect Mocked version of the select function
 * @returns {jest.MockedFunction} mockUseSelect Mocked version of the useSelect hook
 * @returns {jest.MockedFunction} mockUseDispatch Mocked version of the useDispatch hook
 */
export default function mockWpDataModule(): {
	mockSelect: jest.MockedFunction;
	mockUseSelect: jest.MockedFunction;
	mockUseDispatch: jest.MockedFunction;
} {
	// Mock the `@wordpress/data` package to intercept the `useDispatch` and `useSelect` hooks.
	jest.mock( '@wordpress/data', () => ( {
		...( jest.requireActual( '@wordpress/data' ) as Object ),
		select: jest.fn(),
		useSelect: jest.fn(),
		useDispatch: jest.fn(),
	} ) );

	// Get the @wordpress/data module mocked functions.
	const mockSelect = jest.mocked( require( '@wordpress/data' ).select );
	const mockUseSelect = jest.mocked( require( '@wordpress/data' ).useSelect );
	const mockUseDispatch = jest.mocked( require( '@wordpress/data' ).useDispatch );

	// Mock the `useSelect` hook function to return the mock select function.
	mockUseSelect.mockImplementation( ( selector: Function ): any => {
		return selector( mockSelect );
	} );

	return { mockSelect, mockUseSelect, mockUseDispatch };
}
