/**
 * External dependencies
 */
import { stringify } from 'querystringify';
import { values, noop, isArray, isEmpty, get } from 'lodash';
import classNames from 'classnames';
import PropTypes from 'prop-types';
import React from 'react';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

import { Component } from '@wordpress/element';

import {
	Spinner,
} from '@wordpress/components';

/**
 * Internal dependencies
 */
import './style.pcss';
import { google, mapsAPI } from '@moderntribe/common/utils/globals';

/**
 * Module Code
 */
const IMAGE_FORMATS = {
	// png8 or png (default) specifies the 8-bit PNG format.
	PNG: 'png',

	// png32 specifies the 32-bit PNG format.
	PNG32: 'png32',

	// gif specifies the GIF format.
	GIF: 'gif',

	// jpg specifies the JPEG compression format.
	JPG: 'jpg',

	// jpg-baseline specifies a non-progressive JPEG compression format.
	JPG_BASELINE: 'jpg-baseline',
};

const MAP_TYPES = {
	// roadmap (default) specifies a standard roadmap image, as is normally shown on the Google Maps website.
	ROADMAP: 'roadmap',

	// satellite specifies a satellite image.
	SATELLITE: 'satellite',

	// terrain specifies a physical relief map image, showing terrain and vegetation.
	TERRAIN: 'terrain',

	// hybrid specifies a hybrid of the satellite and roadmap image,
	// showing a transparent layer of major streets and place names on the satellite image.
	HYBRID: 'hybrid',
};

const IMAGE_FORMATS_VALUES = values( IMAGE_FORMATS );
const MAP_TYPES_VALUES = values( MAP_TYPES );

const API_KEY = get( mapsAPI(), 'key', '' );
const DEFAULT_ZOOM = parseInt( get( mapsAPI(), 'zoom', 14 ), 10 );

/**
 * A wrapper for Google's Static Maps
 *
 * @see https://developers.google.com/maps/documentation/staticmaps/intro#Overview
 *
 * @example: http://staticmapmaker.com/google/
 */

export default class GoogleMap extends Component {
	static RootStaticUrl = 'https://maps.googleapis.com/maps/api/staticmap';
	static RootEmbedUrl = 'https://www.google.com/maps/embed/v1/place';

	static ImageFormats = IMAGE_FORMATS

	static MapTypes = MAP_TYPES

	static propTypes = {
		coordinates: PropTypes.object.isRequired,
		address: PropTypes.string,
		size: PropTypes.shape( {
			width: PropTypes.number.isRequired,
			height: PropTypes.number.isRequired,
		} ),

		style: PropTypes.object,

		/**
		 * zoom (required if markers not present) defines the zoom level of the map,
		 * which determines the magnification level of the map.
		 *
		 * @see https://developers.google.com/maps/documentation/staticmaps/intro#Zoomlevels
		 */
		zoom: PropTypes.number.isRequired,

		/**
		 * scale affects the number of pixels that are returned.
		 * scale=2 returns twice as many pixels as scale=1 while retaining the same coverage area and level of detail
		 * The default value is calculated from the screen PixelRatio.
		 */
		scale: PropTypes.number,

		/**
		 * @see https://developers.google.com/maps/documentation/staticmaps/intro#ImageFormats
		 */
		format: PropTypes.oneOf( IMAGE_FORMATS_VALUES ),

		/**
		 * @see https://developers.google.com/maps/documentation/staticmaps/intro#MapTypes
		 */
		mapType: PropTypes.oneOf( MAP_TYPES_VALUES ),

		/**
		 * Add a marker on the center
		 */
		hasCenterMarker: PropTypes.bool,
		apiKey: PropTypes.string,
	}

	static defaultProps = {
		format: IMAGE_FORMATS.JPG,
		mapType: MAP_TYPES.ROADMAP,
		hasCenterMarker: true,
		style: {},
		scale: 2,
		interactive: false,
		apiKey: API_KEY,
		zoom: DEFAULT_ZOOM,
		coordinates: {},
		address: {},
	}

	constructor( props ) {
		super( ...arguments );

		this.state = {
			...props,
			error: '',
			isLoading: true,
			rendered: false,
		};

		this.interactiveMapContainer = React.createRef();
		this.map = {
			instance: null,
			marker: null,
		};
		this.interval = noop;
		this.tries = 0;
		this.MAX_TRIES = 5;
	}

	/**
	 * @todo  We need to not do this Logic over in this template
	 * @see   https://github.com/moderntribe/events-gutenberg/pull/327#discussion_r219090823
	 */
	static getDerivedStateFromProps( nextProps, prevState ) {

		// return if it was rendered already
		if ( true === prevState.rendered ) {
			return null;
		}

		// return if the coordinates haven't changed
		if (
			nextProps.coordinates.lat === prevState.coordinates.lat
			&& nextProps.coordinates.lng === prevState.coordinates.lng
		) {
			return null;
		}

		return { rendered: true, loadingMap: false };

	}

	componentDidMount() {
		this.loadMap();
	}

	loadMap() {
		if ( ! google() ) {
			this.tryAgain();
			return;
		}

		const { maps } = google();
		// Try to fetch the library 0.5 seconds later
		if ( ! maps ) {
			this.tryAgain();
			return;
		}

		// There's no valid coordinatees fallback to the image map.
		if ( this.invalidLocation() ) {
			const { address } = this.props;

			if ( isEmpty( address ) ) {
				this.setState( {
					interactive: false,
					isLoading: false,
					error: __(
						'The map does not have valid coordinates nor a valid address',
						'the-events-calendar',
					),
				} );
				return;
			}

			this.setState( {
				interactive: false,
				isLoading: false,
			} );
			return;
		}

		this.setState( {
			isLoading: false,
			interactive: true,
		}, this.attachInteractiveMap );
	}

	getMapConfig() {
		const {
			zoom,
			mapType,
		} = this.props;

		const type = isArray( mapType ) ? mapType : [ mapType ];

		return {
			center: this.getLocation(),
			zoom: zoom,
			mapTypeControl: type.length > 1,
			mapTypeControlOptions: {
				mapTypeIds: type,
			},
			streetViewControl: false,
			fullscreenControl: false,
		};
	}

	invalidLocation() {
		const location = this.getLocation();
		const { lat, lng } = location;
		return ! lat || ! lng;
	}

	getLocation() {
		const { coordinates } = this.props;
		const { lat, lng } = coordinates;

		return {
			lat,
			lng,
		};
	}

	tryAgain = () => {
		if ( this.interval ) {
			clearInterval( this.interval );
		}

		if ( this.tries >= this.MAX_TRIES ) {
			this.setState( {
				isLoading: false,
				error: __( 'Make sure Google Maps Library is included on this page.', 'the-events-calendar' ),
			} );
			return;
		}

		this.interval = setInterval( () => {
			this.loadMap();
		}, 500 );

		this.tries += 1;
	}

	render() {
		const { isLoading, rendered, loadingMap } = this.state;
		const containerClass = classNames( 'tribe-editor__map', {
			'tribe-editor__map--loading': isLoading,
		} );

		let renderMap = this.renderMap();

		if (
			true === rendered
			&& true !== loadingMap
		) {
			renderMap = this.renderMapUpdate();
		}

		return (
			<div className={ containerClass }>
				{ renderMap }
			</div>
		);
	}

	renderMapUpdate() {
		this.setState( { loadingMap: true } );
		this.loadMap();
		return this.renderMap();
	}

	renderMap() {
		const { isLoading, error, interactive, apiKey, rendered } = this.state;


		if ( isLoading ) {
			return <Spinner />;
		}

		if ( error ) {
			return ( <h4>{ error }</h4> );
		}

		if ( ! apiKey ) {
			return (
				<h4> { __( 'A Google Map API KEY is required to view the map', 'the-events-calendar' ) }</h4>
			);
		}

		if ( rendered ) {
			return this.renderInteractive();
		}

		if ( interactive ) {
			return this.renderInteractive();
		}

		return this.renderImage();
	}

	renderImage() {
		return (
			<picture className="tribe-editor__map--static">
				<img
					className="tribe-element-map-object"
					alt="map"
					src={ this.mapUrl }
				/>
				<div className="trie-editor__spinner__container">
					<Spinner />
				</div>
			</picture>
		);
	}

	renderInteractive() {
		return (
			<section className="tribe-editor__map--interactive">
				<div className="tribe-editor__map--dynamic" ref={ this.interactiveMapContainer }>
				</div>
				<div className="trie-editor__spinner__container">
					<Spinner />
				</div>
			</section>
		);
	}

	attachInteractiveMap = () => {
		const { interactive } = this.state;
		const { interactiveMapContainer, map } = this;

		if ( ! interactive || ! interactiveMapContainer.current ) {
			return this.renderImage();
		}

		const { maps } = google();

		map.instance = new maps.Map(
			interactiveMapContainer.current,
			this.getMapConfig()
		);

		if ( map.instance ) {
			map.marker = new maps.Marker( {
				position: this.getLocation(),
				map: map.instance,
			} );
		}
	}

	get mapUrl() {
		const {
			zoom,
			size,
			scale,
			format,
			mapType,
			apiKey,
			address,
		} = this.props;

		const { width, height } = size;

		const queryArgs = {
			zoom: zoom,
			maptype: mapType,
			key: apiKey,
		};
		let rootUrl = null;
		const { interactive } = this.state;

		const coordinates = this.getLocation();
		const { lat, lng } = coordinates;
		if ( interactive ) {
			rootUrl = this.constructor.RootEmbedUrl;

			queryArgs.q = `${ lat },${ lng }`;
		} else {
			rootUrl = this.constructor.RootStaticUrl;

			queryArgs.scale = scale;
			queryArgs.size = `${ width }x${ height }`;
			queryArgs.format = format;

			const invalid = this.invalidLocation();
			if ( invalid && ! isEmpty( address ) ) {
				queryArgs.center = address;
			} else {
				queryArgs.center = `${ lat },${ lng }`;
			}
			queryArgs.markers = this.markerParams;
		}

		return `${ rootUrl }?${ stringify( queryArgs ) }`;
	}

	get markerParams() {
		const { hasCenterMarker, address } = this.props;

		const coordinates = this.getLocation();
		const { lat, lng } = coordinates;

		const invalid = this.invalidLocation();
		const marker = invalid ? address : `${ lat },${ lng }`;
		const markerParams = `size:mid|color:0xff0000|label:|${ marker }`;
		return hasCenterMarker ? markerParams : '';
	}
}
