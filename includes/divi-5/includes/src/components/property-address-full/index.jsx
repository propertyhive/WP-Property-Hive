import metadata from './module.json';
import { registerPropertyMetaModule } from '../property-meta/registerPropertyMetaModule';

registerPropertyMetaModule(metadata, {
  moduleClassName: 'propertyhive_divi5_property_address_full',
  outputClassName: 'propertyhive-divi5-property-address-full',
  textAttrName: 'addressFullText',
  sampleValue: '10 High Street, London, SW1A 1AA',
  defaultAfter: '',
  hasIcon: false,
});
