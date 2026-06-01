import metadata from './module.json';
import { registerPropertyHiveModule } from '../../shared/register-propertyhive-module';
import ModulePreviewWrapper, {
  getResponsiveAttrValue,
  getFontDecorationStyle,
  getTextStyle,
  getCssSizeValue,
  usePreviewDevice,
} from '../ModulePreviewWrapper';

const getIconAttributeValue = (attr, device = 'desktop', fallback = null) => {
  const value = getResponsiveAttrValue(attr, device, fallback);

  if (!value) {
    return fallback;
  }

  return value;
};

const processDiviIcon = (iconAttributeValue) => {
  if (!iconAttributeValue) {
    return '';
  }

  const processFontIcon =
    window?.divi?.iconLibrary?.processFontIcon;

  if (typeof processFontIcon === 'function') {
    return processFontIcon(iconAttributeValue) || '';
  }

  if (typeof iconAttributeValue === 'string') {
    return iconAttributeValue;
  }

  return (
    iconAttributeValue?.decodedUnicode ||
    iconAttributeValue?.unicode ||
    iconAttributeValue?.icon ||
    ''
  );
};

const getIconFontFamily = (iconAttributeValue) => {
  const getFontFamily =
    window?.divi?.iconLibrary?.getIconFontFamily;

  if (typeof getFontFamily === 'function') {
    return getFontFamily(iconAttributeValue) || undefined;
  }

  return undefined;
};


const Preview = (props) => {
  const { attrs = {} } = props;

  const device = usePreviewDevice();

  const iconAttributeValue = getIconAttributeValue(attrs?.icon, device, null);
  const icon = processDiviIcon(iconAttributeValue);
  const iconFontFamily = getIconFontFamily(iconAttributeValue);
  const before = getResponsiveAttrValue(attrs?.before, device, '');
  const after = getResponsiveAttrValue(
    attrs?.after?.innerContent || attrs?.after,
    device,
    'bedrooms'
  );

  const textAlign = getResponsiveAttrValue(attrs?.textAlign, device, 'left');
  const textColor = getResponsiveAttrValue(attrs?.textColor, device, '');
  const iconColor = getResponsiveAttrValue(attrs?.iconColor, device, '');
  const iconSize = getCssSizeValue(attrs?.iconSize, device, '24px');

  const textStyle = {
    ...getFontDecorationStyle(attrs?.bedroomsText, device),
    ...getTextStyle({
      textAlign,
      color: textColor,
    }),
  };

  const iconStyle = {
    ...(iconColor ? { color: iconColor } : {}),
    ...(iconSize ? { fontSize: iconSize } : {}),
    ...(iconFontFamily ? { fontFamily: iconFontFamily } : {}),
    verticalAlign: 'middle',
    marginRight: '7px',
  };



  return (
    <ModulePreviewWrapper
      attrs={attrs}
      device={device}
      className="propertyhive_divi5_property_bedrooms"
    >
      <div className="propertyhive-divi5-property-bedrooms" style={textStyle}>
        {icon !== '' && (
          <span className="et-pb-icon propertyhive-divi5-property-bedrooms__icon" style={iconStyle}>{icon}</span>
        )}

        {before !== '' && (
          <span className="propertyhive-divi5-property-bedrooms__before">{before} </span>
        )}

        <span className="propertyhive-divi5-property-bedrooms__value">3</span>

        {after !== '' && (
          <span className="propertyhive-divi5-property-bedrooms__after"> {after}</span>
        )}
      </div>
    </ModulePreviewWrapper>
  );
};

registerPropertyHiveModule(metadata, {
  preview: Preview,
});
