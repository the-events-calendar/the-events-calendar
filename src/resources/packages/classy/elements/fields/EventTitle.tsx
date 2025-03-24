import { EventTitleProps } from '../../types/FieldProps';
import { __experimentalInputControl as InputControl } from '@wordpress/components';
import { useState, useEffect } from 'react';
import { usePostEdits } from '../../hooks';
import { UsePostEditsReturn } from '../../types/UsePostEditsReturn';

export function EventTitle( props: EventTitleProps ) {
	const { postTitle, editPost } = usePostEdits() as UsePostEditsReturn;

	const [ value, setValue ] = useState< string >( postTitle || '' );

	useEffect( () => {
		setValue( postTitle );
	}, [ postTitle ] );

	const onChange = ( nextValue: string ): void => {
		setValue( nextValue ?? '' );
		editPost( { title: nextValue } );
	};

	return (
		<div className="classy-field classy-field--event-title">
			<div className="classy-field__title">
				<h3>{ props.title }</h3>
			</div>

			<div className="classy-field__inputs">
				<InputControl
					className="classy-field__input"
					__next40pxDefaultSize
					value={ value }
					onChange={ onChange }
				/>
			</div>
		</div>
	);
}
