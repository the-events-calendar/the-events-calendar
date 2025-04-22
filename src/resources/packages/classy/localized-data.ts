import { LocalizedData } from './types/LocalizedData';

declare global {
	interface Window {
		tec: {
			events: {
				classy: {
					data: LocalizedData;
				};
			};
		};
	}
}

export const localizedData: LocalizedData = window?.tec?.events?.classy
	?.data ?? {
	settings: {
		timezoneString: 'UTC',
		timezoneChoice: '',
		startOfWeek: 0,
		endOfDayCutoff: {
			hours: 0,
			minutes: 0,
		},
		dateWithYearFormat: 'F j, Y',
		dateWithoutYearFormat: 'F j',
		monthAndYearFormat: 'F Y',
		compactDateFormat: 'n/j/Y',
		dataTimeSeparator: ' @ ',
		timeRangeSeparator: ' - ',
		timeFormat: 'g:i A',
		timeInterval: 15,
	},
};
