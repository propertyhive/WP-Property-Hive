import metadata from './module.json';
import { registerPropertyContentModule } from '../property-content/registerPropertyContentModule';

registerPropertyContentModule(metadata, {
  moduleClassName: 'propertyhive_divi5_property_negotiator_email_address',
  outputClassName: 'propertyhive-divi5-property-negotiator-email-address',
  textAttrName: 'contentText',
  sampleValue: 'agent@example.com',
});
