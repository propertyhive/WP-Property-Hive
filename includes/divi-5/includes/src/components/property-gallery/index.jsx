import metadata from './module.json';
import { registerPropertyContentModule } from '../property-content/registerPropertyContentModule';

registerPropertyContentModule(metadata, {
  moduleClassName: 'propertyhive_divi5_property_gallery',
  outputClassName: 'propertyhive-divi5-property-gallery',
  textAttrName: 'contentText',
  previewKind: 'gallery',
  sampleValue: '[Property gallery]',
});
