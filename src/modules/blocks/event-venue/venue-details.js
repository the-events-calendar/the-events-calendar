/**
 * External dependencies
 */
import React, { Fragment } from 'react';
import { isEmpty, noop } from 'lodash';
import { mapLink } from '@moderntribe/events/editor/utils/geo-data';
import { decode } from 'he';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Component } from '@wordpress/element';

import {
	Spinner,
	Placeholder,
} from '@wordpress/components';

export default class VenueDetails extends Component {
	static defaultProps = {
		beforeTitle: null,
		afterTitle: null,
		maybeEdit: noop,
	};

	constructor() {
		super( ...arguments );

		this.state = {
			isLoading: false,
		};
	}

	render() {
		const { venue } = this.props;
		const { isLoading } = this.state;

		if ( isLoading ) {
			return (
				<Placeholder key="loading">
					<Spinner />
				</Placeholder>
			);
		}

		if ( venue ) {
			return this.renderVenue();
		}

		return null;
	}

	renderVenue = () => {
		const { venue } = this.props;

		return (
			<div
				className="tribe-editor__venue--current"
				key={ venue.id }
			>
				{ this.renderVenueName() }
				{ this.renderAddress() }
				{ this.renderPhone() }
				{ this.renderWebsite() }
			</div>
		);
	}

	renderVenueName() {
		const { beforeTitle, afterTitle, maybeEdit } = this.props;
		return (
			<div className="tribe-editor__venue__name">
				{ beforeTitle }
				<h3 className="tribe-editor__venue__name-heading" onClick={ maybeEdit }>
					{ decode( this.getVenueName() ) }
				</h3>
				{ afterTitle }
			</div>
		);
	}

	getVenueName( venue = this.props.venue ) {
		// if we still don't have venue we don't have an address
		const { title = {} } = venue;
		const { rendered = __( '(Untitled Venue)', 'the-events-calendar' ) } = title;
		return rendered;
	}

	renderAddress() {
		const { address = {} } = this.props;
		if ( isEmpty( address ) ) {
			return null;
		}

		const {
			city,
			street,
			province,
			zip,
			country,
		} = address;

		return (
			<address className="tribe-editor__venue__address">
				<span className="tribe-venue__street-address">{ street }</span>
				{
					city && (
						<Fragment>
							<br />
							<span className="tribe-venue__locality">{ city }</span>
						</Fragment>
					)
				}
				{ city && <span className="tribe-venue__delimiter">, </span> }
				{ province && <span className="tribe-venue__region">{ province }</span> }
				{ zip && <span className="tribe-venue__postal-code"> { zip }</span> }
				{
					country && (
						<Fragment>
							<br />
							<span className="tribe-venue__country-name"> { country }</span>
						</Fragment>
					)
				}
				{ this.renderGoogleMapLink() }
			</address>
		);
	}

	renderGoogleMapLink() {
		const { showMapLink, address } = this.props;

		if ( ! showMapLink ) {
			return null;
		}

		return (
			<Fragment>
				<br />
				<a
					href={ mapLink( address ) }
					title={ __( 'Click to view a Google Map', 'the-events-calendar' ) }
					target="_blank"
					rel="noopener noreferrer"
				>
					{ __( '+ Google Map', 'the-events-calendar' ) }
				</a>
			</Fragment>
		);
	}

	renderPhone() {
		const { venue } = this.props;

		if ( isEmpty( venue.meta._VenuePhone ) ) {
			return null;
		}

		return (
			<React.Fragment>
				<span className="tribe-editor__venue__phone">{ venue.meta._VenuePhone }</span>
				<br />
			</React.Fragment>
		);
	}

	renderWebsite() {
		const { venue } = this.props;
		if ( isEmpty( venue.meta._VenueURL ) ) {
			return null;
		}

		return (
			<React.Fragment>
				<span className="tribe-editor__venue__website">{ venue.meta._VenueURL }</span>
				<br />
			</React.Fragment>
		);
	}
}
