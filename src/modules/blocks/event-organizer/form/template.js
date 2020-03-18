/**
 * External dependencies
 */
import React from 'react';
import PropTypes from 'prop-types';
import { noop, omit } from 'lodash';
import validator from 'validator';

/**
 * WordPress dependencies
 */
import { Component } from '@wordpress/element';
import { RichText } from '@wordpress/editor';
import { __ } from '@wordpress/i18n';

import './style.pcss';

/**
 * Internal dependencies
 */

export default class OrganizerForm extends Component {
	static defaultProps = {
		title: '',
		phone: '',
		website: '',
		email: '',
		submit: noop,
	};

	static propTypes = {
		title: PropTypes.string,
		email: PropTypes.string,
		website: PropTypes.string,
		phone: PropTypes.string,
		submit: PropTypes.func,
	};

	constructor( props ) {
		super( ...arguments );
		this.state = omit( props, [ 'submit' ] );
	}

	componentWillUnmount() {
		const fields = {
			...this.state,
		};

		if ( fields.email && ! validator.isEmail( fields.email ) ) {
			fields.email = '';
		}

		if ( fields.website && ! validator.isURL( fields.website ) ) {
			fields.website = '';
		}

		this.props.submit( fields );
	}

	render() {
		const { title, email, website, phone } = this.state;
		return (
			<section className="tribe-editor__organizer__form">
				<div className="tribe-editor__organizer__fields">
					<RichText
						tagName="h3"
						format="string"
						value={ title }
						onChange={ this.saveField( 'title' ) }
						formattingControls={ [] }
					/>
					<input
						type="tel"
						name="phone"
						value={ phone }
						placeholder={ __( 'Add Phone', 'the-events-calendar' ) }
						onChange={ this.saveEventField( 'phone' ) }
					/>
					<input
						type="url"
						name="website"
						value={ website }
						placeholder={ __( 'Add website', 'the-events-calendar' ) }
						onChange={ this.saveEventField( 'website' ) }
					/>
					<input
						type="email"
						name="email"
						value={ email }
						placeholder={ __( 'Add email', 'the-events-calendar' ) }
						onChange={ this.saveEventField( 'email' ) }
					/>
				</div>
			</section>
		);
	}

	saveField = ( name ) => {
		return ( value ) => {
			this.setState( { [ name ]: value } );
		};
	};

	saveEventField = ( name ) => {
		return ( event ) => {
			return this.saveField( name )( this.getValue( event ) );
		};
	};

	getValue = ( event = {} ) => {
		const { target } = event;
		return target.value;
	};
}
