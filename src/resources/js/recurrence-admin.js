/**
 * Enhance native WordPress rows without replacing their title or action callbacks.
 * Delegated tooltip events and a scoped observer also cover Quick Edit replacements.
 *
 * @param {HTMLElement|null} list Native WordPress list body.
 * @return {Function|undefined} Removes observers and event listeners.
 */
export const initRowIdentity = ( list ) => {
	if ( ! list ) {
		return;
	}
	const listeners = [];
	const listen = ( target, type, callback, options ) => {
		target.addEventListener( type, callback, options );
		listeners.push( () => target.removeEventListener( type, callback, options ) );
	};
	const triggerSelector = '.tec-occurrence-admin__trigger';
	let active = null;
	let pinned = false;
	let hideTimer;

	const position = () => {
		if ( ! active ) {
			return;
		}
		const tooltip = active.nextElementSibling;
		const rect = active.getBoundingClientRect();
		const width = tooltip.getBoundingClientRect().width;
		const height = tooltip.getBoundingClientRect().height;
		const center = rect.left + rect.width / 2;
		const left = Math.max( 8, Math.min( center - width / 2, window.innerWidth - width - 8 ) );
		const above = rect.bottom + height + 10 > window.innerHeight - 8;
		const top = above ? rect.top - height - 10 : rect.bottom + 10;
		tooltip.dataset.placement = above ? 'above' : 'below';
		tooltip.style.setProperty(
			'--tec-popover-arrow-left',
			`${ Math.max( 12, Math.min( center - left, width - 12 ) ) }px`
		);
		tooltip.style.left = `${ left }px`;
		tooltip.style.top = `${ Math.max( 8, Math.min( top, window.innerHeight - height - 8 ) ) }px`;
	};

	const close = () => {
		clearTimeout( hideTimer );
		active?.parentElement.classList.remove( 'is-open' );
		active = null;
		pinned = false;
	};

	const open = ( trigger ) => {
		clearTimeout( hideTimer );
		if ( active !== trigger ) {
			close();
		}
		active = trigger;
		active.parentElement.classList.add( 'is-open' );
		position();
	};

	const mount = () => {
		if ( active && ! list.contains( active ) ) {
			close();
		}
		list.querySelectorAll( '.tec-occurrence-admin__identity:not([data-mounted])' ).forEach( ( identity ) => {
			identity.dataset.mounted = 'true';
			const link = identity.closest( 'tr' )?.querySelector( '.row-title' );
			if ( link ) {
				const ids = new Set(
					( link.getAttribute( 'aria-describedby' ) || '' ).split( /\s+/ ).filter( Boolean )
				);
				ids.add( identity.dataset.description );
				link.setAttribute( 'aria-describedby', [ ...ids ].join( ' ' ) );
			}
		} );
	};

	mount();
	const observer = new window.MutationObserver( mount );
	observer.observe( list, { childList: true, subtree: true } );

	listen( list, 'pointerover', ( event ) => {
		const trigger = event.target.closest( triggerSelector );
		if ( trigger && event.pointerType !== 'touch' && ! trigger.parentElement.contains( event.relatedTarget ) ) {
			open( trigger );
		} else if ( active?.parentElement.contains( event.target ) ) {
			clearTimeout( hideTimer );
		}
	} );
	listen( list, 'pointerout', ( event ) => {
		if (
			active?.parentElement.contains( event.target ) &&
			! active.parentElement.contains( event.relatedTarget ) &&
			! pinned &&
			list.ownerDocument.activeElement !== active
		) {
			hideTimer = setTimeout( close, 120 );
		}
	} );
	listen( list, 'focusin', ( event ) => {
		const trigger = event.target.closest( triggerSelector );
		if ( trigger ) {
			open( trigger );
		}
	} );
	listen( list, 'focusout', ( event ) => {
		if (
			active?.parentElement.contains( event.target ) &&
			! active.parentElement.contains( event.relatedTarget )
		) {
			close();
		}
	} );
	listen( document, 'click', ( event ) => {
		const trigger = event.target.closest( triggerSelector );
		if ( trigger && list.contains( trigger ) ) {
			if ( active === trigger && pinned ) {
				close();
			} else {
				open( trigger );
				pinned = true;
			}
		} else if ( ! active?.parentElement.contains( event.target ) ) {
			close();
		}
	} );
	listen( document, 'keydown', ( event ) => {
		if ( event.key === 'Escape' ) {
			close();
		}
	} );
	listen( window, 'resize', position );
	listen( window, 'scroll', position, true );
	return () => {
		close();
		observer.disconnect();
		listeners.forEach( ( remove ) => remove() );
	};
};

initRowIdentity( document.getElementById( 'the-list' ) );
