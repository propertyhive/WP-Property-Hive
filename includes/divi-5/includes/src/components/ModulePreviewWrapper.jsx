import React from 'react';

const { useEffect, useState } = window.vendor.wp.element;

export const normalizeDevice = (device) => {
  if (device === 'mobile') {
    return 'phone';
  }

  return device || 'desktop';
};

export const getPreviewDevice = () => {
  const currentDocView =
    document?.body?.getAttribute('data-app-active-view');

  const parentDocView =
    window?.parent?.document?.body?.getAttribute('data-app-active-view');

  const topDocView =
    window?.top?.document?.body?.getAttribute('data-app-active-view');

  return normalizeDevice(
    currentDocView || parentDocView || topDocView || 'desktop'
  );
};

export const usePreviewDevice = () => {
  const [device, setDevice] = useState(getPreviewDevice());

  useEffect(() => {
    const updateDevice = () => {
      setDevice(getPreviewDevice());
    };

    updateDevice();

    const bodies = [
      document?.body,
      window?.parent?.document?.body,
      window?.top?.document?.body,
    ].filter(Boolean);

    const observers = bodies.map((body) => {
      const observer = new MutationObserver(updateDevice);

      observer.observe(body, {
        attributes: true,
        attributeFilter: ['data-app-active-view'],
      });

      return observer;
    });

    return () => {
      observers.forEach((observer) => observer.disconnect());
    };
  }, []);

  return device;
};

export const getResponsiveAttrValue = (
  attr,
  device = 'desktop',
  fallback = undefined
) => {
  return (
    attr?.[device]?.value ??
    attr?.desktop?.value ??
    attr?.value ??
    attr ??
    fallback
  );
};

export const toCssUnit = (value) => {
  if (value === undefined || value === null || value === '') {
    return undefined;
  }

  return typeof value === 'number' ? `${value}px` : value;
};

export const getTextStyle = ({
  textAlign,
  color,
  fontSize,
  fontFamily,
  fontWeight,
} = {}) => ({
  ...(textAlign ? { textAlign } : {}),
  ...(color ? { color } : {}),
  ...(fontSize ? { fontSize: toCssUnit(fontSize) } : {}),
  ...(fontFamily ? { fontFamily } : {}),
  ...(fontWeight ? { fontWeight } : {}),
});


export const getDeepValueByKeys = (value, keys = []) => {
  if (!value || typeof value !== 'object') {
    return undefined;
  }

  for (const key of keys) {
    if (value[key] !== undefined && value[key] !== null && value[key] !== '') {
      return value[key];
    }
  }

  for (const child of Object.values(value)) {
    if (child && typeof child === 'object') {
      const found = getDeepValueByKeys(child, keys);

      if (found !== undefined && found !== null && found !== '') {
        return found;
      }
    }
  }

  return undefined;
};


export const getCssSizeValue = (attr, device = 'desktop', fallback = '') => {
  const value = getResponsiveAttrValue(attr, device, fallback);

  if (typeof value === 'string' || typeof value === 'number') {
    return toCssUnit(value);
  }

  if (value && typeof value === 'object') {
    const nestedValue = getDeepValueByKeys(value, [
      'value',
      'size',
      'fontSize',
      'font-size',
      'font_size',
      'width',
    ]);

    if (typeof nestedValue === 'string' || typeof nestedValue === 'number') {
      return toCssUnit(nestedValue);
    }

    const amount = getDeepValueByKeys(value, [
      'amount',
      'number',
      'val',
    ]);

    const unit = getDeepValueByKeys(value, [
      'unit',
      'cssUnit',
      'css_unit',
    ]);

    if (amount !== undefined && amount !== null && amount !== '') {
      return `${amount}${unit || 'px'}`;
    }
  }

  return fallback;
};

export const getResponsiveDeepValue = (value, device = 'desktop', fallback = undefined) => {
  if (!value || typeof value !== 'object') {
    return value ?? fallback;
  }

  const candidates = [
    value?.[device]?.value,
    value?.[device],
    value?.font?.[device]?.value,
    value?.font?.[device],
    value?.innerContent?.[device]?.value,
    value?.innerContent?.[device],
    value?.desktop?.value,
    value?.desktop,
    value?.font?.desktop?.value,
    value?.font?.desktop,
    value?.innerContent?.desktop?.value,
    value?.innerContent?.desktop,
    value?.value,
  ];

  for (const candidate of candidates) {
    if (candidate !== undefined && candidate !== null && candidate !== '') {
      return candidate;
    }
  }

  return fallback;
};

export const getFontDecorationStyle = (attr = {}, device = 'desktop') => {
  const font = getResponsiveDeepValue(attr?.decoration?.font, device, {});

  const fontFamily = getDeepValueByKeys(font, [
    'fontFamily',
    'font-family',
    'font_family',
    'family',
  ]);

  const fontSize = getDeepValueByKeys(font, [
    'fontSize',
    'font-size',
    'font_size',
    'size',
  ]);

  const fontWeight = getDeepValueByKeys(font, [
    'fontWeight',
    'font-weight',
    'font_weight',
    'weight',
  ]);

  const lineHeight = getDeepValueByKeys(font, [
    'lineHeight',
    'line-height',
    'line_height',
  ]);

  const letterSpacing = getDeepValueByKeys(font, [
    'letterSpacing',
    'letter-spacing',
    'letter_spacing',
  ]);

  const color = getDeepValueByKeys(font, [
    'color',
    'textColor',
    'text-color',
    'text_color',
  ]);

  return {
    ...(fontFamily ? { fontFamily } : {}),
    ...(fontSize ? { fontSize: toCssUnit(fontSize) } : {}),
    ...(fontWeight ? { fontWeight } : {}),
    ...(lineHeight ? { lineHeight: toCssUnit(lineHeight) } : {}),
    ...(letterSpacing ? { letterSpacing: toCssUnit(letterSpacing) } : {}),
    ...(color ? { color } : {}),
  };
};


export const processDiviIconAttr = (attr, device = 'desktop', fallback = '') => {
  const processFontIcon = window?.divi?.iconLibrary?.processFontIcon;
  const responsiveValue = getResponsiveAttrValue(attr, device, fallback);

  const candidates = [];
  if (responsiveValue !== undefined && responsiveValue !== null && responsiveValue !== '') {
    candidates.push(responsiveValue);
  }
  if (attr?.[device]?.value !== undefined) {
    candidates.push(attr?.[device]?.value);
  }
  if (attr?.desktop?.value !== undefined) {
    candidates.push(attr?.desktop?.value);
  }
  candidates.push(attr);

  for (const candidate of candidates) {
    if (candidate === undefined || candidate === null || candidate === '') {
      continue;
    }

    if (typeof processFontIcon === 'function') {
      const processed = processFontIcon(candidate);
      if (processed) {
        return processed;
      }
    }

    if (typeof candidate === 'string') {
      return candidate;
    }

    if (typeof candidate === 'object') {
      const maybeIcon = candidate.icon || candidate.value || candidate.unicode || candidate.selectedIcon;
      if (typeof maybeIcon === 'string') {
        if (typeof processFontIcon === 'function') {
          const processed = processFontIcon(maybeIcon);
          if (processed) {
            return processed;
          }
        }
        return maybeIcon;
      }
    }
  }

  return fallback;
};

export const getModuleStyle = (
  attrs = {},
  device = 'desktop'
) => {
  const decoration = attrs?.module?.decoration || {};

  const layout = getResponsiveAttrValue(
    decoration.layout,
    device,
    {}
  );

  const background = getResponsiveAttrValue(
    decoration.background,
    device,
    {}
  );

  const sizing = getResponsiveAttrValue(
    decoration.sizing,
    device,
    {}
  );

  const spacing = getResponsiveAttrValue(
    decoration.spacing,
    device,
    {}
  );

  const border = getResponsiveAttrValue(
    decoration.border,
    device,
    {}
  );

  const position = getResponsiveAttrValue(
    decoration.position,
    device,
    {}
  );

  const zIndex = getResponsiveAttrValue(
    decoration.zIndex,
    device,
    {}
  );

  return {
    ...(layout?.display ? { display: layout.display } : {}),
    ...(background?.color ? { backgroundColor: background.color } : {}),
    ...(sizing?.width ? { width: toCssUnit(sizing.width) } : {}),
    ...(sizing?.maxWidth ? { maxWidth: toCssUnit(sizing.maxWidth) } : {}),
    ...(sizing?.minHeight ? { minHeight: toCssUnit(sizing.minHeight) } : {}),
    ...(sizing?.height ? { height: toCssUnit(sizing.height) } : {}),
    ...(spacing?.margin?.top ? { marginTop: toCssUnit(spacing.margin.top) } : {}),
    ...(spacing?.margin?.right ? { marginRight: toCssUnit(spacing.margin.right) } : {}),
    ...(spacing?.margin?.bottom ? { marginBottom: toCssUnit(spacing.margin.bottom) } : {}),
    ...(spacing?.margin?.left ? { marginLeft: toCssUnit(spacing.margin.left) } : {}),
    ...(spacing?.padding?.top ? { paddingTop: toCssUnit(spacing.padding.top) } : {}),
    ...(spacing?.padding?.right ? { paddingRight: toCssUnit(spacing.padding.right) } : {}),
    ...(spacing?.padding?.bottom ? { paddingBottom: toCssUnit(spacing.padding.bottom) } : {}),
    ...(spacing?.padding?.left ? { paddingLeft: toCssUnit(spacing.padding.left) } : {}),
    ...(border?.radius ? { borderRadius: toCssUnit(border.radius) } : {}),
    ...(border?.width ? { borderWidth: toCssUnit(border.width) } : {}),
    ...(border?.style ? { borderStyle: border.style } : {}),
    ...(border?.color ? { borderColor: border.color } : {}),
    ...(position?.position ? { position: position.position } : {}),
    ...(position?.top ? { top: toCssUnit(position.top) } : {}),
    ...(position?.right ? { right: toCssUnit(position.right) } : {}),
    ...(position?.bottom ? { bottom: toCssUnit(position.bottom) } : {}),
    ...(position?.left ? { left: toCssUnit(position.left) } : {}),
    ...(zIndex?.zIndex ? { zIndex: zIndex.zIndex } : {}),
  };
};

export default function ModulePreviewWrapper({
  attrs,
  device = 'desktop',
  className = '',
  children,
}) {
  const style = getModuleStyle(
    attrs,
    device
  );

  return (
    <div
      className={className}
      style={style}
    >
      <div className="et_pb_module_inner">
        {children}
      </div>
    </div>
  );
}
