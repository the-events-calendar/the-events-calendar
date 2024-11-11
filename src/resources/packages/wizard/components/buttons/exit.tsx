import React from "react";
import { Button, Modal } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useSelect, useDispatch } from "@wordpress/data";
import { MODAL_STORE_KEY, SETTINGS_STORE_KEY } from "../../data";

const ExitButton = () => {
	const { closeModal } = useDispatch(MODAL_STORE_KEY);

	<Button
		variant="tertiary"
		onClick={closeModal}
	>
		{__('Skip guided setup', 'the-events-calendar')}
	</Button>
};

export default ExitButton;
