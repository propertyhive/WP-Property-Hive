import metadata from './module.json';
import { registerPropertyContentModule } from '../property-content/registerPropertyContentModule';

registerPropertyContentModule(metadata, {
  moduleClassName: 'propertyhive_divi5_property_search_form',
  outputClassName: 'propertyhive-divi5-property-search-form',
  textAttrName: 'contentText',
  previewKind: 'form',
  sampleValue: '[Property search form]',
});
