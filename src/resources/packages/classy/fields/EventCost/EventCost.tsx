import React from 'react';
import { _x } from '@wordpress/i18n';
import { __experimentalInputControl as InputControl } from '@wordpress/components';
import { useEffect, useState } from 'react';
import { useDispatch, useSelect } from '@wordpress/data';
import { FieldProps } from '@tec/common/classy/types/FieldProps.ts';
import { METADATA_EVENT_COST } from '../../constants';


export default function EventCost( props: FieldProps ) {
	const { meta } = useSelect( ( select ) => {
		const selector = select( 'core/editor' );
		return {
			// @ts-ignore
			meta: selector.getEditedPostAttribute( 'meta' ) || {},
		};
	}, [] );
	const { editPost } = useDispatch( 'core/editor' );
	const eventCostMeta: string = meta[ 'tec_event_cost' ] || '';

	const [ eventCostValue, setEventCostValue ] = useState< string >( eventCostMeta );

	useEffect( () => {
		setEventCostValue( eventCostMeta );
	}, [ eventCostMeta ] );

	const onCostChange = ( nextValue: string | undefined ): void => {
		setEventCostValue( nextValue ?? '' );
		editPost( { meta: { 'tec_event_cost': nextValue } } );
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
							'Event Cost',
							'Event cost input label',
							'the-events-calendar'
						) }
						value={ eventCostValue }
						onChange={ onCostChange }
					/>
				</div>
			</div>
		</div>
	);
}

