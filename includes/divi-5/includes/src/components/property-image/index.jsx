import metadata from './module.json';
import { registerPropertyContentModule } from '../property-content/registerPropertyContentModule';

registerPropertyContentModule(metadata, {
  moduleClassName: 'propertyhive_divi5_property_image',
  outputClassName: 'propertyhive-divi5-property-image',
  textAttrName: 'contentText',
  previewKind: 'image',
  sampleValue: '[Property image]',
});
