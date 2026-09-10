/**
 * External dependencies
 */
import { noop } from 'lodash';

export const withAPIData = () => noop;
export const Spinner = () => "🏃‍♂️";
export const Modal = ( { title, children } ) => (
	<div>
		<span>{ title }</span>
		<span>{ children }</span>
	</div>
);
export const Dashicon = ( { className, icon } ) => <span className={ className }>{ icon }</span>;
export const Dropdown = () => <span>Dropdown</span>;
export const Tooltip = () => <span>Tooltip</span>;
export const PanelBody = ({ children }) => <span className="PanelBody">{ children }</span>
export const Button = ( { children, disabled, type, className, href } ) => (
	<button type={ type } disabled={ disabled } className={ className } data-href={ href }>
		{ children }
	</button>
);
export const ToggleControl = ( { label, checked, disabled } ) => (
	<label className="ToggleControl">
		<input type="checkbox" checked={ Boolean( checked ) } disabled={ disabled } readOnly />
		{ label }
	</label>
);
export const CheckboxControl = ( { label, checked, onChange } ) => (
	<label className="CheckboxControl">
		<input type="checkbox" checked={ Boolean( checked ) } onChange={ ( e ) => onChange( e.target.checked ) } />
		{ label }
	</label>
);
export const Notice = ( { children, status, className } ) => (
	<div className={ `Notice Notice--${ status } ${ className || '' }` }>{ children }</div>
);
export const SelectControl = ( { label, disabled } ) => (
	<label className="SelectControl">
		<select disabled={ disabled } />
		{ label }
	</label>
);
export const TextControl = ( { label, value } ) => (
	<label className="TextControl">
		<input type="text" value={ value } readOnly />
		{ label }
	</label>
);
