import metadata from './module.json';
import { registerPropertyMetaModule } from '../property-meta/registerPropertyMetaModule';

registerPropertyMetaModule(metadata, {
  moduleClassName: 'propertyhive_divi5_property_availability',
  outputClassName: 'propertyhive-divi5-property-availability',
  textAttrName: 'availabilityText',
  sampleValue: 'For Sale',
  defaultAfter: '',
  hasIcon: true,
});
