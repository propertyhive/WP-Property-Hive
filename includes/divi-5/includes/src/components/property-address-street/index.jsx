import metadata from './module.json';
import { registerPropertyMetaModule } from '../property-meta/registerPropertyMetaModule';

registerPropertyMetaModule(metadata, {
  moduleClassName: 'propertyhive_divi5_property_address_street',
  outputClassName: 'propertyhive-divi5-property-address-street',
  textAttrName: 'addressStreetText',
  sampleValue: 'High Street',
  defaultAfter: '',
  hasIcon: false,
});
