const FONT_STYLE_ID = 'propertyhive-divi5-fontawesome';
const FONT_URL_PARAMETER = 'propertyhive_divi5_fa_base';

const getFontBaseUrlFromUrl = (url) => {
  if (!url) {
    return '';
  }

  try {
    return new URL(url).searchParams.get(FONT_URL_PARAMETER) || '';
  } catch (error) {
    return '';
  }
};

const getFontBaseUrlFromDiviAsset = (url) => {
  if (!url) {
    return '';
  }

  try {
    const parsedUrl = new URL(url);
    const diviThemePath = parsedUrl.pathname.match(/^(.*\/themes\/Divi)(?:\/|$)/i);

    return diviThemePath
      ? `${parsedUrl.origin}${diviThemePath[1]}/core/admin/fonts/fontawesome`
      : '';
  } catch (error) {
    return '';
  }
};

const getAccessibleDocuments = () => {
  const documents = [];
  const seen = new Set();

  const addDocument = (candidateDocument) => {
    if (!candidateDocument || seen.has(candidateDocument)) {
      return;
    }

    seen.add(candidateDocument);
    documents.push(candidateDocument);

    Array.from(candidateDocument.querySelectorAll?.('iframe') || []).forEach((iframe) => {
      try {
        addDocument(iframe.contentDocument);
      } catch (error) {
        // Cross-origin frames cannot contain a Divi module preview from this site.
      }
    });
  };

  [window, window.parent, window.top].forEach((candidateWindow) => {
    try {
      addDocument(candidateWindow?.document);
    } catch (error) {
      // Ignore cross-origin parent windows.
    }
  });

  return documents;
};

const getFontBaseUrl = () => {
  const currentScriptUrl = getFontBaseUrlFromUrl(document?.currentScript?.src);

  if (currentScriptUrl) {
    return currentScriptUrl;
  }

  for (const candidateDocument of getAccessibleDocuments()) {
    for (const script of Array.from(candidateDocument.scripts || [])) {
      const candidateFontBaseUrl = getFontBaseUrlFromUrl(script.src);

      if (candidateFontBaseUrl) {
        return candidateFontBaseUrl;
      }
    }
  }

  for (const resource of performance?.getEntriesByType?.('resource') || []) {
    const candidateFontBaseUrl = getFontBaseUrlFromUrl(resource.name);

    if (candidateFontBaseUrl) {
      return candidateFontBaseUrl;
    }
  }

  // Some PackageBuildManager versions rebuild the script URL and can omit
  // third-party query parameters. In that case, derive the parent Divi theme
  // directory from any Divi stylesheet or script already present.
  for (const candidateDocument of getAccessibleDocuments()) {
    const assets = candidateDocument.querySelectorAll?.('[src], [href]') || [];

    for (const asset of Array.from(assets)) {
      const candidateFontBaseUrl = getFontBaseUrlFromDiviAsset(
        asset.src || asset.href
      );

      if (candidateFontBaseUrl) {
        return candidateFontBaseUrl;
      }
    }
  }

  for (const resource of performance?.getEntriesByType?.('resource') || []) {
    const candidateFontBaseUrl = getFontBaseUrlFromDiviAsset(resource.name);

    if (candidateFontBaseUrl) {
      return candidateFontBaseUrl;
    }
  }

  return '';
};

const fontBaseUrl = getFontBaseUrl().replace(/\/$/, '');

const fontFaceCss = fontBaseUrl
  ? `
    @font-face {
      font-family: "FontAwesome";
      font-style: normal;
      font-weight: 400;
      font-display: block;
      src: url("${fontBaseUrl}/fa-regular-400.woff2") format("woff2"),
           url("${fontBaseUrl}/fa-regular-400.woff") format("woff");
    }
    @font-face {
      font-family: "FontAwesome";
      font-style: normal;
      font-weight: 900;
      font-display: block;
      src: url("${fontBaseUrl}/fa-solid-900.woff2") format("woff2"),
           url("${fontBaseUrl}/fa-solid-900.woff") format("woff");
    }
    @font-face {
      font-family: "FontAwesome";
      font-style: normal;
      font-weight: 400;
      font-display: block;
      src: url("${fontBaseUrl}/fa-brands-400.woff2") format("woff2"),
           url("${fontBaseUrl}/fa-brands-400.woff") format("woff");
    }
  `
  : '';

const addFontFaces = (targetDocument) => {
  if (
    !fontFaceCss
    || !targetDocument?.head
    || targetDocument.getElementById(FONT_STYLE_ID)
  ) {
    return;
  }

  const style = targetDocument.createElement('style');
  style.id = FONT_STYLE_ID;
  style.textContent = fontFaceCss;
  targetDocument.head.appendChild(style);
};

const syncFontFaces = () => {
  getAccessibleDocuments().forEach(addFontFaces);
};

syncFontFaces();

// Theme Builder creates and replaces preview frames after extension scripts have
// loaded. Re-run the lightweight document scan whenever its DOM changes.
getAccessibleDocuments().forEach((candidateDocument) => {
  const root = candidateDocument.documentElement;

  if (!root) {
    return;
  }

  const observer = new MutationObserver(syncFontFaces);
  observer.observe(root, { childList: true, subtree: true });
  candidateDocument.addEventListener('load', syncFontFaces, true);
});
