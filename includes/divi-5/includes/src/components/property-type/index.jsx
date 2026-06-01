import metadata from './module.json';
import { registerPropertyMetaModule } from '../property-meta/registerPropertyMetaModule';

registerPropertyMetaModule(metadata, {
  moduleClassName: 'propertyhive_divi5_property_type',
  outputClassName: 'propertyhive-divi5-property-type',
  textAttrName: 'propertyTypeText',
  sampleValue: 'Detached',
  defaultAfter: '',
  hasIcon: true,
});
