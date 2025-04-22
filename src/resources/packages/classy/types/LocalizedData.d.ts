import { Hours } from './Hours';
import { Minutes } from './Minutes';
import { StartOfWeek } from './StartOfWeek';

export type Settings = {
	timezoneString: string;
	timezoneChoice: string;
	startOfWeek: StartOfWeek;
	endOfDayCutoff: {
		hours: Hours;
		minutes: Minutes;
	};
	dateWithYearFormat: string;
	dateWithoutYearFormat: string;
	monthAndYearFormat: string;
	compactDateFormat: string;
	dataTimeSeparator: string;
	timeRangeSeparator: string;
	timeFormat: string;
	timeInterval: number;
};

export type LocalizedData = {
	settings: Settings;
};
