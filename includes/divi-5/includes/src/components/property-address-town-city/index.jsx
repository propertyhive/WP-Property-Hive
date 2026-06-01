import metadata from './module.json';
import { registerPropertyMetaModule } from '../property-meta/registerPropertyMetaModule';

registerPropertyMetaModule(metadata, {
  moduleClassName: 'propertyhive_divi5_property_address_town_city',
  outputClassName: 'propertyhive-divi5-property-address-town-city',
  textAttrName: 'addressTownCityText',
  sampleValue: 'London',
  defaultAfter: '',
  hasIcon: false,
});
