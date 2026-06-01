import metadata from './module.json';
import { registerPropertyContentModule } from '../property-content/registerPropertyContentModule';

registerPropertyContentModule(metadata, {
  moduleClassName: 'propertyhive_divi5_property_additional_field',
  outputClassName: 'propertyhive-divi5-property-additional-field',
  textAttrName: 'contentText',
  sampleValue: 'Additional field value',
});
