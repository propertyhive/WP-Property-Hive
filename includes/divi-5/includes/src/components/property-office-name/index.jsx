import metadata from './module.json';
import { registerPropertyContentModule } from '../property-content/registerPropertyContentModule';

registerPropertyContentModule(metadata, {
  moduleClassName: 'propertyhive_divi5_property_office_name',
  outputClassName: 'propertyhive-divi5-property-office-name',
  textAttrName: 'contentText',
  sampleValue: 'Property Hive Office',
});
