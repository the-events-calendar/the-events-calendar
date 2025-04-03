import { _x } from '@wordpress/i18n';
import { EventDetailsProps } from '../../types/FieldProps';
import { RichText} from '@wordpress/block-editor';
import { __experimentalInputControl as InputControl } from '@wordpress/components';
import { usePostEdits } from '../../hooks';
import { UsePostEditsReturn } from '../../types/UsePostEditsReturn';
import { useEffect, useState } from 'react';
import { METADATA_EVENT_URL } from '../../constants';
import { PostFeaturedImage } from '@wordpress/editor';

export function EventDetails( props: EventDetailsProps ) {
	const { postContent, meta, editPost } =
		usePostEdits() as UsePostEditsReturn;
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

	const onUrlChange = ( nextValue: string ): void => {
		setEventUrlValue( nextValue ?? '' );
		editPost( { meta: { [ METADATA_EVENT_URL ]: nextValue } } );
	};

	console.log('hello from classy');

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

					<RichText
						className="classy-field__control classy-field__control--rich-text"
						tagName="p"
						value={ description }
						inlineToolbar={ true }
						allowedFormats={ [ 'core/bold', 'core/italic' ] }
						onChange={ onDescriptionChange }
						placeholder={ _x(
							'Describe your event',
							'Event description placeholder text',
							'the-events-calendar'
						) }
					></RichText>
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
