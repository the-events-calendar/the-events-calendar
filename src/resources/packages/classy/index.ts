import { whenEditorIsReady } from './functions/whenEditorIsReady';
import {
	hideInserterToggle,
	hideZoomOutButton,
} from './functions/editorModifications';
import { addEditorTools } from './functions/addEditorTools';
import {
	initApp as initClassyApp,
	insertElement as insertClassyElement,
	toggleElementVisibility as toggleClassyElementVisibility,
} from './functions/classy';
import { store } from './store';
import './style.pcss';
import { register } from '@wordpress/data';

register( store );

whenEditorIsReady().then( () => {
	hideZoomOutButton();
	hideInserterToggle();
	initClassyApp();
	insertClassyElement();
	addEditorTools( () => toggleClassyElementVisibility() );
} );
