<?php

	/**
	 *  The interface for all Tribe__Events__Pro__Date_Series_Rules__Rules_Interface.
	 *  They all implement a function called getNextDate
	 *  that returns the next date in a series based on it's particular set of rules.
	 */
	interface Tribe__Events__Pro__Date_Series_Rules__Rules_Interface {

		const DATE_ONLY_FORMAT = 'Y-m-d';
		const DATE_FORMAT      = 'Y-m-d H:i:s';

		public function getNextDate( $curdate );
	}
