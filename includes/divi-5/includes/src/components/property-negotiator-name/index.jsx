import metadata from './module.json';
import { registerPropertyContentModule } from '../property-content/registerPropertyContentModule';

registerPropertyContentModule(metadata, {
  moduleClassName: 'propertyhive_divi5_property_negotiator_name',
  outputClassName: 'propertyhive-divi5-property-negotiator-name',
  textAttrName: 'contentText',
  sampleValue: 'Alex Smith',
});
