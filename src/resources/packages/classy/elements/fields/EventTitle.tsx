import { EventTitleProps } from '../../types/EventTitleProps';
import { __experimentalInputControl as InputControl } from '@wordpress/components';
import { useState } from 'react';

export function EventTitle( props: EventTitleProps ) {
	const [ value, setValue ] = useState< string >(
		'Cutting onions like a pro'
	);

	return (
		<div className="classy-field">
			<div className="classy-field__title">
				<h3>{ props.title }</h3>
			</div>

			<div className="classy-field__inputs">
				<InputControl
					__next40pxDefaultSize
					value={ value }
					onChange={ ( nextValue ) => setValue( nextValue ?? '' ) }
				/>
			</div>
		</div>
	);
}
