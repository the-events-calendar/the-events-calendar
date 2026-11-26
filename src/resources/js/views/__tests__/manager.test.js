describe( 'Views manager', () => {
	const viewMarkup = `
		<div data-js="tribe-events-view" data-view="printed">
			<script data-js="tribe-events-view-data" type="application/json">{ "slug": "month" }</script>
		</div>
	`;

	beforeAll( () => {
		global.tribe = {};
		require( '../manager' );
	} );

	afterAll( () => {
		delete global.tribe;
	} );

	afterEach( () => {
		jQuery( document ).off( 'afterSetup.tribeEvents' );
		document.body.innerHTML = '';
	} );

	test( 'selects only the views the plugin printed', () => {
		jQuery( document.body ).append( `
			${ viewMarkup }
			<div data-js="tribe-events-view" data-view="no-data"></div>
			<div data-js="tribe-events-view" data-view="span-data">
				<span data-js="tribe-events-view-data">{}</span>
			</div>
		` );

		const $containers = tribe.events.views.manager.selectContainers();

		expect( $containers.map( ( index, container ) => container.dataset.view ).get() ).toEqual( [ 'printed' ] );
	} );

	test( 'does not select an element that only wraps a printed view', () => {
		jQuery( document.body ).append( `<div data-js="tribe-events-view" data-view="wrapper">${ viewMarkup }</div>` );

		const $containers = tribe.events.views.manager.selectContainers();

		expect( $containers.map( ( index, container ) => container.dataset.view ).get() ).toEqual( [ 'printed' ] );
	} );

	test( 'does not set up a container that carries no view data', () => {
		const setUp = [];
		jQuery( document ).on( 'afterSetup.tribeEvents', ( event, index, $container ) => {
			setUp.push( $container.attr( 'data-view' ) );
		} );
		jQuery( document.body ).append( `${ viewMarkup }<div data-js="tribe-events-view" data-view="no-data"></div>` );

		jQuery( '[data-js="tribe-events-view"]' ).each( tribe.events.views.manager.setup );

		expect( setUp ).toEqual( [ 'printed' ] );
	} );
} );
