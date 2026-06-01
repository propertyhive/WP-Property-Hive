import metadata from './module.json';
import { registerPropertyContentModule } from '../property-content/registerPropertyContentModule';

registerPropertyContentModule(metadata, {
  moduleClassName: 'propertyhive_divi5_property_epcs',
  outputClassName: 'propertyhive-divi5-property-epcs',
  textAttrName: 'contentText',
  previewKind: 'documentImage',
  sampleValue: 'EPC',
});
