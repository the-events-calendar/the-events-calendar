import React from 'react';

type ErrorDisplayProps = {
	error: Error;
};

/**
 * A pure function component that displays an error message and stack trace.
 *
 * @since TBD
 *
 * @param {Error} error The error object to display.
 */
export default function ErrorDisplay( {
	error,
}: ErrorDisplayProps ): React.ReactNode {
	return (
		<div
			className="classy-root classy-root--error"
			style={ {
				padding: '20px',
				backgroundColor: '#fdd',
				color: '#a94442',
				border: '1px solid #d6e9c6',
				display: 'flex',
				flexDirection: 'column',
				alignItems: 'center',
				justifyContent: 'center',
				gap: '20px',
				overflow: 'scroll',
			} }
		>
			<h2>An error occurred in the Classy application:</h2>
			<pre
				style={ {
					marginTop: '20px',
					backgroundColor: '#eee',
					padding: '15px',
					borderRadius: '4px',
				} }
			>
				{ error.stack }
			</pre>
		</div>
	);
}
