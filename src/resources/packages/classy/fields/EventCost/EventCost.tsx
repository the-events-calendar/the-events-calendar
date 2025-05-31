import React, { useEffect, useState } from 'react';
import { __, _x } from '@wordpress/i18n';
import { __experimentalInputControl as InputControl, ToggleControl } from '@wordpress/components';
import { useDispatch, useSelect } from '@wordpress/data';
import CurrencySelector from './CurrencySelector';
import { FieldProps } from '@tec/common/classy/types/FieldProps.ts';
import { METADATA_EVENT_COST, METADATA_EVENT_IS_FREE } from '../../constants';


export default function EventCost( props: FieldProps ) {
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
	const [ isFree, setIsFree ] = useState<boolean>( isFreeMeta );

	const eventCostMeta: string = meta[ METADATA_EVENT_COST ] || '';
	const [ eventCostValue, setEventCostValue ] = useState<string>( isFree ? freeText : eventCostMeta );

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
		<div className="classy-field classy-field--event-cost">
			{ ( props.title && props.title.length > 0 ) && (
				<div className="classy-field__title">
					<h3>{ props.title }</h3>
				</div>
			) }

			<div className="classy-field__inputs">
				<div className="classy-field__inputs-section classy-field__inputs-section--row">
					<div className="classy-field__input">
						<InputControl
							label={ _x(
								'Event Cost',
								'Event cost input label',
								'the-events-calendar'
							) }
							value={ eventCostValue }
							onChange={ onCostChange }
							disabled={ isFree }
						/>
					</div>

					<div className="classy-field__input">
						<CurrencySelector />
					</div>

					<div className="classy-field__input">
						<ToggleControl
							label={ _x(
								'Event is free',
								'Event cost toggle label',
								'the-events-calendar'
							) }
							checked={ isFree }
							onChange={ onFreeChange }
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
			</div>
		</div>
	);
}
