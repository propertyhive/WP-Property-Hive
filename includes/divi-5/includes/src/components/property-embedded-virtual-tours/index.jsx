import metadata from './module.json';
import { registerPropertyContentModule } from '../property-content/registerPropertyContentModule';

registerPropertyContentModule(metadata, {
  moduleClassName: 'propertyhive_divi5_property_embedded_virtual_tours',
  outputClassName: 'propertyhive-divi5-property-embedded-virtual-tours',
  textAttrName: 'contentText',
  previewKind: 'map',
  sampleValue: 'Embedded Virtual Tour Placeholder',
});
