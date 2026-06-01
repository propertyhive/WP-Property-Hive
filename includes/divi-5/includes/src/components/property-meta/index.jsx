import metadata from './module.json';
import { registerPropertyHiveModule } from '../../shared/register-propertyhive-module';
import ModulePreviewWrapper, {
  getResponsiveAttrValue,
  getFontDecorationStyle,
  getTextStyle,
  usePreviewDevice,
} from '../ModulePreviewWrapper';

const Preview = (props) => {
  const { attrs = {} } = props;
  const device = usePreviewDevice();

  const textAlign = getResponsiveAttrValue(attrs?.textAlign, device, 'left');
  const textColor = getResponsiveAttrValue(attrs?.textColor, device, '');

  const textStyle = {
    ...getFontDecorationStyle(attrs?.contentText, device),
    ...getTextStyle({
      textAlign,
      color: textColor,
    }),
  };

  return (
    <ModulePreviewWrapper
      attrs={attrs}
      device={device}
      className="propertyhive_divi5_property_meta"
    >
      <div className="propertyhive-divi5-property-meta" style={textStyle}>
        <ul className="propertyhive-divi5-property-meta-list">
          <li><span className="propertyhive-divi5-property-meta-list__label">Reference Number:</span> <span className="propertyhive-divi5-property-meta-list__value">xxx</span></li>
          <li><span className="propertyhive-divi5-property-meta-list__label">Bedrooms:</span> <span className="propertyhive-divi5-property-meta-list__value">4</span></li>
          <li><span className="propertyhive-divi5-property-meta-list__label">Bathrooms:</span> <span className="propertyhive-divi5-property-meta-list__value">2</span></li>
          <li><span className="propertyhive-divi5-property-meta-list__label">Reception rooms:</span> <span className="propertyhive-divi5-property-meta-list__value">2</span></li>
          <li><span className="propertyhive-divi5-property-meta-list__label">Property Type:</span> <span className="propertyhive-divi5-property-meta-list__value">Flat</span></li>
          <li><span className="propertyhive-divi5-property-meta-list__label">Availability:</span> <span className="propertyhive-divi5-property-meta-list__value">For Sale</span></li>
        </ul>
      </div>
    </ModulePreviewWrapper>
  );
};

registerPropertyHiveModule(metadata, {
  preview: Preview,
});
