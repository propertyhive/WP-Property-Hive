# Property Detail Template Research

**Goal:** Add a second (and later more) detail template to the Property Hive template set — premium feel, adaptive to all core essentials, proven patterns from property marketplaces and luxury agency sites.

**Sample property:** 91 Waterman Court, Wokingham · Offers over £1,340,000 · Bungalow · 3 bed / 3 bath / 2 rec · Freehold

---

## 1. What must a detail template do?

A property detail page is a **decision page**, not a brochure. Visitors answer three questions quickly:

1. **Is this worth my time?** (price, type, beds, location, photos)
2. **Can I picture living here?** (gallery, floorplan, virtual tour, description)
3. **How do I take the next step?** (request viewing, call, email, shortlist)

### Essentials (always present when data exists)

| Zone | Content | Source in PH |
|------|---------|--------------|
| Identity | Address, department kicker (For sale / To let), badges | Title, availability, featured |
| Value | Price + qualifier / POA / rent frequency | `get_formatted_price()` |
| Specs | Beds, baths, receptions, type, tenure, parking | Property meta / taxonomies |
| Media | Photos, floorplans, virtual tours, map | Gallery, floorplans, virtual tours, lat/lng |
| Story | Features, summary, full description / rooms | Features, summary, description |
| Proof | Brochure, EPC, documents | Brochure / EPC attachments |
| Agent | Negotiator, office, phone, email, branch | Office + negotiator fields |
| Action | Request viewing / enquiry, shortlist, call | Enquiry modal, shortlist addon |
| Continuity | Similar / recommended properties | Template-set recommended module |
| Mobile | Sticky enquiry bar | `template_set_show_mobile_cta` |

### Graceful absence

Templates must not leave empty shells. Hide or collapse modules when: no floorplans, no tours, no documents, no map coords, no negotiator photo, no similar properties, lettings-only fields on sales (and vice versa).

---

## 2. Proven patterns from shipped products

### Marketplace / portal (evaluation + conversion)

**[Zillow listing detail](https://mobbin.com/screens/e57b727b-7150-4776-a2d7-7842b028ed17)** and related Zillow screens:

- Wide multi-photo band or mosaic immediately on entry
- Price + beds/baths/sqft in a compact facts cluster near the top
- Utility actions (Save / Share) in a persistent chrome bar
- “For sale” status as a clear badge on media

**[Airbnb listing detail](https://mobbin.com/screens/841053a4-d1aa-405c-a6a7-fa4e639816c3)** and sticky booking patterns:

- Photo grid / mosaic as the primary entrance
- **Sticky right rail** for the primary action (book / enquire)
- Content sections scroll independently of the action card
- Map as a full-width module later in the page

**Adoption for PH:** mosaic or showcase gallery + sticky contact card is the strongest conversion layout for most UK agency sites that compete with Rightmove/Zoopla behaviour.

### Luxury / brand-led (desire + trust)

Savills / Knight Frank / high-end agency sites (and editorial patterns like large type + hero photography):

- Full-bleed photography first; type is quieter and more spaced
- Price often secondary to address and atmosphere
- Contact is present but less “salesy” — brochure / register interest
- Generous white space, serif or refined display type

**Adoption for PH:** strong second template for premium listings and brand-led agencies; still needs mobile CTA and facts, just de-emphasized.

### UX research consensus (2025–26 listing best practice)

Convergent guidance from property listing UX sources:

1. Treat the page like a **landing page**, not a CMS article
2. Large, responsive gallery above the fold; swipe on mobile
3. Key facts at a glance within seconds
4. **One primary CTA** (Request viewing) + secondary (Call / Email)
5. Floorplans + documents near the evaluation zone
6. Embedded map + neighbourhood context
7. Fast images, lazy-load secondary media

---

## 3. What Property Hive (and extensions) must accommodate

### Core plugin

- Departments: residential sales, lettings, commercial (price/rent variants, POA)
- Taxonomies: property type, parking, tenure, sale by, availability
- Media: gallery, floorplans, brochures, EPCs, virtual tours
- Content: features list, summary, room-based or freeform description
- Contact: office, negotiator, enquiry form / make enquiry
- Map: address + coordinates when present
- Branding: brand/accent colours from template-set settings

### Template-set settings already in product

- Gallery layouts: showcase, cinema, mosaic, editorial, strip
- Contact card styles: classic, signature, concierge, editorial
- Button styles: filled, outline, soft
- Toggles: branch, badges, mobile CTA, floorplans, virtual tours, recommended
- Recommended: count, layout (grid/feature/list), image size

### Extensions present in this environment

| Extension | Template implication |
|-----------|----------------------|
| Shortlist | Heart / save control on detail + mobile bar |
| Map search / location | Map module, location label |
| LocRating | Optional neighbourhood score block |
| QR code | Optional share / print area |
| Radial / autocomplete | Search context only (not detail body) |
| Elementor / builders | Template-set can own detail chrome without fighting theme |

### Seed profiles already referenced in code (not yet catalogued)

The detail trait already names direction labels — useful signals of product intent:

- `standard-sales-detail` (live)
- `conversion-first-sales-detail`
- `premium-editorial-detail`
- `lettings-detail`
- `new-homes-development-detail`

Prototypes below explore **layout directions** first; department-specific copy (lettings / new homes) can layer on the chosen structure.

---

## 4. Five divergent directions (prototypes)

| # | Name | Metaphor | Primary bet | Best for |
|---|------|----------|-------------|----------|
| 01 | **Portal Split** | Zillow + Airbnb | Mosaic + sticky enquiry rail | High-volume sales agents |
| 02 | **Luxury Editorial** | Savills / KF magazine | Full-bleed story, quiet CTA | Premium / brand-led firms |
| 03 | **Conversion Stack** | Landing page | Price + CTA before gallery | Lead-gen heavy teams |
| 04 | **Immersive Cinema** | Showcase / film | Full-width cinema gallery | Photo-strong listings |
| 05 | **Spec Atelier** | Spec sheet + atelier | Structured facts + docs grid | Compliance-heavy / detailed stock |

Each prototype uses the same Waterman Court sample data and the same essential modules so comparison is fair.

---

## 5. Selection criteria for the next build

When reviewing prototypes, score each on:

1. **Above-fold clarity** — price, facts, and next action in ~5 seconds  
2. **Gallery prominence** without burying the agent  
3. **Sticky / mobile path** to enquiry  
4. **Room for PH modules** (docs, floorplan, map, similar) without clutter  
5. **Premium feel** that still works for a £250k terrace and a £2m house  
6. **Implementability** on the existing template-set partials + CSS tokens  

**Working recommendation after research:** ship **Portal Split** as the second production template (widest proven pattern + maps cleanly to existing contact-card + gallery settings), then refine with optional **Luxury Editorial** density toggles for higher-end agencies rather than building five full PHP templates at once.

---

## 6. Batch B — ten further directions (06–15)

| # | Name | Primary bet | PH / product fit |
|---|------|-------------|------------------|
| 06 | Neighbourhood First | Map hero + area scores + POIs | Map search / LocRating hooks |
| 07 | Private Client | Appointment-only ultra luxury | Concierge contact card style |
| 08 | Soft Scandi | Lifestyle warmth, soft surfaces | Family / mid-market brand feel |
| 09 | Magazine Chapters | Long-scroll story + chapter nav | Premium editorial; slower CTA |
| 10 | Bento Modular | Each module is a tile | Best *system* model for optional modules |
| 11 | Photo Wall | Masonry gallery + sticky dock | Photo-strong listings; cinema cousin |
| 12 | Lettings Practical | Rent, deposit, availability, checklist | `lettings-detail` seed |
| 13 | New Homes Release | Register, plots, showhome | `new-homes-development-detail` seed |
| 14 | Minimal Swiss | Hard grid, huge type, red accent | Polarising brand-led option |
| 15 | Agent Spotlight | Negotiator-first trust | Boutique agencies; negotiator fields |

### Shortlist guidance (post–15)

Ship **patterns**, not fifteen PHP templates:

1. **Portal Split** — second default sales template  
2. **One premium** — Luxury Editorial *or* Private Client  
3. **Lettings Practical** — when department needs different jobs-to-be-done  
4. **Bento thinking** — implement modules as independent partials regardless of visual skin  

Optional later: New Homes Release (needs richer development data), Neighbourhood First (needs map/POI confidence).

---

## 7. Batch C — five orthogonal directions (16–20)

Each deliberately avoids the retained bets (mosaic + sticky rail, dark cinema hero, spec matrix) and sharpens — rather than clones — the nearest discarded Batch B cousin.

| # | Name | Primary bet | Sharper than Batch B because… |
|---|------|-------------|-------------------------------|
| 16 | **Area Dossier** | The *area* is above the fold: commute table, Ofsted-rated schools, categorised daily-life distances, "a Saturday from this door" | Not a decorative map hero (06) — it's structured decision data. Pattern: Zillow's Neighborhood tab (schools + walk scores as scannable rows) and personalised travel times; Booking.com's categorised distance tables |
| 17 | **Private Office** | Appointment-led luxury: centred serif masthead, full-bleed plates with captions, dotted-leader "Particulars", closing letter from the negotiator, one quiet CTA | Merges 02 + 07 into a single coherent voice. Mobbin research returned only portal layouts for "luxury" — confirmation this direction must borrow from brand sites (Savills/KF conventions), not marketplaces |
| 18 | **Tenant Ready** | Lettings dataset of the same home: rent pcm + weekly, itemised move-in costs to a total, availability badge, included/excluded matrix, 4-step application timeline, pre-enquiry checklist | 12 was a generic lettings skin; this is built around the tenant's actual anxiety — *can I afford it and will I pass referencing*. Pattern: Airbnb's line-item price breakdown to a bold total |
| 19 | **Bento Field System** | Every tile is labelled with the PH field it renders (`get_formatted_price()`, `features`, `epc`…); an empty field's tile drops out and the grid reflows — one tile is shown in its empty state as proof | 10 was a visual bento; this one is the *implementation model* — tiles map 1:1 to template-set partials, so graceful absence is the system, not an afterthought |
| 20 | **Walkthrough Chapters** | Room-by-room tour driven by PH room data (names + dimensions as chapter kickers), sticky chapter rail with IntersectionObserver active state, mid-scroll inline CTA, "end of the tour" close | 09 was editorial sections for their own sake; here chapters *are* the room-based description data, so the structure generates itself from the listing. Pattern: Perplexity Pages' sticky TOC with active highlight |

**Coverage of buyer jobs-to-be-done:** 16 answers "can I live my life from here?", 17 "is this special?", 18 "can I afford and secure it?", 19 "give me everything at a glance", 20 "walk me through it". Combined with 01/04/05, the eight directions now span conversion, drama, density, location, restraint, department, system and story — no two share a primary bet.

---

## 8. Data-mapped build candidates (01b / 04b / 17b)

Assessment of prototypes against actual PH data (source: `PH_Property` in `includes/class-ph-property.php`, the template-set engine in `includes/template-set/`, and the live Features-tab add-on catalogue). v1 files are preserved; corrections live in new `*-v2.html` files with an HTML comment on every module naming its PH source and empty-state rule.

### Ground rules established by the audit

| Claim in prototypes | Reality in core |
|---|---|
| Floor area (sq ft) for residential | **Does not exist** — `_floor_area_*` meta is schema-only; admin input + display are commercial-only (`ph-template-functions.php:680`) |
| EPC rating / score (e.g. "C (72)") | **Does not exist** — EPCs are document attachments/URLs only (`_epcs`, `_epc_urls`); EPC Graph Generator add-on is the only rating-graphic path |
| En-suite counts, embellished bed copy | **Does not exist** — `_bedrooms`/`_bathrooms`/`_reception_rooms` are plain numerics |
| Council tax band | **Exists** — `_council_tax_band`, residential sales & lettings |
| Freeform long description | Residential descriptions are **room-based**: `_rooms` count + `_room_name_{i}` / `_room_dimensions_{i}` / `_room_description_{i}` via `get_formatted_rooms()` |
| Material information | **Exists** — `get_material_information()`: heating, electricity, water, sewerage, broadband, flood risk |
| Save / Share buttons | Add-ons via `propertyhive_single_property_actions`: Shortlist, Send To Friend |
| Virtual tour buttons | Core stores per-tour labels: `_virtual_tour_label_{i}` |

### What each v2 changed

- **01b Portal Split v2** — "Full details" lorem → room-based rendering; facts bar gains tenure + council tax (leasehold cluster conditional); new **Purchase costs** block (free Stamp Duty + Mortgage Calculator add-on shortcodes — natural portal-conversion fit); material information; labelled tours; negotiator + office contact fields; Save/Share annotated to their add-ons.
- **04b Immersive Cinema v2** — new "Room by room" dark-styled section; new details table (price qualifier, tenure, council tax, parking, `_reference_number`); material information; labelled tour control; Save/Share split + annotated; agent panel on negotiator/office fields.
- **17b Private Office v2** — removed invented sq-ft and EPC score; prose chapters → typeset room-by-room from `_rooms`; particulars restricted to core fields (+ reference, outside space); material information as quiet appendix; add-on slots: Viewing Request (primary CTA), Printable Brochures, what3words, LocRating report line, Shortlist/Send-to-friend quiet actions.

### Template-set note for the build

The catalog (`class-ph-template-set-catalog.php`) currently registers only `standard-sales-detail`; `premium-editorial-detail` and `new-homes-development-detail` are referenced in preview code but unregistered. 01b maps closest to the existing partial set (kicker, highlights, contact-panel, modules, similar-properties, mobile-cta, trust-note); 04b and 17b would ship as new catalog entries composed from the same partials plus new gallery/section treatments.
