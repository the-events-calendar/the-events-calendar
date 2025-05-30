import React from 'react';
import { _x } from '@wordpress/i18n';
import {
	__experimentalInputControl as InputControl,
	ToggleControl
} from '@wordpress/components';
import { useEffect, useState } from 'react';
import { useDispatch, useSelect } from '@wordpress/data';
import { FieldProps } from '@tec/common/classy/types/FieldProps.ts';
import {
	METADATA_EVENT_COST,
	METADATA_EVENT_IS_FREE
} from '../../constants';


export default function EventCost( props: FieldProps ) {
	const { meta } = useSelect( ( select ) => {
		const selector = select( 'core/editor' );
		return {
			// @ts-ignore
			meta: selector.getEditedPostAttribute( 'meta' ) || {},
		};
	}, [] );

	const { editPost } = useDispatch( 'core/editor' );

	const eventCostMeta: string = meta[ METADATA_EVENT_COST ] || '';
	const [ eventCostValue, setEventCostValue ] = useState< string >( eventCostMeta );

	useEffect( () => {
		setEventCostValue( eventCostMeta );
	}, [ eventCostMeta ] );

	const onCostChange = ( nextValue: string | undefined ): void => {
		setEventCostValue( nextValue ?? '' );
		editPost( { meta: { [ METADATA_EVENT_COST ]: nextValue } } );
	};

	const isFreeMeta: boolean = meta[ METADATA_EVENT_IS_FREE ] || false;
	const [ isFree, setIsFree ] = useState< boolean >( isFreeMeta );

	useEffect( () => {
		setIsFree( isFreeMeta );
	}, [ isFreeMeta ] );

	const onFreeChange = ( nextValue: boolean ): void => {
		setIsFree( nextValue );
		editPost( { meta: { [ METADATA_EVENT_IS_FREE ]: nextValue } } );

		// If the event is marked as free, update the cost to "Free".
		if ( nextValue ) {
			onCostChange( 'Free' );
		}
	};

	return (
		<div className="classy-field classy-field--event-cost">
			<div className="classy-field__title">
				<h3>{ props.title }</h3>
			</div>

			<div className="classy-field__inputs classy-field__inputs--boxed">
				<div className="classy-field__input">
					<InputControl
						label={ _x(
							'Event Cost',
							'Event cost input label',
							'the-events-calendar'
						) }
						value={ eventCostValue }
						onChange={ onCostChange }
					/>
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
						></ToggleControl>
				</div>
			</div>
		</div>
	);
}
