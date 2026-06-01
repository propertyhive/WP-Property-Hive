import metadata from './module.json';
import { registerPropertyContentModule } from '../property-content/registerPropertyContentModule';

registerPropertyContentModule(metadata, {
  moduleClassName: 'propertyhive_divi5_property_summary_description',
  outputClassName: 'propertyhive-divi5-property-summary-description summary',
  textAttrName: 'contentText',
  sampleValue: 'A short summary description of the property will appear here.',
  titleLabel: 'Summary',
});
