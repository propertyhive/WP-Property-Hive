import metadata from './module.json';
import { registerPropertyContentModule } from '../property-content/registerPropertyContentModule';

registerPropertyContentModule(metadata, {
  moduleClassName: 'propertyhive_divi5_property_full_description',
  outputClassName: 'propertyhive-divi5-property-full-description description',
  textAttrName: 'contentText',
  sampleValue: 'The full property description will appear here.',
  titleLabel: 'Description',
});
