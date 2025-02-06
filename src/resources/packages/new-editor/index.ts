import {dispatch} from "@wordpress/data";
import {localizedData} from './localized-data';

console.log('Hello from the new editor!');

const {eventCategoryTaxonomyName} = localizedData;

dispatch('core/editor').removeEditorPanel(`taxonomy-panel-${eventCategoryTaxonomyName}`);
dispatch('core/editor').removeEditorPanel('taxonomy-panel-post_tag');
