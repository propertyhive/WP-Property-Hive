import metadata from './module.json';
import { registerPropertyContentModule } from '../property-content/registerPropertyContentModule';

registerPropertyContentModule(metadata, {
  moduleClassName: 'propertyhive_divi5_property_furnished',
  outputClassName: 'propertyhive-divi5-property-furnished',
  textAttrName: 'contentText',
  sampleValue: 'Furnished',
});
