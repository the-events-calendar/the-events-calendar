/**
 * Internal dependencies
 */
import { maybeDispatch, PREFIX_EVENTS_STORE } from '@moderntribe/events/data/utils';

const dispatch = jest.fn();
const action = jest.fn();

describe( 'Store prefix', () => {
	expect( PREFIX_EVENTS_STORE ).toBe( '@@MT/EVENTS' );
} );

describe( 'Data utils maybeDispatch', () => {
	afterEach( () => {
		dispatch.mockClear();
		action.mockClear();
	} );

	test( 'Dispatch an action when attribute is present', () => {
		const attributes = {
			title: 'Modern tribe',
		};
		maybeDispatch( attributes, dispatch )( action, 'title' );
		expect( dispatch ).toHaveBeenCalled();
		expect( dispatch ).toHaveBeenCalledTimes( 1 );
		expect( dispatch ).toHaveBeenCalledWith( action( attributes.title ) );
	} );

	test( 'Action being fired when attribute is present', () => {
		const attributes = {
			title: 'Modern tribe',
		};
		maybeDispatch( attributes, dispatch )( action, 'title' );
		expect( action ).toHaveBeenCalled();
		expect( action ).toHaveBeenCalledTimes( 1 );
		expect( action ).toHaveBeenCalledWith( attributes.title );
	} );

	test( 'Action fired with the default value to be `falsy`', () => {
		const attributes = {
			show: false,
			hide: null,
			display: undefined,
			content: '',
			amount: 0,
			number: NaN,
		};

		maybeDispatch( attributes, dispatch )( action, 'show', true );
		expect( action ).toHaveBeenCalled();
		expect( action ).toHaveBeenCalledWith( false );

		maybeDispatch( attributes, dispatch )( action, 'hide', true );
		expect( action ).toHaveBeenCalled();
		expect( action ).toHaveBeenCalledWith( null );

		maybeDispatch( attributes, dispatch )( action, 'display', true );
		expect( action ).toHaveBeenCalled();
		expect( action ).toHaveBeenCalledWith( true );

		maybeDispatch( attributes, dispatch )( action, 'content', 'Custom string' );
		expect( action ).toHaveBeenCalled();
		expect( action ).toHaveBeenCalledWith( 'Custom string' );

		maybeDispatch( attributes, dispatch )( action, 'amount', 10 );
		expect( action ).toHaveBeenCalled();
		expect( action ).toHaveBeenCalledWith( 0 );

		maybeDispatch( attributes, dispatch )( action, 'number', 10 );
		expect( action ).toHaveBeenCalled();
		expect( action ).toHaveBeenCalledWith( 10 );

		expect( action ).toHaveBeenCalledTimes( 6 );
	} );

	test( 'Action fired with default value when value is `empty`', () => {
		maybeDispatch( { title: '' }, dispatch )( action, 'title', 'default' );
		expect( action ).toHaveBeenCalled();
		expect( action ).toHaveBeenCalledWith( 'default' );
		expect( action ).toHaveBeenCalledTimes( 1 );
	} );

	test( 'Action fired with default value when value is `false`', () => {
		maybeDispatch( { title: false }, dispatch )( action, 'title', 'default' );
		expect( action ).toHaveBeenCalledWith( false );
		expect( action ).toHaveBeenCalled();
		expect( action ).toHaveBeenCalledTimes( 1 );
	} );

	test( 'Action fired with default value when value is `undefined`', () => {
		maybeDispatch( { title: undefined }, dispatch )( action, 'title', 'default' );
		expect( action ).toHaveBeenCalledWith( 'default' );
		expect( action ).toHaveBeenCalled();
		expect( action ).toHaveBeenCalledTimes( 1 );
	} );

	test( 'Action fired with default value when value is `0`', () => {
		maybeDispatch( { title: 0 }, dispatch )( action, 'title', 'default' );
		expect( action ).toHaveBeenCalledWith( 0 );
		expect( action ).toHaveBeenCalled();
		expect( action ).toHaveBeenCalledTimes( 1 );
	} );

	test( 'Action not executed when attribute is not present', () => {
		maybeDispatch( {}, dispatch )( action, 'title' );
		expect( dispatch ).not.toHaveBeenCalled();
		expect( action ).not.toHaveBeenCalled();
	} );
} );
