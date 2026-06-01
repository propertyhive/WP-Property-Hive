import metadata from './module.json';
import { registerPropertyContentModule } from '../property-content/registerPropertyContentModule';

registerPropertyContentModule(metadata, {
  moduleClassName: 'propertyhive_divi5_property_floorplans',
  outputClassName: 'propertyhive-divi5-property-floorplans',
  textAttrName: 'contentText',
  previewKind: 'documentImage',
  sampleValue: 'Floorplan',
});
