import metadata from './module.json';
import { registerPropertyMetaModule } from '../property-meta/registerPropertyMetaModule';

registerPropertyMetaModule(metadata, {
  moduleClassName: 'propertyhive_divi5_property_reference_number',
  outputClassName: 'propertyhive-divi5-property-reference-number',
  textAttrName: 'referenceNumberText',
  sampleValue: 'ABC123',
  defaultAfter: '',
  hasIcon: true,
});
