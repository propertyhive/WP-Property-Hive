import metadata from './module.json';
import { registerPropertyContentModule } from '../property-content/registerPropertyContentModule';

registerPropertyContentModule(metadata, {
  moduleClassName: 'propertyhive_divi5_property_floorplans_link',
  outputClassName: 'propertyhive-divi5-property-floorplans-link',
  textAttrName: 'contentText',
  sampleValue: 'View Floorplan',
});
