import React, { Fragment } from 'react';
import { addFilter } from '@wordpress/hooks';
import { Fill } from '@wordpress/components';
import {getDefaultRegistry} from "@tec/common/classy/functions";

addFilter(
	'tec.classy.render',
	'tec.classy.events',
	( fields: React.ReactNode | null ) => {
		const defaultRegistry = getDefaultRegistry();
		const typeofDefaultRegistry = typeof defaultRegistry;
		return (
			<Fragment>
				{fields}
				<Fill name="tec.classy.before">
					<p>{typeofDefaultRegistry}</p>
				</Fill>
			</Fragment>
		);
	}
);

addFilter(
	'tec.classy.render',
	'tec.classy.events',
	( fields: React.ReactNode | null ) => (
		<Fragment>
			{ fields }
			<Fill name="tec.classy.before">
				<p>TEC BEFORE CLASSY __2</p>
			</Fill>
		</Fragment>
	)
);

addFilter(
	'tec.classy.render',
	'tec.classy.events',
	( fields: React.ReactNode | null ) => (
		<Fragment>
			{ fields }
			<Fill name="tec.classy.after">
				<p>TEC AFTER CLASSY __1</p>
			</Fill>
		</Fragment>
	)
);

addFilter(
	'tec.classy.render',
	'tec.classy.events',
	( fields: React.ReactNode | null ) => (
		<Fragment>
			{ fields }
			<Fill name="tec.classy.after">
				<p>TEC AFTER CLASSY __2</p>
			</Fill>
		</Fragment>
	)
);

addFilter(
	'tec.classy.render',
	'tec.classy.events',
	( fields: React.ReactNode | null ) => (
		<Fragment>
			{ fields }
			<Fill name="tec.classy.fields.after">
				<p>TEC AFTER FIELDS __1</p>
			</Fill>
		</Fragment>
	)
);

addFilter(
	'tec.classy.render',
	'tec.classy.events',
	( fields: React.ReactNode | null ) => (
		<Fragment>
			{ fields }
			<Fill name="tec.classy.fields.after">
				<p>TEC AFTER FIELDS __2</p>
			</Fill>
		</Fragment>
	)
);

addFilter(
	'tec.classy.render',
	'tec.classy.events',
	( fields: React.ReactNode | null ) => (
		<Fragment>
			{ fields }
			<Fill name="tec.classy.fields.before">
				<p>TEC BEFORE FIELDS __1</p>
			</Fill>
		</Fragment>
	)
);

addFilter(
	'tec.classy.render',
	'tec.classy.events',
	( fields: React.ReactNode | null ) => (
		<Fragment>
			{ fields }
			<Fill name="tec.classy.fields.before">
				<p>TEC BEFORE FIELDS __2</p>
			</Fill>
		</Fragment>
	)
);
