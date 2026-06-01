import metadata from './module.json';
import { registerPropertyContentModule } from '../property-content/registerPropertyContentModule';

registerPropertyContentModule(metadata, {
  moduleClassName: 'propertyhive_divi5_property_office_email_address',
  outputClassName: 'propertyhive-divi5-property-office-email-address',
  textAttrName: 'contentText',
  sampleValue: 'office@example.com',
});
