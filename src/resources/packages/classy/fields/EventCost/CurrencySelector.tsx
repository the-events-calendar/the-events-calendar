import React from 'react';
import { _x } from '@wordpress/i18n';
import { __experimentalInputControl as InputControl } from '@wordpress/components';
import { useEffect, useState } from 'react';
import { useDispatch, useSelect } from '@wordpress/data';
import { FieldProps } from '@tec/common/classy/types/FieldProps.ts';
import { METADATA_EVENT_CURRENCY_SYMBOL } from '../../constants';

export default function CurrencySelector( props: FieldProps ) {
	const { meta } = useSelect( ( select ) => {
		const selector = select( 'core/editor' );
		return {
			// @ts-ignore
			meta: selector.getEditedPostAttribute( 'meta' ) || {},
		};
	}, [] );
	const { editPost } = useDispatch( 'core/editor' );
	const eventCurrencyMeta: string = meta[ METADATA_EVENT_CURRENCY_SYMBOL ] || '';

	const [ currencyValue, setCurrencyValue ] = useState< string >( eventCurrencyMeta );

	useEffect( () => {
		setCurrencyValue( eventCurrencyMeta );
	}, [ eventCurrencyMeta ] );

	const onCurrencyChange = ( nextValue: string | undefined ): void => {
		setCurrencyValue( nextValue ?? '' );
		editPost( { meta: { [ METADATA_EVENT_CURRENCY_SYMBOL ]: nextValue } } );
	};

	return (
		<div className="classy-field classy-field--event-cost">
			<div className="classy-field__title">
				<h3>{ props.title }</h3>
			</div>

			<div className="classy-field__inputs">
				<div className="classy-field__input">
					<InputControl
						label={ _x(
							'Event Currency',
							'Event currency input label',
							'the-events-calendar'
						) }
						value={ currencyValue }
						onChange={ onCurrencyChange }
					/>
				</div>
			</div>
		</div>
	);
}
