import metadata from './module.json';
import { registerPropertyContentModule } from '../property-content/registerPropertyContentModule';

registerPropertyContentModule(metadata, {
  moduleClassName: 'propertyhive_divi5_property_street_view',
  outputClassName: 'propertyhive-divi5-property-street-view',
  textAttrName: 'contentText',
  previewKind: 'map',
  sampleValue: '[Street View]',
});
