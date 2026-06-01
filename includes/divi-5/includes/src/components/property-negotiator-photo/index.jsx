import metadata from './module.json';
import { registerPropertyContentModule } from '../property-content/registerPropertyContentModule';

registerPropertyContentModule(metadata, {
  moduleClassName: 'propertyhive_divi5_property_negotiator_photo',
  outputClassName: 'propertyhive-divi5-property-negotiator-photo',
  textAttrName: 'contentText',
  previewKind: 'photo',
  sampleValue: '[Negotiator photo]',
});
