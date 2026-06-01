import metadata from './module.json';
import { registerPropertyContentModule } from '../property-content/registerPropertyContentModule';

registerPropertyContentModule(metadata, {
  moduleClassName: 'propertyhive_divi5_property_map_link',
  outputClassName: 'propertyhive-divi5-property-map-link',
  textAttrName: 'contentText',
  sampleValue: 'View Map',
});
