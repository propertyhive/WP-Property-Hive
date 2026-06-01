import metadata from './module.json';
import { registerPropertyContentModule } from '../property-content/registerPropertyContentModule';

registerPropertyContentModule(metadata, {
  moduleClassName: 'propertyhive_divi5_property_enquiry_form',
  outputClassName: 'propertyhive-divi5-property-enquiry-form',
  textAttrName: 'contentText',
  previewKind: 'enquiry',
  sampleValue: '[Property enquiry form]',
});
