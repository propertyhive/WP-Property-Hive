import metadata from './module.json';
import { registerPropertyContentModule } from '../property-content/registerPropertyContentModule';

registerPropertyContentModule(metadata, {
  moduleClassName: 'propertyhive_divi5_property_price_qualifier',
  outputClassName: 'propertyhive-divi5-property-price-qualifier',
  textAttrName: 'contentText',
  sampleValue: 'Guide Price',
});
