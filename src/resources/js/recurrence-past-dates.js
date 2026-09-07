/** Load older date chips without submitting or changing the event. */
document.addEventListener( 'click', async ( event ) => {
	const button = event.target.closest( '.tec-events-recurrence-dates__load' );
	if ( ! button || button.disabled ) {
		return;
	}
	const data = JSON.parse( button.dataset.pastDates );
	const list = button.parentElement.querySelector( '.tec-events-recurrence-dates__chips--past' );
	const status = button.parentElement.querySelector( '.tec-events-recurrence-dates__load-status' );
	button.disabled = true;
	list.setAttribute( 'aria-busy', 'true' );
	status.textContent = window.tecPastDates.loading;
	try {
		const url = new URL( data.url );
		url.searchParams.set( 'offset', data.next );
		url.searchParams.set( 'as_of', data.asOf );
		const response = await fetch( url, { headers: { 'X-WP-Nonce': data.nonce }, credentials: 'same-origin' } );
		if ( ! response.ok ) {
			throw new Error( 'Request failed' );
		}
		const page = await response.json();
		page.dates.forEach( ( chip ) => {
			const item = list.firstElementChild.cloneNode( true );
			const link = item.querySelector( '.tec-events-recurrence-dates__chip' );
			const edit = item.querySelector( '.tec-events-recurrence-dates__chip-edit' );
			const tip = item.querySelector( '[role="tooltip"]' );
			tip.id = `${ list.id }-loaded-${ list.children.length }`;
			link.textContent = chip.label;
			if ( chip.permalink ) {
				link.setAttribute( 'href', chip.permalink );
			} else {
				link.removeAttribute( 'href' );
			}
			link.setAttribute( 'aria-describedby', tip.id );
			edit.href = chip.edit_link;
			edit.setAttribute( 'aria-label', `${ window.tecPastDates.edit } ${ chip.label }` );
			tip.replaceChildren();
			chip.tooltip.forEach( ( line ) => {
				const span = document.createElement( 'span' );
				span.className = 'tec-events-recurrence-dates__chip-tooltip-line';
				span.textContent = line;
				tip.appendChild( span );
			} );
			list.appendChild( item );
		} );
		data.next = page.next;
		button.dataset.pastDates = JSON.stringify( data );
		button.hidden = page.next === null;
		status.textContent = page.next === null ? window.tecPastDates.complete : window.tecPastDates.loaded;
	} catch ( error ) {
		status.textContent = window.tecPastDates.error;
	} finally {
		button.disabled = false;
		list.removeAttribute( 'aria-busy' );
	}
} );
