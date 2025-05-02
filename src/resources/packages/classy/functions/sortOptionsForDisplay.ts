import { CustomSelectOption } from '@wordpress/components/build-types/custom-select-control/types';
import { SelectOption } from '../types/SelectOption';

export function sortOptionsForDisplay(
	a: SelectOption | CustomSelectOption,
	b: SelectOption | CustomSelectOption
): number {
	// Keep the placeholder at the top.
	if ( a.value === '0' ) {
		return -1;
	}

	// Keep the placeholder at the top.
	if ( b.value === '0' ) {
		return 1;
	}

	if ( a.label < b.label ) {
		return -1;
	}
	if ( a.label > b.label ) {
		return 1;
	}
	return 0;
}
