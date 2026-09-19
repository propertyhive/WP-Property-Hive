import propertyActionsMetadata from './modules/PropertyActions/module.json';
import propertyActionsDefaultAttrs from './modules/PropertyActions/module-default-render-attributes.json';
import propertyActionsDefaultPrintedStyleAttrs from './modules/PropertyActions/module-default-printed-style-attributes.json';
import propertyActionsConversionOutline from './modules/PropertyActions/conversion-outline.json';

const namespace = 'propertyhive/property-actions';

const getReact = () => window?.vendor?.React || window?.React;

const getDiviModule = () => window?.divi?.module || {};

const getI18n = () => window?.vendor?.wp?.i18n || window?.wp?.i18n || {};

const translate = (text) => {
  const { __ } = getI18n();

  return typeof __ === 'function' ? __(text, 'propertyhive') : text;
};

const getCurrentPostId = () => {
  const settingsStore = window?.divi?.data?.select?.('divi/settings');
  const postSetting = settingsStore?.getSetting?.(['post']);

  return (
    settingsStore?.getSetting?.(['post', 'id'])
    || postSetting?.id
    || window?.ETBuilderBackend?.postId
    || 0
  );
};

const hasRendererDependencies = () => {
  const React = getReact();
  const diviModule = getDiviModule();

  return Boolean(
    React?.createElement
      && React?.useEffect
      && React?.useMemo
      && React?.useState
      && diviModule?.CssStyle
      && diviModule?.ElementComponents
      && diviModule?.ModuleContainer
      && diviModule?.StyleContainer
  );
};

const getRestEndpoint = () => {
  const root = window?.wpApiSettings?.root || `${window.location.origin}/wp-json/`;

  return `${root.replace(/\/$/, '')}/propertyhive/v1/divi/property-actions-preview`;
};

const normalizeAttrValue = (value, fallback = '') => {
  if (value && 'object' === typeof value && !Array.isArray(value)) {
    if (Object.prototype.hasOwnProperty.call(value, 'desktop')) {
      return normalizeAttrValue(value.desktop, fallback);
    }

    if (Object.prototype.hasOwnProperty.call(value, 'value')) {
      return normalizeAttrValue(value.value, fallback);
    }

    if (Object.prototype.hasOwnProperty.call(value, 'color')) {
      return normalizeAttrValue(value.color, fallback);
    }

    return fallback;
  }

  if (null === value || undefined === value) {
    return fallback;
  }

  const normalized = String(value).trim();

  return normalized ? normalized : fallback;
};

const getAttrValue = (attrs, path, fallback = '') => {
  let value = attrs;

  for (const pathPart of path) {
    if (!value || 'object' !== typeof value || !Object.prototype.hasOwnProperty.call(value, pathPart)) {
      return fallback;
    }

    value = value[pathPart];
  }

  return normalizeAttrValue(value, fallback);
};

const getResponsiveAttrProperty = (attrs, path, property, fallback = '') => {
  let value = attrs;

  for (const pathPart of path) {
    if (!value || 'object' !== typeof value || !Object.prototype.hasOwnProperty.call(value, pathPart)) {
      return fallback;
    }

    value = value[pathPart];
  }

  const desktopValue = value?.desktop?.value || value;

  if (!desktopValue || 'object' !== typeof desktopValue || !Object.prototype.hasOwnProperty.call(desktopValue, property)) {
    return fallback;
  }

  return normalizeAttrValue(desktopValue[property], fallback);
};

const getDisplay = (attrs) => {
  const legacyDisplay = attrs?.display;
  const display = legacyDisplay || getAttrValue(attrs, ['propertyActions', 'advanced', 'display'], 'list');

  return 'buttons' === display ? 'buttons' : 'list';
};

const sanitizeCssValue = (value, fallback) => {
  const clean = normalizeAttrValue(value, fallback)
    .replace(/<[^>]*>/g, '')
    .replace(/[^#a-zA-Z0-9\s.,()%+\-\/]/g, '')
    .trim();

  return clean || fallback;
};

const getModuleSizingCss = (attrs) => {
  const sizingPath = ['module', 'decoration', 'sizing'];
  const sizingProperties = {
    width: 'width',
    minWidth: 'min-width',
    maxWidth: 'max-width',
    height: 'height',
    minHeight: 'min-height',
    maxHeight: 'max-height',
  };

  return Object.entries(sizingProperties)
    .map(([attrProperty, cssProperty]) => {
      const value = getResponsiveAttrProperty(attrs, sizingPath, attrProperty, '');
      const normalized = String(value || '').toLowerCase();

      if (!normalized || 'auto' === normalized || 'none' === normalized) {
        return '';
      }

      return `${cssProperty}:${sanitizeCssValue(value, '')};`;
    })
    .filter(Boolean)
    .join('');
};

const getModuleSizingStyle = (attrs) => {
  const sizingPath = ['module', 'decoration', 'sizing'];
  const sizingProperties = {
    width: 'width',
    minWidth: 'minWidth',
    maxWidth: 'maxWidth',
    height: 'height',
    minHeight: 'minHeight',
    maxHeight: 'maxHeight',
  };

  return Object.entries(sizingProperties).reduce((style, [attrProperty, styleProperty]) => {
    const value = getResponsiveAttrProperty(attrs, sizingPath, attrProperty, '');
    const normalized = String(value || '').toLowerCase();

    if (!normalized || 'auto' === normalized || 'none' === normalized) {
      return style;
    }

    return {
      ...style,
      [styleProperty]: sanitizeCssValue(value, ''),
    };
  }, {});
};

const getPreviewSizingStyle = (attrs) => ({
  width: '100%',
  height: '100%',
  minHeight: 'inherit',
  maxHeight: 'inherit',
  boxSizing: 'border-box',
  ...getModuleSizingStyle(attrs),
});

const normalizeSelector = (selector) => {
  const cleanSelector = String(selector || '').trim();

  if (!cleanSelector) {
    return '.et_pb_property_actions_widget';
  }

  return cleanSelector.startsWith('.') ? cleanSelector : `.${cleanSelector}`;
};

const hasModuleWidthSetting = (attrs) => ['width', 'maxWidth', 'minWidth'].some((property) => {
  const value = getResponsiveAttrProperty(attrs, ['module', 'decoration', 'sizing'], property, '');
  const normalized = String(value || '').toLowerCase();

  return normalized && 'auto' !== normalized && 'none' !== normalized;
});

const getButtonStyleCss = (attrs, selector, options = {}) => {
  if ('buttons' !== getDisplay(attrs)) {
    return '';
  }

  const moduleSelector = normalizeSelector(selector);
  const rootSelector = `${moduleSelector} .property_actions`;
  const fillModuleWidth = hasModuleWidthSetting(attrs);
  const heightFillCss = 'height:100%;min-height:inherit;max-height:inherit;';
  const mirroredSizingCss = options.mirrorModuleSizing ? getModuleSizingCss(attrs) : '';
  const buttonBackgroundColor = sanitizeCssValue(
    getAttrValue(attrs, ['propertyActions', 'decoration', 'buttonBackgroundColor'], '#000000'),
    '#000000'
  );
  const buttonTextColor = sanitizeCssValue(
    getAttrValue(attrs, ['propertyActions', 'decoration', 'buttonTextColor'], '#ffffff'),
    '#ffffff'
  );
  const buttonPadding = sanitizeCssValue(
    getAttrValue(attrs, ['propertyActions', 'decoration', 'buttonPadding'], '10px 15px'),
    '10px 15px'
  );
  const buttonMargin = sanitizeCssValue(
    getAttrValue(attrs, ['propertyActions', 'decoration', 'buttonMargin'], '0 5px 0 0'),
    '0 5px 0 0'
  );

  return [
    options.mirrorModuleSizing ? `${moduleSelector}{width:100%;box-sizing:border-box;${heightFillCss}${mirroredSizingCss}}` : '',
    `${rootSelector}{${heightFillCss}}`,
    `${rootSelector} ul{list-style-type:none!important;margin:0!important;padding:0!important;${fillModuleWidth ? 'width:100%;' : ''}${heightFillCss}}`,
    `${rootSelector} ul li{display:inline-block;margin:0!important;padding:0!important;${fillModuleWidth ? 'width:100%;box-sizing:border-box;' : ''}${heightFillCss}}`,
    `${rootSelector} ul li a{display:flex;background:${buttonBackgroundColor}!important;color:${buttonTextColor}!important;padding:${buttonPadding}!important;margin:${buttonMargin}!important;text-decoration:none;${fillModuleWidth ? 'width:100%;box-sizing:border-box;text-align:center;' : 'box-sizing:border-box;'}${heightFillCss}align-items:center;justify-content:center;}`,
  ].filter(Boolean).join('');
};

const getPreviewClassName = (id) => {
  const cleanId = String(id || 'property-actions-preview')
    .replace(/[^a-zA-Z0-9_-]/g, '-')
    .replace(/^-+|-+$/g, '');

  return `property-actions-preview-${cleanId || 'module'}`;
};

const PropertyActionsStyles = ({
  settings,
  mode,
  state,
  noStyleTag,
  elements,
  attrs,
  orderClass,
}) => {
  const React = getReact();
  const {
    CssStyle,
    StyleContainer,
  } = getDiviModule();
  const buttonStyleCss = getButtonStyleCss(attrs, orderClass);

  return React.createElement(
    StyleContainer,
    {
      mode,
      state,
      noStyleTag,
    },
    elements.style({
      attrName: 'module',
      styleProps: {
        disabledOn: {
          disabledModuleVisibility: settings?.disabledModuleVisibility,
        },
      },
    }),
    React.createElement(CssStyle, {
      selector: orderClass,
      attr: attrs?.css || {},
      cssFields: propertyActionsMetadata.customCssFields,
    }),
    buttonStyleCss
      ? React.createElement('style', { key: 'property-actions-buttons' }, buttonStyleCss)
      : null
  );
};

const propertyActionsClassnames = ({ classnamesInstance }) => {
  classnamesInstance.add('et_pb_property_actions_widget');
};

const PropertyActionsScriptData = ({ elements }) => {
  elements.scriptData({
    attrName: 'module',
  });
};

const PropertyActionsEdit = (props) => {
  const React = getReact();
  const {
    ElementComponents,
    ModuleContainer,
  } = getDiviModule();
  const {
    attrs,
    id,
    name,
    elements,
  } = props;

  const postId = getCurrentPostId();
  const [previewHtml, setPreviewHtml] = React.useState('');
  const [isLoading, setIsLoading] = React.useState(false);

  React.useEffect(() => {
    const fetchAbortController = new AbortController();
    const headers = {
      'Content-Type': 'application/json',
    };
    const nonce = window?.wpApiSettings?.nonce;

    if (nonce) {
      headers['X-WP-Nonce'] = nonce;
    }

    setIsLoading(true);

    window.fetch(getRestEndpoint(), {
      method: 'POST',
      credentials: 'same-origin',
      headers,
      body: JSON.stringify({
        attrs: {},
        post_id: postId,
      }),
      signal: fetchAbortController.signal,
    })
      .then((response) => {
        if (!response.ok) {
          throw new Error(`Property Actions preview request failed: ${response.status}`);
        }

        return response.json();
      })
      .then((response) => {
        setPreviewHtml(response?.html || '');
      })
      .catch((error) => {
        if ('AbortError' !== error?.name) {
          // eslint-disable-next-line no-console
          console.error(error);
        }
      })
      .finally(() => {
        if (!fetchAbortController.signal.aborted) {
          setIsLoading(false);
        }
      });

    return () => {
      fetchAbortController.abort();
    };
  }, [postId]);

  const previewClassName = React.useMemo(() => getPreviewClassName(id), [id]);
  const previewButtonStyles = getButtonStyleCss(attrs, `.${previewClassName}`, {
    mirrorModuleSizing: true,
  });
  const previewSizingStyle = getPreviewSizingStyle(attrs);

  const children = [
    elements.styleComponents({
      attrName: 'module',
    }),
    React.createElement(ElementComponents, {
      attrs: attrs?.module?.decoration || {},
      id,
    }),
  ];

  if (previewButtonStyles) {
    children.push(
      React.createElement('style', { key: 'preview-button-styles' }, previewButtonStyles)
    );
  }

  if (isLoading && !previewHtml) {
    children.push(
      React.createElement('div', { key: 'loading' }, translate('Loading...'))
    );
  } else if (previewHtml) {
    children.push(
      React.createElement('div', {
        key: 'preview',
        className: previewClassName,
        style: previewSizingStyle,
        dangerouslySetInnerHTML: {
          __html: previewHtml,
        },
      })
    );
  } else {
    children.push(
      React.createElement('div', {
        key: 'empty',
        className: previewClassName,
        style: previewSizingStyle,
      }, React.createElement('div', {
        className: 'property_actions',
      }))
    );
  }

  return React.createElement(
    ModuleContainer,
    {
      attrs,
      elements,
      id,
      name,
      stylesComponent: PropertyActionsStyles,
      classnamesFunction: propertyActionsClassnames,
      scriptDataComponent: PropertyActionsScriptData,
    },
    children
  );
};

const registerPropertyActions = () => {
  const moduleLibrary = window?.divi?.moduleLibrary;

  if (
    !moduleLibrary
    || typeof moduleLibrary.registerModule !== 'function'
    || !hasRendererDependencies()
  ) {
    return false;
  }

  window.propertyHiveDiviModules = window.propertyHiveDiviModules || {};

  if (window.propertyHiveDiviModules.propertyActions) {
    return true;
  }

  try {
    moduleLibrary.registerModule(propertyActionsMetadata, {
      defaultAttrs: propertyActionsDefaultAttrs,
      defaultPrintedStyleAttrs: propertyActionsDefaultPrintedStyleAttrs,
      conversionOutline: propertyActionsConversionOutline,
      renderers: {
        edit: PropertyActionsEdit,
        styles: PropertyActionsStyles,
      },
    });
  } catch (error) {
    window.propertyHiveDiviModules.propertyActionsRegistrationError = error;

    return false;
  }

  window.propertyHiveDiviModules.propertyActions = true;
  delete window.propertyHiveDiviModules.propertyActionsRegistrationError;

  return true;
};

const registerWhenReady = (attempt = 0) => {
  if (registerPropertyActions()) {
    return;
  }

  const hooks = window?.vendor?.wp?.hooks || window?.wp?.hooks;

  if (attempt === 0 && hooks?.addAction) {
    hooks.addAction(
      'divi.moduleLibrary.registerModuleLibraryStore.after',
      namespace,
      registerPropertyActions
    );
  }

  if (attempt < 80) {
    window.setTimeout(() => registerWhenReady(attempt + 1), 250);
  } else if (window.propertyHiveDiviModules?.propertyActionsRegistrationError) {
    // eslint-disable-next-line no-console
    console.error(window.propertyHiveDiviModules.propertyActionsRegistrationError);
  }
};

registerWhenReady();
