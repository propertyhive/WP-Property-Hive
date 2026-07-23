# Implementation Plan — Three New Detail Templates for the Template-Set System

**Goal:** Implement the three data-mapped prototypes as production template-set detail templates:

| Prototype | Catalog slug | Slug status |
|---|---|---|
| `01-portal-split-v2.html` | `conversion-first-sales-detail` | Anticipated seed — dormant copy branches, demo content & CSS already exist |
| `04-immersive-cinema-v2.html` | `immersive-cinema-detail` | New slug |
| `17-private-office-v2.html` | `premium-editorial-detail` | Anticipated seed — dormant branches & CSS already exist |

`standard-sales-detail` remains the default. All work happens on branch `feature/property-template-system-spec`. **No commits without explicit instruction.**

---

## Architecture facts the implementation must respect

(Verified against source; line numbers as of HEAD `83e2fc33`.)

1. **Registration is the switch.** `PH_Template_Set_Catalog::get_detail_templates()` (`includes/template-set/class-ph-template-set-catalog.php:17-21`) is the single source of truth. Adding a slug there makes it automatically: selectable in admin settings (`class-ph-settings-frontend.php:1436-1442`), valid for `?ph_detail_template=`, listed in the visual editor (`class-ph-template-set-editor-controller.php:46`), and previewable.
2. **No top-level template override.** Detail pages render through hooks wired in `class-ph-template-set.php:86-95` onto core actions (`propertyhive_single_property_summary`, `propertyhive_after_single_property_summary`, `propertyhive_property_actions_end`). Render callbacks live in `includes/template-set/traits/trait-ph-template-set-detail.php` and (gallery/facts) `trait-ph-template-set-preview.php`.
3. **Partials fall back automatically.** `PH_Template_Set_Template_Loader` (`class-ph-template-set-template-loader.php:122-202`) resolves `template-set/detail/{slug}/{part}.php` → shared `{part}.php` → default slug's partials. New templates only need partial files where they diverge from `standard-sales-detail`.
4. **Variables flow via `$args`** — `Template_Loader::render()` `extract()`s them (`:45`); the `propertyhive_template_set_template_args` filter (`:38`) wraps every partial.
5. **One shared stylesheet.** All per-template CSS goes into `assets/css/template-set.css`, scoped under body class `.ph-detail-template-{slug}` (emitted in `trait-ph-template-set-search.php:48,98`). Brand/accent come from CSS custom properties `--ph-template-brand` / `--ph-template-accent` printed by `class-ph-template-set-assets.php:119-137`.
6. **Gallery variants already exist.** `render_detail_gallery()` (`trait-ph-template-set-preview.php:311-313`) emits `ph-gallery-variant-{layout}`; options include `mosaic` and `cinema` (`class-ph-template-set-options.php:29-35`). Portal Split uses `mosaic`, Immersive Cinema uses `cinema` — no new gallery engine needed, only per-slug default + CSS.
7. **Some renders are preview-only.** `render_detail_modules` / `render_similar_properties` are gated by `is_demo_preview()` (`trait-ph-template-set-detail.php:16,59`); kicker/highlights/context-panel/contact-panel/mobile-cta/trust-note run live. `render_detail_facts_strip` is currently hard-gated to `standard-sales-detail` (`trait-ph-template-set-preview.php:75-77,400`) — this gate must be widened (see Phase 1).
8. **Dev workflow:** no composer/npm/phpcs/phpunit. Verification = `php -l` on each touched file + manual preview URLs + headless-browser screenshots. Conventions: `ABSPATH` guard, escape at output (`esc_html`, `esc_attr`, `esc_url`, `wp_kses_post` for description/price HTML), docblock header on every partial listing "Available variables" and the theme-override path, `sanitize_title`/`sanitize_key` for slugs/parts.

## Data ground rules (from the field audit — RESEARCH.md §8)

- **Never render:** residential floor area (no core input), EPC rating/score (documents only), en-suite counts.
- **Residential descriptions are room-based:** `_rooms` + `_room_name_{i}` / `_room_dimensions_{i}` / `_room_description_{i}`; fall back to `get_formatted_description()` when `_rooms` is 0.
- **Available but not yet surfaced:** `_council_tax_band` (currently only in the rental panel, `trait-ph-template-set-detail.php:1234`), `_reference_number`, leasehold cluster (`_leasehold_years_remaining`, `_ground_rent`, `_ground_rent_review_years`, `_service_charge`, `_service_charge_review_years`), `get_material_information()`, per-tour labels `_virtual_tour_label_{i}`.
- **Add-on surfaces stay hooks, not implementations:** shortlist/send-to-friend arrive via the legacy actions box (`propertyhive_single_property_actions`, `ph-template-functions.php:1197`) which still renders on live pages — do not duplicate. Calculator/what3words/LocRating slots are **out of scope** for this build (portal prototype's "Purchase costs" block ships only if trivially gated on `shortcode_exists()`; otherwise omit — decide in review).

---

## Phase 1 — Shared data layer (foundation, template-agnostic)

All in `trait-ph-template-set-detail.php` (mirroring `get_detail_facts_strip_items()` / `get_rental_panel_items()` style — return arrays of `label`/`value` items, empty array when no data):

1. `get_detail_room_items( $property )` → `[ [ 'name', 'dimensions', 'description' ], … ]` from `_rooms` meta; returns `[]` when count is 0 or department is commercial.
2. `get_detail_material_information( $property )` → labelled rows from `PH_Property::get_material_information()` (heating, electricity, water, sewerage, broadband, flood risk); skip unset rows.
3. `get_detail_key_facts( $property )` → extend/supplement `get_detail_meta_items()` (`:913-965`) with council tax band (sales too, not just rental panel), reference number, and the leasehold cluster (rows only when tenure is leasehold/share-of-freehold).
4. `get_property_document_labels()` (`:1296-1328`): use `_virtual_tour_label_{i}` when set instead of the generic "Virtual tour"; one entry per tour.
5. Widen the `standard-sales-detail` hard-gates at `trait-ph-template-set-preview.php:75-77,400` to a capability check like `detail_template_uses_facts_strip( $template )` so new slugs can opt in.
6. Pass the new arrays into partials via the existing `$args` pattern; document them in each partial's "Available variables" docblock.

**Escaping:** room descriptions and material info values through `esc_html()` (dimensions too); nothing here needs `wp_kses_post` except the fallback formatted description which already handles it.

## Phase 2 — Registration & plumbing

1. Add the three slugs to `get_detail_templates()` (`class-ph-template-set-catalog.php:17-21`) with labels: "Portal Split", "Immersive Cinema", "Private Office" (i18n via `__( …, 'propertyhive' )`).
2. `get_template_catalog()` grouping: keep `detail` type; group label "Detail page templates" (existing behaviour).
3. Sample-department mapping (`class-ph-template-set-request-context.php:541-543`): all three prefer `residential-sales`; no change needed unless the default misbehaves.
4. Verify: settings dropdown, visual editor select, admin-bar switcher, and `?ph_detail_template={slug}` all resolve; unregistered-slug fallback still lands on standard.
5. Audit the **existing dormant branches** for `conversion-first-sales-detail` and `premium-editorial-detail` in `trait-ph-template-set-detail.php` (kicker `:599-620`, highlights `:629-712`, context panel `:721-801`, hint `:809-818`, CTA `:1255-1269`), demo content (`trait-ph-template-set-preview.php:452-575`) and CSS (`template-set.css` ~`:2341-5088`): keep what matches the v2 prototypes, rewrite what doesn't. Do not assume the dormant code is correct — the prototypes are the spec.

## Phase 3 — Portal Split (`conversion-first-sales-detail`)

Spec: `docs/template-prototypes/01-portal-split-v2.html` (annotated with PH sources per module).

1. **Gallery:** default gallery variant `mosaic` for this slug (per-slug default; user can still override in editor). CSS for the 1.4fr/1fr mosaic under `.ph-detail-template-conversion-first-sales-detail`.
2. **Facts bar:** per-slug facts-strip items adding Tenure + Council tax (Phase 1 #3); leasehold rows conditional.
3. **Partials** under `templates/template-set/detail/conversion-first-sales-detail/` only where diverging: `modules.php` (order: features → overview → room-based full details → documents → material information → location), `contact-panel.php` (sticky enquiry card: price block, Request viewing primary, Email/Call secondary, negotiator row, trust note text). Everything else falls back.
4. **CTA labels/kicker/highlights:** align dormant branches with prototype copy ("Request viewing" primary; kicker "For sale · {tenure}").
5. **CSS:** two-column layout (`minmax(0,1fr) 340px`), sticky right rail (`position:sticky; top`), facts bar, room-list typography, responsive collapse at 900px with card ordered first — mirror prototype breakpoints.

## Phase 4 — Immersive Cinema (`immersive-cinema-detail`)

Spec: `docs/template-prototypes/04-immersive-cinema-v2.html`.

1. **New slug everywhere:** kicker/highlights/context/CTA branches + demo listing content (no dormant code exists).
2. **Gallery:** default variant `cinema`; full-viewport hero + filmstrip via CSS/JS on the existing gallery module (check `assets/js/frontend/template-set/gallery.js` variant handling; extend only if the cinema variant lacks filmstrip behaviour).
3. **Floating glass card = contact-panel partial** for this slug (positioned over the hero via CSS; static-flow fallback under 800px exactly as prototype).
4. **Dark theme:** scoped custom-property overrides under `.ph-detail-template-immersive-cinema-detail` (dark surfaces, `--ph-template-accent` gold default stays user-overridable). Ensure text contrast for badges/muted text per prototype values.
5. **Modules order:** features → overview → room-by-room → details table (price/tenure/council tax/parking/reference) → material information → documents → location → agent panel → similar.

## Phase 5 — Private Office (`premium-editorial-detail`)

Spec: `docs/template-prototypes/17-private-office-v2.html`.

1. **Masthead:** centred serif kicker/title/price — kicker branch + `highlights` branch reworked to the "smallcaps line + serif h1 + price line + excerpt brief" structure.
2. **Room-by-room as typeset section** (Phase 1 #1 data), particulars `<dl>` with dotted leaders (key facts incl. reference, outside space, council tax; leasehold conditional), material information as quiet appendix grid.
3. **Contact = "letter" panel:** dark closing band with negotiator photo/initials, sig, role, two outline CTAs ("Arrange a private viewing", "Request the brochure"); map to contact-panel partial for this slug. Default contact card style `concierge` for this slug is acceptable if it reduces CSS, but the prototype design wins.
4. **Serif stack:** reuse the prototype's font tokens via scoped CSS (system serif stack — no webfont additions).
5. **Documents appendix:** outlined pill links incl. labelled tours; map block with "precise location shared on enquiry" label variant (template copy, not a data change).

## Phase 6 — Verification (every phase gates on this)

1. `php -l` on every touched PHP file.
2. Preview matrix via `?ph_detail_template={slug}` on the sample property (91 Waterman Court): 3 templates × {desktop 1280, mobile 375} — headless-Chrome screenshots compared against the three v2 prototypes.
3. Empty-data resilience: a property with no rooms (fallback description), no tours/floorplans/EPC (labels absent), no council tax/reference (rows absent), leasehold sample (cluster rows present). Create/adjust a draft test property if needed.
4. Regression: `standard-sales-detail` renders byte-identical CSS classes/output paths (no behavioural change when the new slugs are not selected); settings save/validate round-trip (`sanitize_template_set_settings`) accepts the new slugs.
5. Editor: visual editor preview navigation works for all three (guard from commit `10f64d7d` respected).

---

# Part 2 — Template control manifests + pixel-fidelity pass

Added after the first implementation landed. Two goals: (a) each template gets its own conditional editor controls in a way that scales to future templates; (b) close the remaining visual gap to the v2 prototypes exactly.

## Current-state problems (verified in `class-ph-template-set-editor-controller.php`)

1. `render_template_editor()` hardcodes one identical control list for every detail template (lines 80–107); nothing is conditional on the selected template.
2. Unshown settings are preserved via hidden inputs and every AJAX save writes ALL keys globally — so a value saved while template A was active silently constrains template B (the root of the stale-gallery-layout bug).
3. `get_editor_sidebar_layout()` (lines 273–331) is a second hardcoded copy of the same structure for the JS sidebar.
4. Round-4's identity fix (forced gallery variants) leaves the gallery control visible but ineffective for portal/cinema — a symptom of missing per-template control metadata.

## A. Template control manifests

**Catalog entries become manifests.** `PH_Template_Set_Catalog::get_detail_templates()` keeps returning `slug => label` for back-compat, and a new `get_detail_template_manifest( $slug )` (filterable, e.g. `propertyhive_template_set_detail_template_manifest`) returns:

```php
array(
  'label'    => 'Immersive Cinema',
  'supports' => array( /* shared control keys this template shows in the editor */
    'template_set_show_floorplans', 'template_set_show_virtual_tours',
    'template_set_show_mobile_cta', 'template_set_button_style',
    'template_set_show_recommended', 'template_set_recommended_count', ...
  ),
  'locked'   => array( /* identity settings: forced value; control rendered disabled with a "set by template" note */
    'template_set_gallery_layout' => 'cinema',
  ),
  'defaults' => array( /* per-template defaults when the agency hasn't overridden */
    'template_set_button_style' => 'filled',
  ),
  'controls' => array( /* template-specific controls, declared not hardcoded */
    'template_set_cinema_card_position' => array(
      'type' => 'select', 'label' => 'Hero card position',
      'options' => array( 'right' => 'Right', 'left' => 'Left' ),
      'default' => 'right', 'group' => 'media',
    ),
  ),
)
```

- `standard-sales-detail` gets a manifest equal to today's control set → zero behaviour change.
- Sidebar grouping (`get_editor_sidebar_layout`) is derived from manifests (`group` key), not hand-maintained.
- Editor renders: template select → the manifest's shared controls → its `controls` → locked entries as disabled rows with an explanatory note. Unsupported controls are neither rendered nor submitted.

**Initial per-template controls** (from the prototypes' natural variation points):
- Portal Split: facts-strip fields preset (compact/extended), purchase-costs module toggle (when calculator add-ons active), trust-note text.
- Immersive Cinema: hero height (standard/tall), hero card position (right/left), filmstrip on/off.
- Private Office: masthead brief on/off (excerpt), particulars density, letter sign-off role line on/off, map discretion label on/off.
(Ship small — 2–3 per template; the architecture is the deliverable, controls can grow.)

## B. Per-template setting overrides

New storage inside the existing option: `propertyhive_template_assistant['template_overrides'][ $slug ][ $key ] = $value`.

Resolution helper used by ALL template-scoped getters (`get_gallery_layout`, button style, contact card style, toggles, new controls):

```
locked (manifest) → template_overrides[slug][key] → manifest defaults[key] → global settings[key] → global default
```

- Editor saves template-scoped keys into `template_overrides[ current detail template ]` only for controls that were actually rendered; global keys (enabled, editor mode, brand colours, search settings) save globally as today.
- `sanitize_template_set_settings()` validates override keys against the manifest (unknown keys dropped, values validated against each control's options).
- Round-4's hard-forcing in `get_gallery_layout()` migrates into `locked` manifest entries — same behaviour, now declared data instead of special-cased code, and the editor can *show* why the control is locked.
- Back-compat: existing sites have no `template_overrides` → resolution falls through to globals; nothing changes until someone edits a specific template.

## C. Pixel-fidelity pass (method, not vibes)

The prototypes are static local HTML — both they and the live templates can be measured in the same browser. Per template, per breakpoint (1280 / 768 / 375):

1. Load prototype and live page side by side; for each section (masthead, gallery, facts, modules, contact, similar, mobile CTA) measure: container max-width, vertical rhythm (section padding/margins), type sizes/weights/letter-spacing, radii, gaps, colour values.
2. Produce a written **delta table** per template (`element → prototype value → live value → fix`).
3. Apply deltas in slug-scoped CSS (Codex round per template), re-measure, iterate until the delta table is empty or intentional.
4. Anything that *should* be a choice rather than a fixed value becomes a manifest control instead of hardcoded CSS.

## Sequencing

1. **B first** (storage + resolution helper) — it's the foundation and de-risks every later save.
2. **A** (manifests + editor rendering + sidebar layout from manifests + locked-control UI).
3. **C** (fidelity deltas per template; portal → cinema → editorial), using A/B where a delta is a preference.
4. Regression gates throughout: standard-sales-detail unchanged; existing saved settings keep working; editor save round-trip validated.

## Sequencing & risk notes

- Phases 1–2 first (foundation + registration), then 3/4/5 are independent and reviewable per-template; 6 runs per phase.
- Biggest risk: the dormant CSS (~2,700 lines for placeholder slugs) conflicting with prototype-faithful styles — prefer replacing dormant blocks over appending, and keep all new rules inside the slug scope.
- Second risk: live vs preview render split (`render_detail_modules` preview-only) — the module content for live pages flows through core single-property hooks; confirm each new module renders on a real live page, not just demo preview, and decide (in implementation review) whether to lift modules rendering to live for the new slugs the way kicker/highlights already are.
- Mobile CTA, badges, branch line, floorplan/tour toggles: reuse existing settings, no new options in this build.
