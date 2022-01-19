/**
 * External dependencies
 */
import React from 'react';
import IframeResizer from 'iframe-resizer-react';

/**
 * WordPress dependencies
 */

/**
 * Internal dependencies
 */
import './style.pcss';

/**
 * Module Code
 */

const render = ( props ) => {
	const { attributes } = props;
	const ajaxAction = 'tec_events_iframe_full_site_editor';

	let customClassName = [ attributes.className ];
	if ( 'full' === attributes.align ) {
		customClassName.push( 'alignfull' );
	}

	const iframeStyle = {
		width: '1px',
		minWidth: '100%'
	};

	const iframeSrc = `${ ajaxurl }?action=${ ajaxAction }`;

	return (
		<div key="tec-archive-events" className="tribe-editor__block tribe-editor__archive-events">
			<IframeResizer
				log
				heightCalculationMethod="lowestElement"
				className={ customClassName.join( ' ' ) }
				src={ iframeSrc }
				style={ iframeStyle }
				frameBorder="0"
				scrolling="no"
			/>
		</div>
	)
};

const ArchiveEvents = ( props ) => {
	return (
		render( props )
	);
};

export default ArchiveEvents;
