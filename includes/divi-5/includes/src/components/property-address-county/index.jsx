import metadata from './module.json';
import { registerPropertyMetaModule } from '../property-meta/registerPropertyMetaModule';

registerPropertyMetaModule(metadata, {
  moduleClassName: 'propertyhive_divi5_property_address_county',
  outputClassName: 'propertyhive-divi5-property-address-county',
  textAttrName: 'addressCountyText',
  sampleValue: 'Greater London',
  defaultAfter: '',
  hasIcon: false,
});
