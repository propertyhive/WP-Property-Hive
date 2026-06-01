import metadata from './module.json';
import { registerPropertyContentModule } from '../property-content/registerPropertyContentModule';

registerPropertyContentModule(metadata, {
  moduleClassName: 'propertyhive_divi5_property_deposit',
  outputClassName: 'propertyhive-divi5-property-deposit',
  textAttrName: 'contentText',
  sampleValue: '£1,500 deposit',
});
