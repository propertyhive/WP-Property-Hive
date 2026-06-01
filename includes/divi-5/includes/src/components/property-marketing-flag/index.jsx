import metadata from './module.json';
import { registerPropertyContentModule } from '../property-content/registerPropertyContentModule';

registerPropertyContentModule(metadata, {
  moduleClassName: 'propertyhive_divi5_property_marketing_flag',
  outputClassName: 'propertyhive-divi5-property-marketing-flag',
  textAttrName: 'contentText',
  sampleValue: 'For Sale',
});
