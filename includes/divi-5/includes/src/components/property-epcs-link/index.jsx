import metadata from './module.json';
import { registerPropertyContentModule } from '../property-content/registerPropertyContentModule';

registerPropertyContentModule(metadata, {
  moduleClassName: 'propertyhive_divi5_property_epcs_link',
  outputClassName: 'propertyhive-divi5-property-epcs-link',
  textAttrName: 'contentText',
  sampleValue: 'View EPC',
});
