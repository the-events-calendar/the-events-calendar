/**
 * External dependencies
 */
import React from 'react';
import { isFunction, values } from 'lodash';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import {
	Spinner,
	Placeholder,
} from '@wordpress/components';
import { Component } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { Input } from '@moderntribe/events/elements';

/**
 * Module Code
 */

class OrganizerForm extends Component {
	static defaultProps = {
		postType: 'tribe_organizer',
	};

	constructor() {
		super( ...arguments );
		this.updateOrganizer = this.updateOrganizer.bind( this );
		this.onSubmit = this.onSubmit.bind( this );

		this.state = {
			title: null,
			phone: '',
			website: '',
			email: '',
			organizer: null,
			isValid: this.isValid(),
		};

		this.fields = {};
	}

	isCreating() {
		if ( ! this.state.organizer ) {
			return false;
		}

		if ( ! isFunction( this.state.organizer.state ) ) {
			return false;
		}

		return 'pending' === this.state.organizer.state();
	}

	onSubmit() {
		const {
			title,
			phone,
			website,
			email,
		} = this.state;

		this.updateOrganizer( {
			title: title,
			// For now every Organizer goes are publish
			status: 'publish',
			meta: {
				_OrganizerEmail: email,
				_OrganizerPhone: phone,
				_OrganizerWebsite: website,
			},
		} );
	}

	updateOrganizer( toSend ) {
		const basePath = wp.api.getPostTypeRoute( this.props.postType );
		const request = wp.apiRequest( {
			path: `/wp/v2/${ basePath }`,
			method: 'POST',
			body: JSON.stringify( toSend ),
		} );

		// Set the organizer state
		this.setState( { organizer: request } );

		request.done( ( newPost ) => {
			if ( ! newPost.id ) {
				console.warning( 'Invalid creation of organizer:', newPost );
			}

			this.props.addOrganizer( newPost );
			this.props.onClose();
		} ).fail( ( err ) => {
			console.error( err );
		} );
	}

	isValid() {
		const fields = values( this.fields );
		const results = fields.filter( ( input ) => input.isValid() );

		return fields.length === results.length;
	}

	focus( name ) {
		return () => {
			const input = this.fields[ name ];
			if ( input ) {
				input.focus();
			}
		};
	}

	saveRef = ( input ) => {
		if ( input ) {
			const { props } = input;
			const { name } = props || {};
			this.fields[ name ] = input;
		}
	}

	render() {
		if ( this.isCreating() ) {
			return [
				<div
					className="tribe-editor__organizer__form"
					key="tribe-organizer-form"
				>
					<Placeholder key="placeholder">
						<Spinner />
					</Placeholder>
				</div>,
			];
		}

		return [
			<div
				className="tribe-editor__organizer__form"
				key="tribe-organizer-form"
			>
				<h3 key="tribe-organizer-form-title">
					{ __( 'Create Organizer' ) }
				</h3>
				<p
					className="description"
				>
					{ __( 'The e-mail address will be obfuscated on your site to avoid it getting harvested by spammers.', 'the-events-calendar' ) }
				</p>
				<dl>
					<dt onClick={ this.focus( 'organizer[name]' ) }>
						{ __( 'Name:', 'the-events-calendar' ) }
						{ ' ' }
					</dt>
					<dd>
						<Input
							type="text"
							ref={ this.saveRef }
							name="organizer[name]"
							onComplete={ () => this.setState( { isValid: this.isValid() } ) }
							onChange={ ( next ) => this.setState( { title: next.target.value } ) }
							validate
						/>
					</dd>
					<dt onClick={ this.focus( 'organizer[phone]' ) }>
						{ __( 'Phone:', 'the-events-calendar' ) }
						{ ' ' }
					</dt>
					<dd>
						<Input
							type="phone"
							ref={ this.saveRef }
							name="organizer[phone]"
							onComplete={ () => this.setState( { isValid: this.isValid() } ) }
							onChange={ ( next ) => this.setState( { phone: next.target.value } ) }
							validate
						/>
					</dd>
					<dt onClick={ this.focus( 'organizer[website]' ) }>
						{ __( 'Website:', 'the-events-calendar' ) }
						{ ' ' }
					</dt>
					<dd>
						<Input
							type="url"
							ref={ this.saveRef }
							onComplete={ () => this.setState( { isValid: this.isValid() } ) }
							onChange={ ( next ) => this.setState( { website: next.target.value } ) }
							name="organizer[website]"
							validate
						/>
					</dd>
					<dt onClick={ this.focus( 'organizer[email]' ) }>
						{ __( 'Email:', 'the-events-calendar' ) }
						{ ' ' }
					</dt>
					<dd>
						<Input
							type="email"
							ref={ this.saveRef }
							name="organizer[email]"
							onComplete={ () => this.setState( { isValid: this.isValid() } ) }
							onChange={ ( next ) => this.setState( { email: next.target.value } ) }
							validate
						/>
					</dd>
				</dl>

				<button
					type="button"
					className="button-secondary"
					onClick={ this.onSubmit }
					disabled={ ! this.isValid() }
				>
					{ __( 'Create Organizer', 'the-events-calendar' ) }
				</button>
			</div>,
		];
	}
}

export default OrganizerForm;
