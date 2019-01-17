/**
 * External dependencies
 */
import { get, values, noop, pick } from 'lodash';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { select } from '@wordpress/data';
import { Component } from '@wordpress/element';
import { RichText } from '@wordpress/editor';

/**
 * Internal dependencies
 */
import { Input } from '@moderntribe/events/elements';
import list, { getCountries, getStates, getCountryCode, getStateCode } from '@moderntribe/events/editor/utils/geo-data';
import { setDefault, getVenueCountry, getVenueStateProvince } from '@moderntribe/events/data/blocks/venue/utils';
import { editorDefaults } from '@moderntribe/common/utils/globals';
import './style.pcss';

export function toFields( venue ) {
	const title = get( venue, 'title', {} );
	const meta = get( venue, 'meta', {} );
	const address = setDefault( get( meta, '_VenueAddress', '' ), editorDefaults().venueAddress );
	const city = setDefault( get( meta, '_VenueCity', '' ), editorDefaults().venueCity );
	const country = getVenueCountry( meta );
	const stateProvince = getVenueStateProvince( meta );
	const zip = setDefault( get( meta, '_VenueZip', '' ), editorDefaults().venueZip );
	const phone = setDefault( get( meta, '_VenuePhone', '' ), editorDefaults().venuePhone );
	const url = get( meta, '_VenueURL', '' );

	const countryCode = getCountryCode( country );
	return {
		title: get( title, 'rendered', '' ),
		address,
		city,
		country: countryCode,
		zip,
		phone,
		url,
		stateProvince: getStateCode( countryCode, stateProvince ),
	};
}

export function toVenue( fields ) {
	const { title, address, city, country, zip, phone, url, stateProvince } = fields;

	return {
		title,
		status: 'draft',
		meta: {
			_VenueAddress: address,
			_VenueCity: city,
			_VenueCountry: get( list.countries, country, '' ) || country,
			_VenueProvince: get( list.us_states, stateProvince, '' ) || stateProvince,
			_VenueZip: zip,
			_VenuePhone: phone,
			_VenueURL: url,
			_VenueStateProvince: stateProvince,
		},
	}
}

/**
 * Module Code
 */

export default class VenueForm extends Component {
	static defaultProps = {
		onSubmit: noop,
	};

	constructor( props ) {
		super( ...arguments );

		this.state = {
			title: '',
			address: '',
			city: '',
			country: '',
			zip: '',
			phone: '',
			url: '',
			stateProvince: '',
			...props,
		};
		this.fields = {};
	}

	componentWillUnmount() {
		const FIELDS = [
			'title', 'address', 'city', 'country', 'zip', 'phone', 'url', 'stateProvince',
		];
		const fields = pick( this.state, FIELDS );
		fields.country = get( list.countries, fields.country, '' ) || fields.country;
		fields.stateProvince = get( list.us_states, fields.stateProvince, '' ) || fields.stateProvince;
		this.props.onSubmit( fields );
	}

	saveRef = ( input ) => {
		if ( input ) {
			const { props } = input;
			const { name } = props || {};
			this.fields[ name ] = input;
		}
	}

	renderOption( element ) {
		return ( <option value={ element.code } key={ element.code }>
			{ element.name }
		</option> );
	}

	renderCountry() {
		const { country } = this.state;
		const placeholder = country ? null : (
			<option value="" disabled>
				{ __( 'Country', 'the-events-calendar' ) }
			</option>
		);

		return (
			<select
				value={ country }
				className="small tribe-editor__venue__select"
				onChange={ ( event ) => this.setState( { country: event.target.value } ) }
			>
				{ placeholder }
				{ getCountries().map( this.renderOption ) }
			</select>
		);
	}

	renderState() {
		const { stateProvince, country } = this.state;
		const states = getStates( country );

		if ( states.length === 0 ) {
			return (
				<Input
					className="medium"
					type="text"
					name="venue[stateProvince]"
					placeholder="State"
					ref={ this.saveRef }
					onChange={ ( event ) => this.setState( { stateProvince: event.target.value } ) }
					value={ stateProvince }
				/>
			);
		}
		delete this.fields[ 'venue[stateProvince]' ];
		return (
			<select
				value={ stateProvince }
				onChange={ ( event ) => this.setState( { stateProvince: event.target.value } ) }
				className="medium tribe-editor__venue__select"
			>
				{ states.map( this.renderOption ) }
			</select>
		);
	}

	render() {
		const {
			title,
			address,
			city,
			zip,
			phone,
			url,
		} = this.state;

		return (
			<div
				className="tribe-editor__venue__form"
				key="tribe-venue-form"
			>
				<RichText
					tagName="h3"
					format="string"
					value={ title }
					onChange={ ( value ) => { this.setState( { title: value } ) } }
					formattingControls={ [] }
				/>
				<div className="tribe-editor__venue__fields">
					<Input
						type="text"
						name="venue[address]"
						placeholder="Street Address"
						ref={ this.saveRef }
						value={ address }
						onChange={ ( next ) => this.setState( { address: next.target.value } ) }
					/>
					<Input
						type="text"
						name="venue[city]"
						placeholder="City"
						ref={ this.saveRef }
						onChange={ ( next ) => this.setState( { city: next.target.value } ) }
						value={ city }
					/>
					<div className="row">
						{ this.renderCountry() }
						{ this.renderState() }
					</div>
					<div className="row">
						<Input
							className="small"
							type="text"
							name="venue[zip]"
							placeholder="ZIP"
							ref={ this.saveRef }
							onChange={ ( next ) => this.setState( { zip: next.target.value } ) }
							value={ zip }
						/>
					</div>
					<Input
						type="tel"
						name="venue[phone]"
						placeholder="Phone number"
						ref={ this.saveRef }
						onChange={ ( next ) => this.setState( { phone: next.target.value } ) }
						value={ phone }
					/>
					<Input
						type="url"
						name="venue[url]"
						placeholder="Website"
						ref={ this.saveRef }
						onChange={ ( next ) => this.setState( { url: next.target.value } ) }
						value={ url }
					/>
				</div>
			</div>
		);
	}
}
