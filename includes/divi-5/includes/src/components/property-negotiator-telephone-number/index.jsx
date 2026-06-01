import metadata from './module.json';
import { registerPropertyContentModule } from '../property-content/registerPropertyContentModule';

registerPropertyContentModule(metadata, {
  moduleClassName: 'propertyhive_divi5_property_negotiator_telephone_number',
  outputClassName: 'propertyhive-divi5-property-negotiator-telephone-number',
  textAttrName: 'contentText',
  sampleValue: '01234 567890',
});
