import {  _x } from '@wordpress/i18n';
import { EventDetailsProps } from '../../types/FieldProps';
import {RichText} from '@wordpress/block-editor';
import { __experimentalInputControl as InputControl } from '@wordpress/components';
import React from 'react';

export function EventDetails( props: EventDetailsProps ) {
	return (
		<div className="classy-field classy-field--event-details">
			<div className="classy-field__title">
				<h3>{ props.title }</h3>
			</div>

			<div className="classy-field__inputs">

				<div className="classy-field__input-title">
					<h4>{ _x('Description','Event details description input title', 'the-events-calendar') }</h4>
				</div>

				<RichText
					className="classy-field__input classy-field__input--rich-text"	
					tagName="p"				
					value={ '' }
					allowedFormats={ [ 'core/bold', 'core/italic' ] }
					onChange={ () => {} }
					placeholder={ _x('Describe your event', 'Event description placeholder text','the-events-calendar') }
				>
				</RichText>

				{/* add the featured image here */}

				<div className="classy-field__input-title">
					<h4>{ _x('Event website','Event details website URL input title', 'the-events-calendar') }</h4>
				</div>

				<InputControl
					className='classy-field__input'
					__next40pxDefaultSize
					value={ '' }
					onChange={ ()=>{} }
					placeholder="www.example.com"
				/>

			</div>
		</div>
	);
}
