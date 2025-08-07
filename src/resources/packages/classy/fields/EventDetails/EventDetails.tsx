import * as React from 'react';
import { _x } from '@wordpress/i18n';
import { __experimentalInputControl as InputControl } from '@wordpress/components';
import { useEffect, useState } from 'react';
import { PostFeaturedImage } from '@wordpress/editor';
import { useDispatch, useSelect } from '@wordpress/data';
import { TinyMceEditor } from '@tec/common/classy/components';
import { isValidUrl } from '@tec/common/classy/functions';
import { FieldProps } from '@tec/common/classy/types/FieldProps.ts';
import { METADATA_EVENT_URL } from '../../constants';

export default function EventDetails( props: FieldProps ) {
	const { postContent, meta } = useSelect( ( select ) => {
		const store: {
			getEditedPostContent: () => string;
			getEditedPostAttribute: ( attribute: string ) => any;
		} = select( 'core/editor' );

		return {
			postContent: store.getEditedPostContent() || '',
			meta: store.getEditedPostAttribute( 'meta' ) || null,
		};
	}, [] );
	const { editPost } = useDispatch( 'core/editor' );
	const eventUrlMeta: string = meta?.[ METADATA_EVENT_URL ] || '';

	const [ description, setDescription ] = useState< string >( postContent || '' );
	const [ eventUrlValue, setEventUrlValue ] = useState< string >( eventUrlMeta );
	const [ hasValidUrl, setHasValidUrl ] = useState< boolean >( true );

	useEffect( () => {
		setDescription( postContent );
	}, [ postContent ] );

	useEffect( () => {
		setEventUrlValue( eventUrlMeta );
	}, [ eventUrlMeta ] );

	const onDescriptionChange = ( nextValue: string ): void => {
		setDescription( nextValue ?? '' );
		editPost( { content: nextValue } );
	};

	const onUrlChange = ( nextValue: string | undefined ): void => {
		const urlValue = nextValue ?? '';

		if ( ! isValidUrl( urlValue ) ) {
			setHasValidUrl( false );
			return;
		}

		setHasValidUrl( true );
		setEventUrlValue( urlValue );
		editPost( { meta: { [ METADATA_EVENT_URL ]: nextValue } } );
	};

	return (
		<div className="classy-field classy-field--event-details">
			<div className="classy-field__title">
				<h3>{ props.title }</h3>
			</div>

			<div className="classy-field__inputs">
				<div className="classy-field__input">
					<div className="classy-field__input-title">
						<h4>{ _x( 'Description', 'Event details description input title', 'the-events-calendar' ) }</h4>
					</div>

					<div className="classy-field__control classy-field__control--tinymce-editor">
						<TinyMceEditor
							content={ description }
							onChange={ onDescriptionChange }
							id="classy-event-details-description-editor"
						/>
					</div>

					<div className="classy-field__input-note">
						{ _x( 'Describe your event', 'Event description placeholder text', 'the-events-calendar' ) }
					</div>
				</div>

				<div className="classy-field__input">
					<div className="classy-field__input-title">
						<h4>
							{ _x(
								'Featured Image',
								'Event details featured image input title',
								'the-events-calendar'
							) }
						</h4>
					</div>

					<div className="classy-field__control classy-field__control--featured-image">
						{ /* @ts-ignore */ }
						<PostFeaturedImage />
					</div>

					<div className="classy-field__input-note">
						{ _x(
							'We recommend a 16:9 aspect ratio for featured images.',
							'Event details featured image input note',
							'the-events-calendar'
						) }
					</div>
				</div>

				<div className="classy-field__input">
					<div className="classy-field__input-title">
						<h4>
							{ _x( 'Event website', 'Event details website URL input title', 'the-events-calendar' ) }
						</h4>
					</div>

					<InputControl
						className={ `classy-field__control classy-field__control--input${
							! hasValidUrl ? ' classy-field__control--invalid' : ''
						}` }
						__next40pxDefaultSize
						value={ eventUrlValue }
						onChange={ onUrlChange }
						placeholder="www.example.com"
					/>
					{ ! hasValidUrl && (
						<div className="classy-field__input-note classy-field__input-note--error">
							{ _x(
								'Must be a valid URL',
								'Event details website URL input error message',
								'the-events-calendar'
							) }
						</div>
					) }
				</div>
			</div>
		</div>
	);
}
