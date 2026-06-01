import { registerPropertyHiveModule } from '../../shared/register-propertyhive-module';
import ModulePreviewWrapper, {
  getResponsiveAttrValue,
  getFontDecorationStyle,
  getTextStyle,
  getCssSizeValue,
  usePreviewDevice,
  processDiviIconAttr,
} from '../ModulePreviewWrapper';

const imagePlaceholderStyle = {
  background: 'linear-gradient(135deg, #e8e8e8 0%, #f7f7f7 100%)',
  border: '1px solid #d5d5d5',
  color: '#777',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  minHeight: '180px',
  textAlign: 'center',
};

const ratioPadding = (ratio, fallback = '66.6667%') => {
  if (!ratio || ratio.indexOf(':') === -1) {
    return fallback;
  }

  const [w, h] = ratio.split(':').map((part) => parseFloat(part));
  if (!w || !h) {
    return fallback;
  }

  return `${(h / w) * 100}%`;
};

const PreviewPlaceholder = ({ kind, attrs, device, sampleValue }) => {
  if (kind === 'image') {
    const ratio = getResponsiveAttrValue(attrs?.outputRatio, device, '');
    const label = `Property Image #${getResponsiveAttrValue(attrs?.imageNumber, device, '1')}`;

    if (ratio) {
      return (
        <div style={{ ...imagePlaceholderStyle, minHeight: 0, position: 'relative' }}>
          <span style={{ display: 'block', paddingBottom: ratioPadding(ratio), width: '100%' }} />
          <span style={{ position: 'absolute', inset: 0, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>{label}</span>
        </div>
      );
    }

    return <div style={imagePlaceholderStyle}>{label}</div>;
  }

  if (kind === 'gallery') {
    const layout = getResponsiveAttrValue(attrs?.galleryLayout, device, 'grid');
    const count = layout === 'one_large_four_small' ? 5 : 6;
    const isFeatureLayout = layout === 'one_large_four_small';

    return (
      <div
        style={{
          display: 'grid',
          gridTemplateColumns: isFeatureLayout ? 'repeat(4, 1fr)' : 'repeat(3, 1fr)',
          gap: '8px',
        }}
      >
        {Array.from({ length: count }).map((_, index) => (
          <div
            key={index}
            style={{
              ...imagePlaceholderStyle,
              minHeight: index === 0 && isFeatureLayout ? '260px' : '120px',
              gridColumn: index === 0 && isFeatureLayout ? 'span 2' : undefined,
              gridRow: index === 0 && isFeatureLayout ? 'span 2' : undefined,
              position: 'relative',
              overflow: 'hidden',
            }}
          >
            Image {index + 1}
            {index === count - 1 && (
              <span style={{ position: 'absolute', inset: 0, display: 'flex', alignItems: 'center', justifyContent: 'center', background: 'rgba(0,0,0,.55)', color: '#fff', fontWeight: 600, padding: '10px', textAlign: 'center' }}>
                See all 12 images
              </span>
            )}
          </div>
        ))}
      </div>
    );
  }

  if (kind === 'documentImage') {
    return <div style={{ ...imagePlaceholderStyle, minHeight: '240px', width: '100%' }}>{sampleValue}</div>;
  }

  if (kind === 'images') {
    const hideThumbnails = getResponsiveAttrValue(attrs?.hideThumbnails, device, 'no') === 'yes';
    const rawNumImages = parseInt(getResponsiveAttrValue(attrs?.numImages, device, ''), 10);
    const thumbnailCount = Number.isFinite(rawNumImages) && rawNumImages > 0 ? Math.min(rawNumImages, 8) : 4;

    return (
      <div style={{ border: '1px solid #ddd', padding: '12px' }}>
        <div style={{ ...imagePlaceholderStyle, minHeight: '220px' }}>Property Images Slider</div>
        {!hideThumbnails && thumbnailCount > 0 && (
          <div style={{ display: 'flex', gap: '6px', marginTop: '8px', flexWrap: 'wrap' }}>
            {Array.from({ length: thumbnailCount }).map((_, index) => (
              <div key={index} style={{ ...imagePlaceholderStyle, minHeight: '45px', width: '70px' }}>{index + 1}</div>
            ))}
          </div>
        )}
      </div>
    );
  }

  if (kind === 'form') {
    return (
      <div className="property-search-form" style={{ border: '1px solid #ddd', padding: '16px' }}>
        <label style={{ display: 'block', marginBottom: '6px' }}>Search area</label>
        <input type="text" disabled value="" placeholder="Town, city or postcode" style={{ width: '100%', marginBottom: '10px' }} />
        <button type="button" disabled>Search</button>
      </div>
    );
  }

  if (kind === 'enquiry') {
    return (
      <div style={{ border: '1px solid #ddd', padding: '16px' }}>
        <label>Name</label><input type="text" disabled style={{ display: 'block', width: '100%', marginBottom: '8px' }} />
        <label>Email Address</label><input type="email" disabled style={{ display: 'block', width: '100%', marginBottom: '8px' }} />
        <label>Telephone Number</label><input type="tel" disabled style={{ display: 'block', width: '100%', marginBottom: '8px' }} />
        <label>Message</label><textarea disabled style={{ display: 'block', width: '100%', marginBottom: '8px' }} />
        <button type="button" disabled>Send Enquiry</button>
      </div>
    );
  }

  if (kind === 'map') {
    if ((sampleValue || '').toLowerCase().includes('virtual tour')) {
      return (
        <div style={{ ...imagePlaceholderStyle, minHeight: 0, position: 'relative' }}>
          <span style={{ display: 'block', paddingBottom: '56.25%', width: '100%' }} />
          <span style={{ position: 'absolute', inset: 0, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>{sampleValue || 'Embedded Virtual Tour Placeholder'}</span>
        </div>
      );
    }
    const height = getCssSizeValue(attrs?.height, device, '400px');
    return <div style={{ ...imagePlaceholderStyle, minHeight: height }}>{sampleValue || 'Map Placeholder'}</div>;
  }

  if (kind === 'photo') {
    return <div style={{ ...imagePlaceholderStyle, width: '160px', height: '160px', borderRadius: '50%' }}>Photo</div>;
  }

  if (kind === 'features') {
    const bulletType = getResponsiveAttrValue(attrs?.bulletType, device, 'disc');
    const bulletColor = getResponsiveAttrValue(attrs?.bulletColor, device, '');
    const columns = getResponsiveAttrValue(attrs?.columns, device, '1');
    const listStyleType = bulletType === 'square' ? 'square' : 'disc';
    const icon = processDiviIconAttr(attrs?.bulletIcon, device) || '✓';

    return (
      <ul style={{ columnCount: columns || 1, listStyleType: bulletType === 'icon' ? 'none' : listStyleType, paddingLeft: bulletType === 'icon' ? 0 : undefined }}>
        {['Feature one', 'Feature two', 'Feature three'].map((item) => (
          <li key={item} style={{ breakInside: 'avoid', color: bulletType === 'icon' ? undefined : (bulletColor || undefined), marginBottom: '6px' }}>
            {bulletType === 'icon' && <span className="et-pb-icon" style={{ marginRight: '8px', color: bulletColor || undefined, fontSize: '1em', lineHeight: 1 }}>{icon}</span>}
            {item}
          </li>
        ))}
      </ul>
    );
  }

  return sampleValue;
};

const createPropertyContentPreview = ({ moduleClassName, outputClassName, textAttrName, sampleValue, previewKind = 'text', titleLabel = '' }) => {
  const Preview = (props) => {
    const { attrs = {} } = props;
    const device = usePreviewDevice();

    const textAlign = getResponsiveAttrValue(attrs?.textAlign, device, 'left');
    const textColor = getResponsiveAttrValue(attrs?.textColor, device, '');
    const label = getResponsiveAttrValue(attrs?.label?.innerContent || attrs?.label, device, '');

    const textStyle = {
      ...getFontDecorationStyle(attrs?.[textAttrName], device),
      ...getTextStyle({ textAlign, color: textColor }),
      whiteSpace: 'pre-line',
    };

    const displayValue = label || sampleValue;
    const hideTitle = getResponsiveAttrValue(attrs?.hideTitle, device, 'no') === 'yes';

    return (
      <ModulePreviewWrapper attrs={attrs} device={device} className={moduleClassName}>
        <div className={outputClassName} style={textStyle}>
          {titleLabel && !hideTitle && <h4>{titleLabel}</h4>}
          <PreviewPlaceholder kind={previewKind} attrs={attrs} device={device} sampleValue={displayValue} />
        </div>
      </ModulePreviewWrapper>
    );
  };

  return Preview;
};

export const registerPropertyContentModule = (metadata, config) => {
  registerPropertyHiveModule(metadata, {
    preview: createPropertyContentPreview(config),
  });
};
