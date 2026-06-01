import metadata from './module.json';
import { registerPropertyContentModule } from '../property-content/registerPropertyContentModule';

registerPropertyContentModule(metadata, {
  moduleClassName: 'propertyhive_divi5_property_location',
  outputClassName: 'propertyhive-divi5-property-location',
  textAttrName: 'contentText',
  sampleValue: 'London',
});
