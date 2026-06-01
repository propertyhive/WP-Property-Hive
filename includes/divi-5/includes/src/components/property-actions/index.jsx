import metadata from './module.json';
import { registerPropertyHiveModule } from '../../shared/register-propertyhive-module';
import ModulePreviewWrapper, {
  getResponsiveAttrValue,
  usePreviewDevice,
} from '../ModulePreviewWrapper';

const getButtonStyle = (attrs, device) => {
  const buttonLayout = getResponsiveAttrValue(
    attrs?.buttonLayout,
    device,
    'inline'
  );

  const buttonFixedWidth = getResponsiveAttrValue(
    attrs?.buttonFixedWidth,
    device,
    '120px'
  );

  const buttonBgColor = getResponsiveAttrValue(
    attrs?.buttonBgColor,
    device,
    ''
  );

  const buttonTextColor = getResponsiveAttrValue(
    attrs?.buttonTextColor,
    device,
    ''
  );

  const buttonPadding = getResponsiveAttrValue(
    attrs?.buttonPadding,
    device,
    '8px 12px'
  );

  const buttonMargin = getResponsiveAttrValue(
    attrs?.buttonMargin,
    device,
    '0 10px 10px 0'
  );

  return {
    listItem: {
      ...(buttonLayout === 'equalWidth' ? { flex: '1 1 0' } : {}),
      ...(buttonLayout === 'fixedWidth' ? { width: buttonFixedWidth } : {}),
      margin: buttonMargin,
    },
    link: {
      display: 'block',
      ...(buttonBgColor ? { backgroundColor: buttonBgColor } : {}),
      ...(buttonTextColor ? { color: buttonTextColor } : {}),
      ...(buttonPadding ? { padding: buttonPadding } : {}),
      ...(buttonLayout === 'equalWidth' ? { textAlign: 'center' } : {}),
    },
  };
};

const getHoverCss = (attrs, device) => {
  const buttonBgHoverColor = getResponsiveAttrValue(
    attrs?.buttonBgHoverColor,
    device,
    ''
  );

  const buttonTextHoverColor = getResponsiveAttrValue(
    attrs?.buttonTextHoverColor,
    device,
    ''
  );

  if (!buttonBgHoverColor && !buttonTextHoverColor) {
    return '';
  }

  return `
    .propertyhive_divi5_property_actions .property_actions.property_actions_buttons ul li a:hover {
      ${buttonBgHoverColor ? `background-color:${buttonBgHoverColor};` : ''}
      ${buttonTextHoverColor ? `color:${buttonTextHoverColor};` : ''}
    }
  `;
};

const Preview = (props) => {
  const { attrs = {} } = props;

  const device = usePreviewDevice();

  const display = getResponsiveAttrValue(
    attrs?.display,
    device,
    'list'
  );

  const buttonLayout = getResponsiveAttrValue(
    attrs?.buttonLayout,
    device,
    'inline'
  );

  const buttonStyle = getButtonStyle(attrs, device);

  const wrapperClassName = [
    'property_actions',
    `property_actions_${display}`,
    display === 'buttons' ? `property_actions_button_layout_${buttonLayout}` : '',
  ].filter(Boolean).join(' ');

  return (
    <ModulePreviewWrapper
      attrs={attrs}
      device={device}
      className="propertyhive_divi5_property_actions"
    >
      <div className={wrapperClassName}>
        {display === 'buttons' && (
          <style>{`
            .propertyhive_divi5_property_actions .property_actions_buttons ul { list-style-type:none; margin:0; padding:0; display:flex; flex-wrap:wrap; }
            .propertyhive_divi5_property_actions .property_actions_buttons.property_actions_button_layout_inline ul { display:block; }
            .propertyhive_divi5_property_actions .property_actions_buttons.property_actions_button_layout_inline ul li { display:inline-block; }
            .propertyhive_divi5_property_actions .property_actions_buttons.property_actions_button_layout_equalWidth ul { display:flex; }
            .propertyhive_divi5_property_actions .property_actions_buttons.property_actions_button_layout_fixedWidth ul li { display:inline-block; }
            ${getHoverCss(attrs, device)}
          `}</style>
        )}

        <ul>
          <li className="action-make-enquiry" style={display === 'buttons' ? buttonStyle.listItem : undefined}><a href="#" style={display === 'buttons' ? buttonStyle.link : undefined}>Make Enquiry</a></li>
          <li className="action-floorplans" style={display === 'buttons' ? buttonStyle.listItem : undefined}><a href="#" style={display === 'buttons' ? buttonStyle.link : undefined}>Floorplan</a></li>
          <li className="action-epc" style={display === 'buttons' ? buttonStyle.listItem : undefined}><a href="#" style={display === 'buttons' ? buttonStyle.link : undefined}>EPC</a></li>
          <li className="action-brochure" style={display === 'buttons' ? buttonStyle.listItem : undefined}><a href="#" style={display === 'buttons' ? buttonStyle.link : undefined}>Brochure</a></li>
          <li className="action-virtual-tour" style={display === 'buttons' ? buttonStyle.listItem : undefined}><a href="#" style={display === 'buttons' ? buttonStyle.link : undefined}>Virtual Tour</a></li>
        </ul>
      </div>
    </ModulePreviewWrapper>
  );
};

registerPropertyHiveModule(metadata, {
  preview: Preview,
});
