import * as React from 'react';
import { Fragment, useEffect, useState } from 'react';
import { __, _x } from '@wordpress/i18n';
import { __experimentalInputControl as InputControl, ToggleControl } from '@wordpress/components';
import { useDispatch, useSelect } from '@wordpress/data';
import { CurrencySelector } from '@tec/common/classy/components';
import {
	METADATA_EVENT_COST,
	METADATA_EVENT_CURRENCY,
	METADATA_EVENT_CURRENCY_POSITION,
	METADATA_EVENT_CURRENCY_SYMBOL,
	METADATA_EVENT_IS_FREE
} from '../../constants';

export default function EventCost(): JSX.Element {
	const { meta } = useSelect( ( select ) => {
		const selector = select( 'core/editor' );
		return {
			// @ts-ignore
			meta: selector.getEditedPostAttribute( 'meta' ) || {},
		};
	}, [] );

	const freeText = __( 'Free', 'the-events-calendar' );

	const { editPost } = useDispatch( 'core/editor' );

	const isFreeMeta: boolean = meta[ METADATA_EVENT_IS_FREE ] || false;
	const [ isFree, setIsFree ] = useState< boolean >( isFreeMeta );

	const eventCostMeta: string = meta[ METADATA_EVENT_COST ] || '';
	const [ eventCostValue, setEventCostValue ] = useState< string >( isFree ? freeText : eventCostMeta );

	// Track changes to the post content and update the state accordingly.
	useEffect( () => {
		setEventCostValue( eventCostMeta );
	}, [ eventCostMeta ] );

	useEffect( () => {
		setIsFree( isFreeMeta );
	}, [ isFreeMeta ] );

	// Handle changes to the event cost input.
	const onCostChange = ( nextValue: string | undefined ): void => {
		setEventCostValue( nextValue ?? '' );
		editPost( { meta: { [ METADATA_EVENT_COST ]: nextValue } } );
	};

	// Handle changes to the "is free" toggle.
	const onFreeChange = ( nextValue: boolean ): void => {
		setIsFree( nextValue );
		setEventCostValue( nextValue ? freeText : eventCostMeta );

		editPost( { meta: { [ METADATA_EVENT_IS_FREE ]: nextValue } } );
	};

	return (
		<Fragment>
			<div className="classy-field__group">
				<div className="classy-field__input classy-field__input--unit">
					<div className="classy-field__control classy-field__control--input">
						<InputControl
							label={
								<span className="classy-field__input-title">
									{ _x( 'Event cost', 'Event cost input title', 'the-events-calendar' ) }
								</span>
							}
							value={ eventCostValue }
							onChange={ onCostChange }
							disabled={ isFree }
						/>
					</div>
				</div>

				<div className="classy-field__input classy-field__input--height-100">
					<CurrencySelector
						currencyCodeMeta={ METADATA_EVENT_CURRENCY }
						currencyPositionMeta={ METADATA_EVENT_CURRENCY_POSITION }
						currencySymbolMeta={ METADATA_EVENT_CURRENCY_SYMBOL }
					/>
				</div>

				<div className="classy-field__input classy-field__input--height-75">
					<ToggleControl
						label={ _x( 'Event is free', 'Event cost toggle label', 'the-events-calendar' ) }
						checked={ isFree }
						onChange={ onFreeChange }
						__nextHasNoMarginBottom
					/>
				</div>
			</div>

			<div className="classy-field__input-note">
				{ _x(
					'If multiple entry prices are available, list each price separated by commas.',
					'Event cost input note',
					'the-events-calendar'
				) }
			</div>
		</Fragment>
	);
}
