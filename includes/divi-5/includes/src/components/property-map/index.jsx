import metadata from './module.json';
import { registerPropertyHiveModule } from '../../shared/register-propertyhive-module';
import ModulePreviewWrapper, {
  getCssSizeValue,
  getResponsiveAttrValue,
  usePreviewDevice,
} from '../ModulePreviewWrapper';

const getZoomValue = (attrs, device) => {
  const value = getResponsiveAttrValue(attrs?.zoom, device, '14');

  const raw = (typeof value === 'string' || typeof value === 'number')
    ? value
    : (value?.size || value?.value || value?.amount || '14');

  const zoom = parseInt(String(raw).replace(/[^0-9]/g, ''), 10);

  return Number.isFinite(zoom) && zoom > 0 ? zoom : 14;
};

const Preview = (props) => {
  const { attrs = {} } = props;
  const device = usePreviewDevice();
  const height = getCssSizeValue(attrs?.height, device, '400px');
  const zoom = getZoomValue(attrs, device);
  const scrollwheel = getResponsiveAttrValue(attrs?.scrollwheel, device, 'true');

  return (
    <ModulePreviewWrapper
      attrs={attrs}
      device={device}
      className="propertyhive_divi5_property_map"
    >
      <div
        className="propertyhive-divi5-property-map-placeholder"
        style={{
          height,
          minHeight: '120px',
          background: '#e5e5e5',
          border: '1px solid #cfcfcf',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          textAlign: 'center',
          color: '#555',
          boxSizing: 'border-box',
        }}
      >
        <div>
          <div style={{ fontSize: '28px', lineHeight: '1', marginBottom: '8px' }}>⌖</div>
          <strong>Property Map</strong>
          <div style={{ fontSize: '12px', marginTop: '6px' }}>
            Height: {height} · Zoom: {zoom} · Scrollwheel: {String(scrollwheel)}
          </div>
        </div>
      </div>
    </ModulePreviewWrapper>
  );
};

registerPropertyHiveModule(metadata, {
  preview: Preview,
});
