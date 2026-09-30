import metadata from './module.json';
import { registerPropertyMetaModule } from '../property-meta/registerPropertyMetaModule';

registerPropertyMetaModule(metadata, {
  moduleClassName: 'propertyhive_divi5_property_floor_area',
  outputClassName: 'propertyhive-divi5-property-floor-area',
  textAttrName: 'contentText',
  sampleValue: '1,200 sq ft',
  defaultAfter: '',
  hasIcon: true,
});
