import React, { Component } from 'react';

type FallbackComponentType = ( error: Error ) => React.ReactNode;

type ErrorBoundaryProps = {
	children: React.ReactNode;
	fallback: FallbackComponentType;
};

type ErrorBoundaryState = {
	error: Error | null;
};

/**
 * A boundary component that catches JavaScript errors anywhere in its child component tree,
 * logs those errors, and displays a fallback UI.
 *
 * @since TBD
 */
export default class ErrorBoundary extends Component<
	ErrorBoundaryProps,
	ErrorBoundaryState
> {
	/**
	 * Constructs the ErrorBoundary instance with initial state.
	 *
	 * @since TBD
	 *
	 * @param {ErrorBoundaryProps} props The properties passed to this component.
	 */
	constructor( props: ErrorBoundaryProps ) {
		super( props );
		this.state = { error: null };
	}

	/**
	 * Updates the state with the error caught by the nearest descendant error boundary.
	 *
	 * @since TBD
	 *
	 * @param {Error} error The error object that was thrown.
	 *
	 * @return {ErrorBoundaryState} The new state of the component.
	 */
	static getDerivedStateFromError( error: Error ) {
		return { error };
	}

	/**
	 * Logs the error and its information to the console.
	 *
	 * @since TBD
	 *
	 * @param {any} error The error object that was thrown.
	 * @param {any} errorInfo Information about which component threw the error.
	 */
	componentDidCatch( error: any, errorInfo: any ) {
		console.error( 'Classy has throw an error:', error, errorInfo );
	}

	/**
	 * Renders either the fallback UI if there is an error in the state,
	 * or the children components otherwise.
	 *
	 * @since TBD
	 *
	 * @return {React.ReactNode} The component to render.
	 */
	render() {
		if ( this.state.error !== null ) {
			return this.props.fallback( this.state.error );
		}

		return this.props.children;
	}
}
