import metadata from './module.json';
import { registerPropertyMetaModule } from '../property-meta/registerPropertyMetaModule';

registerPropertyMetaModule(metadata, {
  moduleClassName: 'propertyhive_divi5_property_council_tax_band',
  outputClassName: 'propertyhive-divi5-property-council-tax-band',
  textAttrName: 'councilTaxBandText',
  sampleValue: 'C',
  defaultAfter: '',
  hasIcon: true,
});
