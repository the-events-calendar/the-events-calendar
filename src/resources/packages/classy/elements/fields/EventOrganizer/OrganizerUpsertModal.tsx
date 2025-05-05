import React from 'react';
import { Modal } from '@wordpress/components';
import OrganizerUpsert from './OrganizerUpsert';
import { OrganizerData } from '../../../types/OrganizerData';

export default function OrganizerUpsertModal( props: {
	isUpdate: boolean;
	onCancel: () => void;
	onClose: () => void;
	onSave: ( organizerData: OrganizerData ) => void;
	values: OrganizerData;
} ) {
	const { isUpdate, onCancel, onClose, onSave, values } = props;

	console.log( 'isUpdate', isUpdate );

	return (
		<Modal
			__experimentalHideHeader={ true }
			className="classy-modal classy-modal--organizer"
			onRequestClose={ onClose }
			overlayClassName="classy-modal__overlay classy-modal__overlay--organizer"
		>
			<OrganizerUpsert
				isUpdate={ isUpdate }
				onCancel={ onCancel }
				onSave={ onSave }
				values={ values }
			/>
		</Modal>
	);
}
