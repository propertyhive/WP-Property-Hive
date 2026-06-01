import metadata from './module.json';
import { registerPropertyContentModule } from '../property-content/registerPropertyContentModule';

registerPropertyContentModule(metadata, {
  moduleClassName: 'propertyhive_divi5_property_floor_area',
  outputClassName: 'propertyhive-divi5-property-floor-area',
  textAttrName: 'contentText',
  sampleValue: '1,200 sq ft',
});
