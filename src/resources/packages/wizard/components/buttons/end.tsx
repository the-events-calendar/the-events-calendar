import React from "react";
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
import { Button } from '@wordpress/components';
import { useSelect, useDispatch } from "@wordpress/data";
import { useState, useEffect} from '@wordpress/element';
import {SETTINGS_STORE_KEY} from "../../data";
import { API_ENDPOINT } from "../../data/settings/constants";

const EndButton = ({disabled}) => {
	const settings = useSelect(select => select(SETTINGS_STORE_KEY).getSettings() || false, []);
	const isSaving = useSelect(select => select(SETTINGS_STORE_KEY).getIsSaving() || false, []);
	const setSaving = useDispatch(SETTINGS_STORE_KEY).setSaving;
	const [isClicked, setClicked] = useState(false);

	/*
	useEffect(() => {
		const fetchData = async () => {
			const { timezones, availableViews, ...filteredSettings } = settings;
			const result = await apiFetch({
				method: "POST",
				data: filteredSettings,
				path: API_ENDPOINT,
			});

			// Handle the response here, for example:
			console.log("API response:", result);
			// You could also trigger a state update or call a callback function
			// setResponse(result); // if using a state variable
			// or call a function to handle the result:
			// handleApiResponse(result);
		};
		fetchData();
	}, [isClicked]);


	if ( isClicked ) {
		return (
			<Button
				variant="primary"
				disabled={true}
			>
				{__('Saving...', 'the-events-calendar')}
			</Button>
		)
	}
	*/

	return(
		<Button
			variant="primary"
			onClick={setClicked}
			disabled={disabled}
		>
			{__('Continue', 'the-events-calendar')}
		</Button>
	);
};

export default EndButton;
