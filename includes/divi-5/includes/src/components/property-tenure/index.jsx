import metadata from './module.json';
import { registerPropertyMetaModule } from '../property-meta/registerPropertyMetaModule';

registerPropertyMetaModule(metadata, {
  moduleClassName: 'propertyhive_divi5_property_tenure',
  outputClassName: 'propertyhive-divi5-property-tenure',
  textAttrName: 'tenureText',
  sampleValue: 'Freehold',
  defaultAfter: '',
  hasIcon: true,
});
