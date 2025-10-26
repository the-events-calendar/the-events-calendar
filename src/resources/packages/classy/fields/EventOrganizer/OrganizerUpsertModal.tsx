import * as React from 'react';
import { Modal } from '@wordpress/components';
import { _x } from '@wordpress/i18n';
import { IconNew } from '@tec/common/classy/components';
import OrganizerUpsert from './OrganizerUpsert';
import { OrganizerData } from '../../types/OrganizerData';

export default function OrganizerUpsertModal( props: {
	isUpdate: boolean;
	onCancel: () => void;
	onClose: () => void;
	onSave: ( organizerData: OrganizerData ) => void;
	values: OrganizerData;
} ) {
	const { isUpdate, onCancel, onClose, onSave, values } = props;

	const title = isUpdate
		? _x( 'Update Organizer', 'Update organizer modal header title', 'the-events-calendar' )
		: _x( 'New Organizer', 'Insert organizer modal header title', 'the-events-calendar' );

	return (
		<Modal
			className="classy-modal classy-modal--organizer"
			icon={ <IconNew /> }
			onRequestClose={ onClose }
			overlayClassName="classy-modal__overlay classy-modal__overlay--organizer"
			title={ title }
		>
			<OrganizerUpsert isUpdate={ isUpdate } onCancel={ onCancel } onSave={ onSave } values={ values } />
		</Modal>
	);
}
