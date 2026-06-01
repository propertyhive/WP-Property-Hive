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

const createPropertyMetaPreview = ({
  moduleClassName,
  outputClassName,
  textAttrName,
  sampleValue,
  defaultAfter = '',
  hasIcon = true,
}) => {
  const Preview = (props) => {
    const { attrs = {} } = props;

    const device = usePreviewDevice();

    const iconAttributeValue = hasIcon ? getIconAttributeValue(attrs?.icon, device, null) : null;
    const icon = hasIcon ? processDiviIcon(iconAttributeValue) : '';
    const iconFontFamily = hasIcon ? getIconFontFamily(iconAttributeValue) : undefined;
    const before = getResponsiveAttrValue(attrs?.before, device, '');
    const after = getResponsiveAttrValue(
      attrs?.after?.innerContent || attrs?.after,
      device,
      defaultAfter
    );

    const textAlign = getResponsiveAttrValue(attrs?.textAlign, device, 'left');
    const textColor = getResponsiveAttrValue(attrs?.textColor, device, '');
    const iconColor = hasIcon ? getResponsiveAttrValue(attrs?.iconColor, device, '') : '';
    const iconSize = hasIcon ? getCssSizeValue(attrs?.iconSize, device, '24px') : '';

    const textStyle = {
      ...getFontDecorationStyle(attrs?.[textAttrName], device),
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
        className={moduleClassName}
      >
        <div className={outputClassName} style={textStyle}>
          {hasIcon && icon !== '' && (
            <span className={`et-pb-icon ${outputClassName}__icon`} style={iconStyle}>{icon}</span>
          )}

          {before !== '' && (
            <span className={`${outputClassName}__before`}>{before} </span>
          )}

          <span className={`${outputClassName}__value`}>{sampleValue}</span>

          {after !== '' && (
            <span className={`${outputClassName}__after`}> {after}</span>
          )}
        </div>
      </ModulePreviewWrapper>
    );
  };

  return Preview;
};

export const registerPropertyMetaModule = (metadata, config) => {
  registerPropertyHiveModule(metadata, {
    preview: createPropertyMetaPreview(config),
  });
};
