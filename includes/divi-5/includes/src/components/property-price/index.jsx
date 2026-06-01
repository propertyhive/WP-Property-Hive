import metadata from './module.json';
import { registerPropertyMetaModule } from '../property-meta/registerPropertyMetaModule';

registerPropertyMetaModule(metadata, {
  moduleClassName: 'propertyhive_divi5_property_price',
  outputClassName: 'propertyhive-divi5-property-price',
  textAttrName: 'priceText',
  sampleValue: '£350,000',
  defaultAfter: '',
  hasIcon: true,
});
