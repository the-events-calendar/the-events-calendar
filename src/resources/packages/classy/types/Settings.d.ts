import { LocalizedData, Settings } from '@tec/common/classy/types/LocalizedData';
import { Minutes } from '@tec/common/classy/types/Minutes';
import { Hours } from '@tec/common/classy/types/Hours';

export type EndOfDayCutoff = {
	hours: Hours;
	minutes: Minutes;
	endHours: Hours;
	endMinutes: Minutes;
};

export type TECSettings = {
	venuesLimit: number;
	disableContent?: boolean;
	disableContentReason?: string;
	endOfDayCutoff: EndOfDayCutoff;
} & Settings;

export type TECLocalizedData = {
	settings: TECSettings;
} & LocalizedData;
