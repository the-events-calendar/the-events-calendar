import {LocalizedData, Settings} from '@tec/common/classy/types/LocalizedData';
import {Minutes} from "@tec/common/classy/types/Minutes";
import {Hours} from "@tec/common/classy/types/Hours";

export type TECSettings = {
	venuesLimit: number;
	disableContent?: boolean;
	disableContentReason?: string;
} & Settings;

export type EndOfDayCutoff = {
	hours: Hours;
	minutes: Minutes;
}

export type TECLocalizedData = {
	endOfDayCutoff: EndOfDayCutoff;
	settings: TECSettings;
} & LocalizedData;
