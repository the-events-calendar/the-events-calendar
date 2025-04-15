/**
 * External dependencies
 */
import React, { useEffect } from 'react';
import { useSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import QrCode from './template';
import { QrCode as QrCodeIcon } from '@moderntribe/events/icons';

const { __ } = wp.i18n;
const { InnerBlocks } = wp.blockEditor;

/**
 * Module Code
 */
export default {
	id: 'qr-code',
	title: __( 'QR Code', 'the-events-calendar' ),
	description: __( 'Display a QR code for an event.', 'the-events-calendar' ),
	icon: <QrCodeIcon />,
	category: 'tribe-events',
	keywords: [ 'event', 'qr code', 'events-gutenberg', 'tribe' ],
	example: {},

	edit: ( props ) => {
		const { attributes, setAttributes } = props;

		// Handle dependent dropdown visibility
		useEffect( () => {
			const handleDependentDropdowns = () => {
				const dependents = document.querySelectorAll( '.tribe-widget-form-control--dropdown[data-depends]' );

				dependents.forEach( ( dependent ) => {
					const dependsOn = dependent.dataset.depends;
					const condition = dependent.dataset.condition;

					const parent = document.querySelector( dependsOn );

					if ( parent ) {
						const updateVisibility = () => {
							if ( parent.value === condition ) {
								dependent.classList.remove( 'hidden' );
							} else {
								dependent.classList.add( 'hidden' );
							}
						};

						// Initial check
						updateVisibility();

						// Listen for changes
						parent.addEventListener( 'change', updateVisibility );
					}
				} );
			};

			// Create a MutationObserver to watch for when the widget form is added to the DOM
			const observer = new MutationObserver( ( mutations ) => {
				mutations.forEach( ( mutation ) => {
					if ( mutation.addedNodes.length ) {
						// Check if any of the added nodes contain our dependent dropdowns
						const hasDependentDropdowns = Array.from( mutation.addedNodes ).some( node => {
							if ( node.querySelector ) {
								return node.querySelector( '.tribe-widget-form-control--dropdown[data-depends]' );
							}
							return false;
						});

						if ( hasDependentDropdowns ) {
							handleDependentDropdowns();
						}
					}
				} );
			} );

			// Start observing the document body for changes
			observer.observe( document.body, {
				childList: true,
				subtree: true
			} );

			// Also try initial check in case elements are already there
			handleDependentDropdowns();

			// Cleanup
			return () => {
				observer.disconnect();
				document.querySelectorAll( '.tribe-widget-form-control--dropdown[data-depends]' ).forEach( ( dependent ) => {
					const parent = document.querySelector( dependent.dataset.depends );
					if ( parent ) {
						parent.removeEventListener( 'change', updateVisibility );
					}
				} );
			};
		}, [] );

		return <QrCode { ...props } />;
	},
	save: () => <InnerBlocks.Content />,
};
