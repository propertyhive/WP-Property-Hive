import metadata from './module.json';
import { registerPropertyMetaModule } from '../property-meta/registerPropertyMetaModule';

registerPropertyMetaModule(metadata, {
  moduleClassName: 'propertyhive_divi5_property_reception_rooms',
  outputClassName: 'propertyhive-divi5-property-reception-rooms',
  textAttrName: 'receptionRoomsText',
  sampleValue: '1',
  defaultAfter: 'reception rooms',
  hasIcon: true,
});
