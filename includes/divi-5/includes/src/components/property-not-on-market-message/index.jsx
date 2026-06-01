import metadata from './module.json';
import { registerPropertyContentModule } from '../property-content/registerPropertyContentModule';

registerPropertyContentModule(metadata, {
  moduleClassName: 'propertyhive_divi5_property_not_on_market_message',
  outputClassName: 'propertyhive-divi5-property-not-on-market-message',
  textAttrName: 'contentText',
  sampleValue: 'This property is not currently available.',
});
