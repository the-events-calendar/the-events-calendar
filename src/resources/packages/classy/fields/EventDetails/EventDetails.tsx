import React from 'react';
import { _x } from '@wordpress/i18n';
import { __experimentalInputControl as InputControl } from '@wordpress/components';
import { useEffect, useState } from 'react';
import { PostFeaturedImage } from '@wordpress/editor';
import { useDispatch, useSelect } from '@wordpress/data';
import { TinyMceEditor } from '@tec/common/classy/components/TinyMceEditor';
import { METADATA_EVENT_URL } from '@tec/common/classy/constants';
import { FieldProps } from '../../../../../../common/src/resources/packages/classy/types/FieldProps';

export default function EventDetails( props: FieldProps ) {
	const { postContent, meta } = useSelect( ( select ) => {
		const selector = select( 'core/editor' );
		return {
			// @ts-ignore
			postContent: selector.getEditedPostContent() || '',
			// @ts-ignore
			meta: selector.getEditedPostAttribute( 'meta' ) || {},
		};
	}, [] );
	const { editPost } = useDispatch( 'core/editor' );
	const eventUrlMeta: string = meta[ METADATA_EVENT_URL ] || '';

	const [ description, setDescription ] = useState< string >(
		postContent || ''
	);
	const [ eventUrlValue, setEventUrlValue ] =
		useState< string >( eventUrlMeta );

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
		setEventUrlValue( nextValue ?? '' );
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
						<h4>
							{ _x(
								'Description',
								'Event details description input title',
								'the-events-calendar'
							) }
						</h4>
					</div>

					<div className="classy-field__control classy-field__control--tinymce-editor">
						<TinyMceEditor
							content={ description }
							onChange={ onDescriptionChange }
							id="classy-event-details-description-editor"
						/>
					</div>

					<div className="classy-field__input-note">
						{ _x(
							'Describe your event',
							'Event description placeholder text',
							'the-events-calendar'
						) }
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
							{ _x(
								'Event website',
								'Event details website URL input title',
								'the-events-calendar'
							) }
						</h4>
					</div>

					<InputControl
						className="classy-field__control classy-field__control--input"
						__next40pxDefaultSize
						value={ eventUrlValue }
						onChange={ onUrlChange }
						placeholder="www.example.com"
					/>
				</div>
			</div>
		</div>
	);
}
