import metadata from './module.json';
import { registerPropertyMetaModule } from '../property-meta/registerPropertyMetaModule';

registerPropertyMetaModule(metadata, {
  moduleClassName: 'propertyhive_divi5_property_bedrooms',
  outputClassName: 'propertyhive-divi5-property-bedrooms',
  textAttrName: 'bedroomsText',
  sampleValue: '3',
  defaultAfter: 'bedrooms',
  hasIcon: true,
});
