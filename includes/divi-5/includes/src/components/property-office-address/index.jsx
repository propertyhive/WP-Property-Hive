import metadata from './module.json';
import { registerPropertyContentModule } from '../property-content/registerPropertyContentModule';

registerPropertyContentModule(metadata, {
  moduleClassName: 'propertyhive_divi5_property_office_address',
  outputClassName: 'propertyhive-divi5-property-office-address',
  textAttrName: 'contentText',
  sampleValue: '1 High Street, London',
});
