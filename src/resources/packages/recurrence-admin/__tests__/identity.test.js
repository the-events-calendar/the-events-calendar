import { initRowIdentity } from '../../../js/recurrence-admin';

const row = ( id = 1 ) => `<tr><td class="column-title"><strong><a class="row-title" href="?post=10001056" aria-describedby="existing">Event title</a></strong><div class="row-actions"><a href="?post=425">Edit event details</a></div></td><td class="column-tec-identity"><span class="tec-occurrence-admin__identity" data-description="description-${ id }"><span id="description-${ id }">Occurrence</span><span class="tec-occurrence-admin__indicator"><button class="tec-occurrence-admin__trigger" aria-describedby="tip-${ id }">Schedule</button><span class="tec-occurrence-admin__tooltip" id="tip-${ id }" role="tooltip">Schedule explanation</span></span></span></td></tr>`;
let cleanup;
let list;
let triggers;

beforeEach( () => {
	document.body.innerHTML = `<table><tbody id="the-list">${ row() }${ row( 3 ) }</tbody></table><button id="outside">Outside</button>`;
	list = document.getElementById( 'the-list' );
	cleanup = initRowIdentity( list );
	triggers = list.querySelectorAll( '.tec-occurrence-admin__trigger' );
} );

afterEach( () => {
	cleanup();
	document.body.innerHTML = '';
} );

test( 'preserves native titles, descriptions and actions while removing the separate line', async () => {
	const heading = list.querySelector( '.column-title' );
	expect( heading.querySelector( '.tec-occurrence-admin__identity' ) ).toBeNull();
	expect( list.querySelector( '.tec-occurrence-admin__identity' ).parentElement.className ).toBe( 'column-tec-identity' );
	expect( heading.querySelector( 'a' ).getAttribute( 'href' ) ).toBe( '?post=10001056' );
	expect( heading.querySelector( 'a' ).getAttribute( 'aria-describedby' ) ).toBe( 'existing description-1' );
	expect( list.querySelector( '.row-actions a' ).getAttribute( 'href' ) ).toBe( '?post=425' );
	list.innerHTML = row( 2 );
	await Promise.resolve();
	expect( list.querySelectorAll( '.column-tec-identity .tec-occurrence-admin__identity[data-mounted]' ) ).toHaveLength( 1 );
	expect( list.querySelector( '.row-title' ).getAttribute( 'aria-describedby' ) ).toBe( 'existing description-2' );
	await Promise.resolve();
	expect( list.querySelectorAll( '.column-tec-identity .tec-occurrence-admin__identity[data-mounted]' ) ).toHaveLength( 1 );
} );

test( 'shows only the focused row explanation and Escape dismisses without moving focus', () => {
	triggers[ 0 ].focus();
	expect( triggers[ 0 ].parentElement.classList.contains( 'is-open' ) ).toBe( true );
	expect( triggers[ 1 ].parentElement.classList.contains( 'is-open' ) ).toBe( false );
	document.dispatchEvent( new KeyboardEvent( 'keydown', { key: 'Escape', bubbles: true } ) );
	expect( list.querySelector( '.is-open' ) ).toBeNull();
	expect( document.activeElement ).toBe( triggers[ 0 ] );
	triggers[ 1 ].focus();
	expect( triggers[ 1 ].parentElement.classList.contains( 'is-open' ) ).toBe( true );
} );

test( 'supports tap toggling and outside dismissal', () => {
	triggers[ 1 ].focus();
	triggers[ 1 ].click();
	expect( triggers[ 1 ].parentElement.classList.contains( 'is-open' ) ).toBe( true );
	triggers[ 1 ].click();
	expect( list.querySelector( '.is-open' ) ).toBeNull();
	triggers[ 0 ].click();
	document.getElementById( 'outside' ).click();
	expect( list.querySelector( '.is-open' ) ).toBeNull();
} );

test( 'keeps tooltips within the viewport and hoverable', () => {
	jest.useFakeTimers();
	const tooltip = triggers[ 0 ].nextElementSibling;
	triggers[ 0 ].getBoundingClientRect = () => ( { left: 1000, width: 24, top: 740, bottom: 764 } );
	tooltip.getBoundingClientRect = () => ( { width: 280, height: 80 } );
	triggers[ 0 ].dispatchEvent( new MouseEvent( 'pointerover', { bubbles: true } ) );
	expect( tooltip.style.left ).toBe( '736px' );
	expect( tooltip.style.top ).toBe( '650px' );
	expect( tooltip.dataset.placement ).toBe( 'above' );
	expect( tooltip.style.getPropertyValue( '--tec-popover-arrow-left' ) ).toBe( '268px' );
	triggers[ 0 ].getBoundingClientRect = () => ( { left: 10, width: 24, top: -100, bottom: -76 } );
	window.dispatchEvent( new Event( 'scroll' ) );
	expect( tooltip.style.top ).toBe( '8px' );
	triggers[ 0 ].dispatchEvent( new MouseEvent( 'pointerout', { bubbles: true, relatedTarget: tooltip } ) );
	jest.runAllTimers();
	expect( triggers[ 0 ].parentElement.classList.contains( 'is-open' ) ).toBe( true );
	tooltip.dispatchEvent( new MouseEvent( 'pointerout', { bubbles: true, relatedTarget: document.body } ) );
	jest.runAllTimers();
	expect( list.querySelector( '.is-open' ) ).toBeNull();
	jest.useRealTimers();
} );
