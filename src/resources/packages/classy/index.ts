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
import { init as initStore } from './store';
import './style.pcss';

initStore();

whenEditorIsReady().then( () => {
	hideZoomOutButton();
	hideInserterToggle();
	initClassyApp();
	insertClassyElement();
	addEditorTools( () => toggleClassyElementVisibility() );
} );
