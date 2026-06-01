import metadata from './module.json';
import { registerPropertyContentModule } from '../property-content/registerPropertyContentModule';

registerPropertyContentModule(metadata, {
  moduleClassName: 'propertyhive_divi5_back_to_search',
  outputClassName: 'propertyhive-divi5-back-to-search',
  textAttrName: 'contentText',
  sampleValue: '← Back to search',
});
