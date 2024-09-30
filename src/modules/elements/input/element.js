/**
 * External dependencies
 */
import React from 'react';
import { isFunction, noop } from 'lodash';
import PropTypes from 'prop-types';
import validator from 'validator';

/**
 * WordPress dependencies
 */
import { Component } from '@wordpress/element';
import { TextControl } from '@wordpress/components';

/**
 * Internal dependencies
 */
import './style.pcss';

/**
 * Module Code
 */

/**
 * Input element that adds two important properties:
 *
 * - required
 * - validate
 *
 * Both properties are helpful to provide validation based on the type of the element also has
 * the option to add a custom validation callback with the property validateCallback.
 *
 * <Input />
 *
 * Abstraction on top of the native <input> element it gives external instance methods such as:
 *
 * - isValid()
 * - focus()
 */
class Input extends Component {
	/**
	 * Default types for properties required for this component
	 *
	 * @param validated {boolean} If this component needs to be validated
	 * @param required {boolean} If this input is required
	 */
	static propTypes = {
		validate: PropTypes.bool,
		required: PropTypes.bool,
		onChange: PropTypes.any,
		onComplete: PropTypes.any,
		validateCallback: PropTypes.any,
		type: PropTypes.string,
		className: PropTypes.string,
	}

	/**
	 * Set the default values for the required properties if not provided
	 *
	 * @type {Object} The default properties for the component
	 */
	static defaultProps = {
		required: false,
		validate: false,
		onChange: noop,
		onComplete: noop,
		validateCallback: null,
		type: '',
		className: '',
	}

	constructor() {
		super( ...arguments );

		this.state = {
			isValid: this.validate( '' ),
		};
	}

	/**
	 * Callback fired every time the input changes, if the property onChange is passed to the component is called as well
	 * synchronously.
	 *
	 * @param {Element} input The input element.
	 */
	onChange = ( input ) => {
		const { onChange, onComplete, validate } = this.props;
		const callback = isFunction( onChange ) ? onChange : noop;
		const completeCallback = isFunction( onComplete ) ? onComplete : noop;

		if ( validate ) {
			this.setState( () => ( { isValid: this.validate( input ) } ), completeCallback );
			callback( input );
		} else {
			completeCallback();
			callback( input );
		}
	}

	/**
	 * Validates the component using validateCallback if provided or using the logic based on the type inferring
	 *
	 * @param {string} value The value to be validated
	 * @returns {boolean} `true` if the param is valid false otherwise
	 */
	validate( value ) {
		const { validateCallback } = this.props;
		return isFunction( validateCallback ) ? validateCallback( value ) : this.maybeValidate( value );
	}

	/**
	 * If the input is empty does not make any validation unless the value is required otherwise it uses the validation
	 * based on the type="url" of the <input> component.
	 *
	 * @param {string} value The value to be validated
	 * @returns {boolean} `true` if the param is valid false otherwise
	 */
	maybeValidate = ( value ) => {
		const { type, required } = this.props;

		if ( value.length === 0 ) {
			return ! required;
		}

		let isValid = true;
		switch ( type ) {
			case 'tel':
			case 'phone':
				isValid = validator.isMobilePhone( value, 'any' );
				break;
			case 'email':
				isValid = validator.isEmail( value );
				break;
			case 'url':
				isValid = validator.isURL( value );
				break;
			case 'number':
				isValid = validator.isNumeric( value );
				break;
		}
		return isValid;
	}

	/**
	 * If the component is valid or not based on the validation logic
	 *
	 * @returns {boolean} Whether the state is valid or not.
	 */
	isValid() {
		return this.state.isValid;
	}

	/**
	 * Focus to the current component input
	 */
	focus() {
		this.input.focus();
	}

	/**
	 * If the Component is valid is going to Append the class: `is-valid` otherwise if it fails is going to use the
	 * class `is-invalid`.
	 *
	 * @returns {string|*} The class names.
	 */
	getClassName() {
		const { className, validate } = this.props;
		const { isValid } = this.state;
		const classes = className ? className.split( ' ' ) : [];

		if ( validate ) {
			if ( isValid ) {
				classes.push( 'tribe-editor--valid' );
			} else {
				classes.push( 'tribe-editor--valid' );
			}
		}

		return classes
			.filter( ( name ) => name && name.length )
			.join( ' ' );
	}

	render() {
		// Remove properties that are not part of the DOM.
		const { onComplete, required, validate, ...properties } = this.props;
		return (
			<TextControl
				{ ...properties }
				className={ `${ this.getClassName() }` }
				ref={ ( input ) => this.input = input }
				onChange={ this.onChange }
			/>
		);
	}
}

export default Input;
