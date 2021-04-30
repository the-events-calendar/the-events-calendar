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
	
	state = {
		title: null,
		phone: '',
		website: '',
		email: '',
		organizer: null,
		isValid: true,
	};
	
	fields = {};

	isCreating = () => {
		const { organizer } = this.state;
		
		if ( ! organizer ) {
			return false;
		}

		if ( ! isFunction( organizer.state ) ) {
			return false;
		}

		return 'pending' === organizer.state();
	}

	onSubmit = () => {
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
	
	onInputChange = ( key ) => ( value ) => {
		this.setState( { [ key ]: value } );
	}
	
	onInputComplete = () => {
		this.setState( { isValid: this.isValid() } );
	}

	updateOrganizer = ( toSend ) => {
		const { postType } = this.props;
		const request = wp.apiRequest( {
			path: `/wp/v2/${ postType }`,
			method: 'POST',
			data: toSend,
		} );

		// Set the organizer state
		this.setState( { organizer: request } );

		request.done( ( newPost ) => {
			if ( ! newPost.id ) {
				console.warning( 'Invalid creation of organizer:', newPost );
			}

			this.props.addOrganizer( newPost.id, newPost );
			this.props.onClose();
		} ).fail( ( err ) => {
			console.error( err );
		} );
	}

	isValid = () => {
		const fields = values( this.fields );
		const results = fields.filter( ( input ) => input.isValid() );

		return fields.length === results.length;
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
			return (
				<div
					className="tribe-editor__organizer__form"
					key="tribe-organizer-form"
				>
					<Placeholder key="placeholder">
						<Spinner />
					</Placeholder>
				</div>
			);
		}

		return (
			<div
				className="tribe-editor__organizer__form"
				key="tribe-organizer-form"
			>
				<h3 key="tribe-organizer-form-title">
					{ __( 'Create Organizer' ) }
				</h3>
				<p className="description">
					{ __( 'The e-mail address will be obfuscated on your site to avoid it getting harvested by spammers.', 'the-events-calendar' ) }
				</p>
				<dl>
					<dt>
						{ __( 'Name:', 'the-events-calendar' ) }
					</dt>
					<dd>
						<Input
							type="text"
							ref={ this.saveRef }
							name="organizer[name]"
							onComplete={ this.onInputComplete }
							onChange={ this.onInputChange('title') }
							validate
						/>
					</dd>
					<dt>
						{ __( 'Phone:', 'the-events-calendar' ) }
					</dt>
					<dd>
						<Input
							type="phone"
							ref={ this.saveRef }
							name="organizer[phone]"
							onComplete={ this.onInputComplete }
							onChange={ this.onInputChange('phone') }
							validate
							data-testid="organizer-form-input-phone"
						/>
					</dd>
					<dt>
						{ __( 'Website:', 'the-events-calendar' ) }
					</dt>
					<dd>
						<Input
							type="url"
							ref={ this.saveRef }
							onComplete={ this.onInputComplete }
							onChange={ this.onInputChange('website') }
							name="organizer[website]"
							validate
						/>
					</dd>
					<dt>
						{ __( 'Email:', 'the-events-calendar' ) }
					</dt>
					<dd>
						<Input
							type="email"
							ref={ this.saveRef }
							name="organizer[email]"
							onComplete={ this.onInputComplete }
							onChange={ this.onInputChange('email') }
							validate
						/>
					</dd>
				</dl>

				<button
					type="button"
					className="button-secondary"
					onClick={ this.onSubmit }
					disabled={ ! this.isValid() }
					data-testid="organizer-form-button-create"
				>
					{ __( 'Create Organizer', 'the-events-calendar' ) }
				</button>
			</div>
		);
	}
}

export default OrganizerForm;
