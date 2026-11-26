const fs = require( 'fs' );
const path = require( 'path' );

/*
 * Tooltipster ships as a vendored UMD bundle in common, not as an npm package. Evaluating it with
 * `module` and `define` undefined takes its browser branch, which attaches it to the jQuery passed in.
 */
const loadTooltipster = () => {
	const bundle = fs.readFileSync(
		path.resolve( __dirname, '../../../../../common/vendor/tooltipster/tooltipster.bundle.js' ),
		'utf8'
	);
	new Function( 'module', 'exports', 'define', 'jQuery', bundle )( undefined, undefined, undefined, jQuery ); // eslint-disable-line no-new-func
};

describe( 'Views tooltip', () => {
	const containerSelector = '[data-js="tribe-events-view"]';
	const eventId = 4815;
	const contentId = `tribe-events-tooltip-content-${ eventId }`;

	const buildContainer = ( triggerContent ) => {
		const $container = jQuery( `
			<div data-js="tribe-events-view">
				<a data-js="tribe-events-tooltip" data-tooltip-content="" href="#">Event</a>
				<div class="tribe-events-tooltip__content" id="${ contentId }" role="tooltip">Event details</div>
			</div>
		` );
		$container.find( '[data-js="tribe-events-tooltip"]' ).attr( 'data-tooltip-content', triggerContent );
		jQuery( document.body ).append( $container );

		return $container;
	};

	beforeAll( () => {
		String.prototype.className = function () {
			return this.replace( '.', '' );
		};
		loadTooltipster();
		global.tribe = { events: { views: { manager: { selectors: { container: containerSelector } } } } };
		require( '../tooltip' );
	} );

	afterAll( () => {
		delete String.prototype.className;
		delete global.tribe;
	} );

	afterEach( () => {
		jest.restoreAllMocks();
		document.body.innerHTML = '';
	} );

	test( 'shows the tooltip element the view printed for the trigger', () => {
		const $container = buildContainer( `#${ contentId }` );
		const $trigger = $container.find( '[data-js="tribe-events-tooltip"]' );

		tribe.events.views.tooltip.initTooltips( $container );

		expect( $trigger.tooltipster( 'content' ).get( 0 ) ).toBe( document.getElementById( contentId ) );
	} );

	test( 'never parses the trigger attribute as markup', () => {
		const markup = '<i data-probe="1"></i>';
		const parseHTML = jest.spyOn( jQuery, 'parseHTML' );
		const $container = buildContainer( markup );
		parseHTML.mockClear();

		tribe.events.views.tooltip.initTooltips( $container );

		expect( parseHTML ).not.toHaveBeenCalledWith( markup, expect.anything(), expect.anything() );
		expect( $container.find( '[data-js="tribe-events-tooltip"]' ).hasClass( 'tooltipstered' ) ).toBe( false );
	} );

	test( 'ignores a trigger that points at an element outside its view', () => {
		const outsideId = `${ contentId }-outside`;
		jQuery( document.body ).append( `<div id="${ outsideId }">Not printed by the view</div>` );
		const $container = buildContainer( `#${ outsideId }` );

		tribe.events.views.tooltip.initTooltips( $container );

		expect( $container.find( '[data-js="tribe-events-tooltip"]' ).hasClass( 'tooltipstered' ) ).toBe( false );
	} );

	test( 'tears down a view whose triggers were skipped', () => {
		const $container = buildContainer( '<b>x</b>' );
		tribe.events.views.tooltip.initTooltips( $container );

		expect( () => tribe.events.views.tooltip.deinitTooltips( $container ) ).not.toThrow();
	} );
} );
