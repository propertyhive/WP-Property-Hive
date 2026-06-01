import metadata from './module.json';
import { registerPropertyContentModule } from '../property-content/registerPropertyContentModule';

registerPropertyContentModule(metadata, {
  moduleClassName: 'propertyhive_divi5_property_images',
  outputClassName: 'propertyhive-divi5-property-images',
  textAttrName: 'contentText',
  previewKind: 'images',
  sampleValue: '[Property images]',
});
