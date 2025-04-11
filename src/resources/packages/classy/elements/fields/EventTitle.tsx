import { EventTitleProps } from '../../types/FieldProps';
import { __experimentalInputControl as InputControl } from '@wordpress/components';
import { useState, useEffect } from 'react';
import { useSelect, useDispatch } from '@wordpress/data';

export function EventTitle( props: EventTitleProps ) {
	// @todo in a component like this one: dispatch to the `tec/classy` store directly or to the `core/editor` registry?
	const postTitle = useSelect( ( select ) => {
		const { getEditedPostAttribute }: { getEditedPostAttribute: Function } =
			select( 'tec/classy' );
		return getEditedPostAttribute( 'title' );
	}, [] );
	const { editPost }: { editPost: Function } = useDispatch( 'tec/classy' );

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
					className="classy-field__control classy-field__control--input"
					__next40pxDefaultSize
					value={ value }
					onChange={ onChange }
				/>
			</div>
		</div>
	);
}
