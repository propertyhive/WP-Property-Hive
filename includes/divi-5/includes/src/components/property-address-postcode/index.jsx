import metadata from './module.json';
import { registerPropertyMetaModule } from '../property-meta/registerPropertyMetaModule';

registerPropertyMetaModule(metadata, {
  moduleClassName: 'propertyhive_divi5_property_address_postcode',
  outputClassName: 'propertyhive-divi5-property-address-postcode',
  textAttrName: 'addressPostcodeText',
  sampleValue: 'SW1A 1AA',
  defaultAfter: '',
  hasIcon: false,
});
