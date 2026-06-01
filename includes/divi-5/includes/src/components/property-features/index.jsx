import metadata from './module.json';
import { registerPropertyContentModule } from '../property-content/registerPropertyContentModule';

registerPropertyContentModule(metadata, {
  moduleClassName: 'propertyhive_divi5_property_features',
  outputClassName: 'propertyhive-divi5-property-features features',
  textAttrName: 'contentText',
  previewKind: 'features',
  sampleValue: 'Features',
  titleLabel: 'Features',
});
