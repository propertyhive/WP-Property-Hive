import metadata from './module.json';
import { registerPropertyMetaModule } from '../property-meta/registerPropertyMetaModule';

registerPropertyMetaModule(metadata, {
  moduleClassName: 'propertyhive_divi5_property_bathrooms',
  outputClassName: 'propertyhive-divi5-property-bathrooms',
  textAttrName: 'bathroomsText',
  sampleValue: '3',
  defaultAfter: 'bathrooms',
  hasIcon: true,
});
