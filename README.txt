=== PropertyHive ===
Contributors: PropertyHive,BIOSTALL
Tags: property, real estate, estate agents, estate agent, property management, propertyhive, property hive, properties, property plugin, estate agent plugin, rightmove, zoopla, blm, rtdf, jupix, vebra, alto, expertagent, dezrez, expert agent, expertagent, reapit, reaxml, letmc, acquaint
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=N68UHATHAEDLN&lc=GB&item_name=BIOSTALL&no_note=0&cn=Add%20special%20instructions%20to%20the%20seller%3a&no_shipping=1&currency_code=GBP&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted
Requires at least: 3.8
Tested up to: 5.9.1
Stable tag: 1.5.32
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Estate agency software for WP. Market properties on your website, manage contacts and applicants, or expand the features through add ons

== Description ==

Property Hive is the first plugin that aims to bring all of the features you'd normally find in estate agency software into WordPress.

From managing residential and commercial properties, owners and landlords, to tracking applicants, matching and emailing them suitable properties, we want to do it all.

= Why Property Hive? =

There are many estate agency software options available on the market, but here's why we think you'll love Property Hive:

**Only use the features your business needs**

By default Property Hive is a property and contact management tool, allowing you to manage your property stock, list properties on your website, record owners details, store applicants requirements and email suitable properties to them.

Through use of premium add-ons however, you can choose which features you want to bolt on. The add ons fit seamlessly into the existing plugin, and with each other.
 
Want to send your properties to property portals? There's an add-on for that.
Want to add Draw-A-Search functionality to your website? There's an add-on for that.

Our add-ons are priced individually from just £14.99 meaning you only pay for what you use.

You can [view all of our add-ons](https://wp-property-hive.com/add-ons/ "Property Hive Add-Ons") on our website.

**Works with any new or existing theme**

Property Hive isn't a theme. It's a platform allowing you to integrate property search into any website, new or existing. For more details on supported themes [click here](https://wp-property-hive.com/which-wordpress-themes-work-with-property-hive/) or get started with our very own free open-source theme [Honeycomb](https://wp-property-hive.com/property-hive-website-themes/honeycomb//).

**Integrates with the major software providers and property portals**

If you already use software such as Jupix, Vebra Alto, Dezrez, Reapit, ExpertAgent, LetMC and more then it's easy to get your properties imported on an automatic basis at regular intervals so they display on your website. Our [Property Import Add On](https://wp-property-hive.com/addons/property-import/) can have you and running in minutes.

Likewise, if you send properties to the portals such as Rightmove, Zoopla, OnTheMarket and Gumtree, we have various add ons to allow the exporting of properties to these sites.

**Millions of developers**

Property Hive isn't developed by one person, or a small team. It's built by the world. Property Hive is open-source meaning anyone can contribute, regardless of which country they're in, or how much existing knowledge of the plugin they have already. 

Want to build a new feature? Maybe fix a bug you've found? As long as you have an understanding of PHP and WordPress, or know someone that does, you can make the changes yourself. What's more, your hard work will be received gratefully by everyone else that uses the plugin.

== Installation ==

= Minimum Requirements =

* WordPress 3.8 or greater
* PHP version 5.2.4 or greater
* MySQL version 5.0 or greater

= Automatic installation =

Automatic installation is the easiest option as WordPress handles the file transfers itself and you don’t need to leave your web browser. To do an automatic install of Property Hive, log in to your WordPress dashboard, navigate to the Plugins menu and click Add New.

In the search field type "Property Hive" and click Search Plugins. Once you've found our plugin you can view details about it such as the point release, rating and description. Most importantly of course, you can install it by simply clicking "Install Now".

= Manual installation =

The manual installation method involves downloading the Property Hive plugin and uploading it to your webserver via your favourite FTP application. The WordPress codex contains [instructions on how to do this here](http://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation).

= Updating =

Automatic updates should work like a charm; as always though, ensure you backup your site just in case.

== Screenshots ==

1. Once activated, all your property related information is maintained within it's own section
2. Manage your properties just like you would normal posts and pages
3. Editing a property record - the 'Summary' tab contains address and owner/landlord information
4. Editing a property record - the 'Details' tab contains details about the property (bedrooms, price etc)
5. Editing a property record - The 'Marketing' tab allows you to specify whether the property is on the market, and it's availability
6. Editing a property record - The 'Descriptions' tab allows you to add features and property descriptions.
7. Editing a property record - The 'Media' tab is where photos, floorplans, brochures and EPC's are uploaded.
8. Manage your owners and landlords from the 'Contacts' page.
9. Editing a contact record - The 'Contact Details' tab is where you store contact information for the contact
10. Editing a contact record - The 'Relationships' tab contains all of the relationships that you have with this contact (i.e. in the event they're a landlord of two properties)
11. Record an applicants' requirements. Allows for one or more set of requirements in the case where someone is looking to buy and rent.
12. View a list of properties that match the applicant's requirements. You can them email these properties to the applicant.
13. Enquiries made on the site are available from within WordPress. Assign them to a negotiator and mark them as 'Closed' once complete.
14. The 'Settings' section gives you control over which departments are active, add and edit offices, and edit the custom fields (types, locations etc) that appear within your install

== Changelog ==

= 1.5.32 - 2022-03-10 =
* Added council tax band to properties and appraisals to comply with upcoming rule changes
* Added 'All' filter to enquiries grid and ensure main enquiry list defaults to show only open enquiries
* Added 'link' button to TinyMCE toolbar when full WYSIWYG editor enabled for full descriptions
* Added 'Overdue' filter to management key dates lists
* Renamed 'Property Rooms' to 'Property Descriptions' for clarity on where full descriptions should be entered

= 1.5.31 - 2022-02-28 =
* Added new [similar_properties] attribute 'matching_address_field' to specify only properties in same location are returned. Possible values include: address_two, address_three, address_four and location
* Added new filter 'propertyhive_auto_email_match_cron_recurrence' to change auto match recurrence. Possible options are hourly, twicedaily or daily
* Added new filter 'propertyhive_auto_match_maximum_results' to allow limiting number of auto-match results
* Added new filter 'propertyhive_auto_match_from_email_address' to change auto match from email address. By default it will use the email address of the office that has the most properties contained within the mailout
* Changed appraisal confirmation emails to use 'From' address specified in email settings area, instead of admin email address
* Corrected 'columns' attribute not impacting template CSS classes in [properties] shortcode
* Corrected auto-match tooltip so it doesn't sound like they're sent instantly
* Corrected office name not showing in main enquiries list
* Corrected recent 'Yesterday' date filter addition
* Corrected viewing confirmation emails breaking when owner or applicant has a comma-delimited email address entered
* Declared compatibility for WordPress 5.9.1

= 1.5.30 - 2022-01-23 =
* Added owner confirmation emails to appraisals
* Ensure og:image tag is included when using Yoast and images are being stored as URLs
* In the auto match cron, only get applicants with Send Matching Properties ticked in initial query
* Added 'Yesterday' as option to date range filter in backend on viewings, offers etc
* Added filter 'propertyhive_query_search_form_currency' to override currency being used in search queries
* PHP8 compatility fix regarding obtaining featured property image
* Ensured 'Property Hive-Only Mode' checkbox description is ran through __()

= 1.5.29 - 2021-12-21 =
* Added new Elementor Gallery widget with two layouts to choose from
* Ensure Property Hive dashboard widgets are shown for non-admin users, except News will still only show for admins
* Add ability to withdraw an existing offer
* Added filter 'propertyhive_new_currency_exchange_rates' allowing someone to use their own currency exchange rate API

= 1.5.28 - 2021-12-16 =
* Swapped logic re recent update to the description editors whereby WYSIWYG functionality will need to be enabled using the filter 'propertyhive_enable_description_editor' instead of being active by default. Done due to the fact it was messing up existing descriptions that contained line breaks

= 1.5.27 - 2021-12-15 =
* Added support for additional HTML tags in room/full descriptions
* Room and descriptions to now use TinyMCE WYSIWYG. Use filter 'propertyhive_disable_description_editor' to turn off and revert to standard textareas
* Added support for new search form filter 'Date Added' allowing users to filter by properties added in the last X days. Can be easily added to search forms using the Template Assistant add on
* Defaulted schema for properties to RealEstateListing if using Yoast
* Added a new 'No Show' viewing status to record the fact an applicant didn't turn up

= 1.5.26 - 2021-12-08 =
* Added support for property type and location dropdowns in search form hiding empty terms if enabled in recent Template Assistant add on update
* Added filters (e.g. 'propertyhive_viewing_applicant_contact_details') to customise applicant contact details output in grids
* Added filter 'propertyhive_auto_email_match_cron_timestamp' to change auto match cron time. Defaults to 2am
* Added filters (e.g. 'propertyhive_contact_viewings_row_classes') to amend classes output on rows of grids on property and contact records
* Improved sanitisation of data output in grids on property and contact records
* Run taxonomies output in search form dropdowns through __() so they can be translated accordingly
* Added price_actual to list of fields returned in REST API to aid use by international agents
* Residential details meta box only saved when relevant department ticked to ensure compatibility with Rooms add on and upcoming bedrooms fix

= 1.5.25 - 2021-11-23 =
* Added a new column to list of enquiries in backend displaying the properties an enquiry is in relation to
* Added a new setting under 'Settings > General > Miscellaneous' to specify what happens should an off market property URL be accessed: Still show the property details or do a 301 redirect back to the search results page
* When converting an applicant from an enquiry ensure the address is copied across if present, such as enquiries received from Rightmove
* Enhanced searching by address keyword on frontend by catering for comma-delimited search terms (i.e. High Street, Basildon)
* Return draft properties in backend during AJAX property searches (i.e. when booking a viewing) with " - Draft" appended
* Added a quick 'Update' shortcut link on applicant requirements record to update match price range when max price is updated
* Date fields changed throughout to use HTML date field type instead of jQuery datepicker plugin. This should mean formatting of dates are relevant to locale
* When a user login is created from a contact, ensure first and last names are filled on the WordPress user record. Not used anywhere by Property Hive but for the benefit of any third party plugins using the user details
* Corrected undefined error when selecting applicant solicitor on a sale record
* Corrected edit functionality for EPCs and Brochures in media section on property record
* Declared compatibility for WordPress 5.8.2

= 1.5.24 - 2021-11-03 =
* Added ability to include Recaptcha V3 on enquiry and registration forms
* Added ability to include hCaptcha on enquiry and registration forms
* Added 'Remove Tenant' functionality to tenancies with multiple tenants. Will write to history when a tenant is removed
* Don't do currency conversion if currency requested is the same as the property currency. Just show price entered in this case
* Round prices that have undergone currency conversion to prevent decimal places showing
* Added filter 'propertyhive_summary_description_nl2br' to be able to turn nl2br off in summary description template on property details page
* Passed in additional args to 'propertyhive_property_map_actions' action

= 1.5.23 - 2021-10-25 =
* Added filter 'propertyhive_add_property_on_market_change_note' to allow disabling of note being written when on market status changes
* Added filter 'propertyhive_add_property_price_change_note' to allow disabling of note being written when on market status changes
* Filter Similar Properties in enquiry auto responder by selected match statuses
* Corrected issue with column/loop classes being wrong in similar properties shortcode causing formatting issues
* Further tweak to vimeo regex in Fancybox

= 1.5.22 - 2021-10-19 =
* Auto-restart email queue cron job if found to not be running for whatever reason
* Added necessary filters and new field types for upcoming Bookings add on
* Allow contact to be created from enquiry if name or email address is present. Previously it required both
* Display owner solicitor in property owner metabox
* Catered for scrollwheel being false in property_map shortcode when using OSM
* Corrected undefined index PHP error in Elementor widgets introduced in last release regarding image URLs
* Corrected issue with applicant not showing on sales list

= 1.5.21 - 2021-09-29 =
* Broader support for London postcodes. Searching for WC2 for example will include properties in WC2E, whilst still excluding properties in WC22
* Added filter to enable Elementor Portfolio widget to work when images are stored as URLs
* Changed how dimensions appear in output full descriptions with removal of brackets and bold formatting. A lot of the time we saw it where dimensions had the imperial/metric equivalent in brackets which resulted in double brackets
* Added new action 'propertyhive_exchange_rates_updated' when currency exchanges rates are updated
* Corrected issue with certain Vimeo links being broken by Fancybox by manually applying patch (https://github.com/fancyapps/fancybox/issues/2498)

= 1.5.20 - 2021-09-07 =
* Added assigned properties counts onto the custom field grids
* Added India, Pakistan and Saint Vincent and the Grenadines as countries
* Added support for adding custom icons for availabilities when using Map Search add on
* Default currency field in search form if existing cookie set and being used

= 1.5.19 - 2021-08-17 =
* Added New Zealand, Greece and Switzerland to list of supported countries
* Added new function to output file upload field on records. Done in conjunction with Template Assistant add on which now allows additional fields of this type to be added
* Added support for Yoast Duplicate Post plugin

= 1.5.18 - 2021-07-29 =
* Corrected potential issue with auto-matching whereby date that auto match was enabled (which has an effect on which properties are returned) would be updated when updating settings, even if already enabled
* Added Select All / None options to match screens
* Output date and time in settings that auto match was enabled
* Added management type filter to tenancy list
* Ensured DONOTCACHE constants are set on My Account page with scope to add more

= 1.5.17 - 2021-07-20 =
* Ensured enquiry form fails if disclaimer tickbox not ticked
* Added ability to record a solicitor on a contact record. This will be used as the default solicitor then on any offers generated going forward
* Improved support for tenancies with no end date, ensuring filters work and labels are correct
* Added ability to store notes/details against a management key date
* Added filter 'propertyhive_show_tenancy_lease_length' to hide lease length. Not applicable for example in Scotland with private residential tenancies where they don't have a fixed length
* Added filter 'propertyhive_tenancy_lease_types' to alter the tenancy lease types
* Declared compatibility for WordPress 5.8

= 1.5.16 - 2021-07-06 =
* Store a concatenated address for properties and contacts and use that in backend searches, instead of doing 6 individual query JOINs on each individual address field. Should reduce search query times by 75%+, especially on larger datasets
* Corrected Property Hive Only mode being used when White Label add on active

= 1.5.15 - 2021-06-28 =
* Ensure 'Features' tab isn't shown in Elementor Tabbed Details widget when no features exist
* Only show warning regarding email log not running on dashboard to prevent query being ran on every page
* Corrected issues with price columns in lists not showing decimal places when entered

= 1.5.14 - 2021-06-23 =
* Added support for media stored as URLS in Elementor Tabbed Details widget
* Added filter 'propertyhive_key_date_upcoming_days' to change key date upcoming days threshold. 7 days by default
* Added filter 'propertyhive_tenancy_management_types' to tenancy management types
* Added custom JS event 'ph:toggleSearchDepartment' when department toggled in search form
* Corrected issue with tenancy rent and deposit saving with decimal points

= 1.5.13 - 2021-06-14 =
* History & Notes grid loaded when being viewed, not when record is loading. This should increase load times of all Property Hive records
* Searching by address keyword to now include country name as included criteria on sites that operate with multiple countries enabled
* New attributes added to properties shortcode (show_order, show_result_count, pagination) in preparation of Elementor/full site editing support. More to follow in coming weeks on this.
* Added new actions before and after property enquiry wp_mail function called: 'propertyhive_(before|after)_property_enquiry_sent'
* Moved filter 'propertyhive_property_enquiry_sent' earlier in process to more logical place

= 1.5.12 - 2021-06-07 =
* Updated currency exchange cron due to existing API no longer being available, plus multiple other enhancements and optimisations surrounding storing currencies
* Added new filter 'propertyhive_show_tab_counts' to turn off counts in tabs on property and contact recoods
* Added new filter 'propertyhive_features_autocomplete' to disable features autocomplete functionality
* Corrected plugin update warning showing when an active license key exists but was due to renew in 30 days or less
* Corrected Elementor Street View widget not working

= 1.5.11 - 2021-06-01 =
* Added new 'Property Hive-Only Mode' on user profiles making it possible to hide standard WP functionality and promote Property Hive functionality making it easier for negotiators wishing to use Property Hive as their primary estate agency CRM
* Added new 'propertyhive_floor_area_output' to modify formatted floor area output
* Added new 'propertyhive_site_area_output' to modify formatted site area output

= 1.5.10 - 2021-05-24 =
* New merge contacts tool allowing you to merge duplicate contacts. Available by selecting the contacts you wish to merge and choosing 'Merge' from the bulk actions dropdown
* Added additional negotiator fields to user profiles including telephone number and photo upload
* Updated PH_Property object to contain negotiator related properties ($property->negotiator_name, negotiator_telephone_number, negotiator_email_address and negotiator_photo)
* Added new Elementor widgets for property office information
* Added new Elementor widgets for property negotiator information
* Added new tenancies grid to contact record showing all tenancies this contact is a tenant on
* Corrected issue with related notes not showing in notes grids

= 1.5.9 - 2021-05-17 =
* Offers, Sales and Enquiries grids on property and contact records updated to new grid layout with status filter
* Added filter 'propertyhive_search_form_rent_frequency' to change rent frequency used in search forms. Defaults to 'pcm'
* Ensure thumbnail heights are consistent and work when lazyloading in effect
* Corrected issue when viewing enquiry record introduced in last release
* Declared compatibility for WordPress 5.7.2

= 1.5.8 - 2021-05-10 =
* Updated contacts viewing grid to use new UI to fit in better with WordPress styling and include filters
* Updated properties viewing grid to use new UI to fit in better with WordPress styling and include filters
* Added 'Per Day' rent frequency to commercial properties
* Changed monetary input fields to display value with decimal and thousands separators
* Added Book Viewing link on enquiry and auto-populate property and applicant
* Removed notification about missing Google Maps API key warning if map provider is OpenStreetMaps
* Added Back To Search Elementor widget
* Added 'propertyhive_enquiry_email_show_manage_link' filter to allow users to prevent manage link from showing in enquiry email
* Deleting a user with role propertyhive_contact removes any meta keys for contacts that links this user to a contact to prevent a 'floating' user with no relationship
* Deleting a contact where a user login has been created deletes the user in question to prevent a 'floating' user with no relationship
* Fake a window resize event on Flexslider image load to get around issue with wrong image height being calculated when lazy loading being used
* Corrected map not loading correctly in Elementor tabbed details widget when OpenStreetMaps chosen as the provider
* Corrected deprecated Elementor namespaces warnings
* Corrected issue with license not showing as valid when within weeks of expiry

= 1.5.7 - 2021-05-03 =
* Added counts to tabs on property and contact records showing number of items in each tab (viewings, offers etc)
* Added 'Enquiries' tab and list to contact record displaying enquiries made by that contact
* Create applicant profile when creating a contact from an enquiry. Previously, unless you completed requirements there and then, the contact would go in as a contact with no relationships and would be easy to lose / result in duplicates
* Related to the above, when an applicant profile is created it will use the price and bedrooms of the property being enquired about as the basis of the relationship. A notice will also appear alerting you to this fact
* Added notification at the top of an enquiry record if a viewing between that contact and property already exists. This prevents confusion should an enquiry be a chase/follow up enquiry, or if someone else has dealt with the enquiry already
* Added a link to enquiry emails sent allowing you to jump straight to the enquiry in WordPress. The link won't be included if the Enquiries module is disabled or the GDPR settings specify enquiries shouldn't be stored
* Changed email address shown on enquiry record to be a mailto link
* Updated jQuery UI CSS and images to match version of jQuery UI included by WordPress

= 1.5.6 - 2021-04-27 =
* Added preliminary support for Oxygen site builder by ensuring Property Hive templates still load
* Removed comments from map JS as it was sometimes breaking sites when caching plugins minified the HTML
* Default virtual tour tab to use embedded video instead of link in Elementor tabbed details widget
* Corrected Vimeo links used in any Elementor widgets that reference embedded virtual tours
* Corrected Elementor tabbed details widget following Elementor update which seemed to cause PHP error

= 1.5.5 - 2021-04-20 =
* Added previous and next links to top of viewings when there are multiple viewings for same applicant/property
* Added date filter to admin offers and sales lists
* Exclude password protected properties from shortcode output
* When adding a tenancy the automatically calculated end date will be 1 day less the selected term. So a 12 month tenancy starting on 1st April 2021 will now have an end date calculated as 31st March 2022, as opposed to 1st April 2022
* Added ability to delete a management key date from tenancies and properties
* Can now search tenancies in backend by property address or tenant name(s)
* Corrected issue with searching for viewing, offers and sales by address
* Added office ID class to individual property results and single property body tag on frontend
* Declared compatibility for WordPress 5.7.1

= 1.5.4 - 2021-04-13 =
* Added new rent frequency of 'Per Day' for lettings properties
* Correct YouTube links used in any Elementor widgets that reference embedded virtual tours, converting https://www.youtube.com/watch?v=xxxxxxxx to https://www.youtube.com/embed/xxxxxxxx
* Added a prompt to import demo data for new installations of Property Hive, including ability to dismiss it
* Added a new 'Demo Data' settings tab for new installations of Property Hive, including ability to hide it
* Ensure AJAX grids load on property record (i.e. Viewings grid) when block editor used for whatever reason (i.e. when using Houzez and Houzez Data Bridge add on)
* Added filter 'propertyhive_default_applicant_send_matching_properties' to specify default 'send matching properties' status when an applicant is created

= 1.5.3 - 2021-04-06 =
* Changed default rent frequency to be PA when adding commercial properties
* Added oEmbed option in Elementor embedded virtual tours widget
* Default my account tab if hash is present in URL (i.e. #my-account-saved_searches)
* Swapped order of address meta fields queried during keyword search to resolve issue when keyword and radius is set
* Corrected issue with setting property on new viewing if address has an apostrophe
* Include new note type of 'status_change' in notes grids to support old way of recording maintenance job status changes
* Improved sanitization of locations when selecting them on a property record to prevent issue with new terms being created

= 1.5.2 - 2021-03-29 =
* Display feedback received date on viewings
* Added classes to single property page body tag for department, on market, featured and taxonomy fields eg. availability, marketing flags. This allows different styling to be applied based on a property's features or for elements to be shown/hidden (for example, hide the enquiry button if a property is not on market)
* Improved contact creation from enquiry including better checking for phone numbers and email addresses to support enquiries from different sources where the field names aren't always the same
* Added filter 'propertyhive_tenancy_meter_reading_types' to customise meter reading types
* Added new settings to store appliant registration and login pages. This allows us to redirect to the my account page if someone is already logged in but lands on the login page, as well as use by other add ons
* Swapped meter readings and management tabs
* Corrected undefined index error on tenancies list when tenants have no contact details entered
* Corrected typo in various success messages: succesfully -> successfully
* Corrected action name for custom deposit scheme fields

= 1.5.1 - 2021-03-23 =
* Added support for Rank Math SEO plugin whereby property taxonomies and off market properties are removed from XML sitemaps
* Added new Elementor widgets: Floorplan Link, EPC Link, Brochure Link, Enquiry Form Link, Virtual Tour Link
* Added filter 'property_search_results_thumbnail_size' to allow customisation of thumbnail size used in search results. New setting in Template Assistant add on utilises this
* Added event details to new viewing details lightbox
* Updated Fancybox jQuery library from 3.3.5 to 3.5.7
* Corrected issue with department not getting set by default in applicant registration form following new custom department feature

= 1.5.0 - 2021-03-15 =
* Added the ability to add custom departments under 'Property Hive > Settings > General'. This should satisfy one of our most common support queries, as well as open Property Hive upto a whole host of new businesses
* New property management and tenancies module taken out of BETA
* Added address of contacts to new viewing details lightboxes
* Added new 'propertyhive_lightbox_contact_details' action to new viewing details lightboxes
* Corrected issue with owner/landlord details not showing in new viewing details lightboxes
* Corrected Elementor map widget not showing options since recent Elementor update

= 1.4.79 - 2021-03-08 =
* New workflow when clicking to view viewings from a property or contact record. We'll now show a popup that shows viewing details, allows you to add notes and perform actions, and paginate through them without needing to click into the actual viewing. Aimed at making following up viewings a quicker process
* Added ability to add additional applicants to an existing viewing
* Added ability to store multiple applicants against offers and sales
* Added 'Dear' field to contact details with support in all email, document and SMS templates
* Added new filter 'propertyhive_email_process_limit' to set the number of emails processed per batch from email queue
* Added Luxembourg as supported country
* Corrected misnamed variable to prevent potential error notice on notes grid
* Corrected chance of PHP notice showing on offers and sales grids when the applicant didn't have any contact details entered
* Declared compatibility for WordPress 5.7

= 1.4.78 - 2021-02-22 =
* Added ability to export generated applicant lists to CSV
* Display applicant phone number and email in Viewing, Offer, Sale and Tenancy grids on property records
* Corrected/added actions in tenancy and management meta boxes to allow adding of custom fields using Template Assistant add on
* Added new 'propertyhive_subgrid_link_target' filter to specify links in subgrids should open in a new tab
* Tweaks surrounding storing, querying and displaying of properties on enquiries
* Improve readibility of labels output on enquiry details page (i.e. change email_address to 'Email Address')

= 1.4.77 - 2021-02-12 =
* Added new Elementor widget: Embedded Virtual Tours
* Added ability to add embedded virtual tours to 'Tabbed Details' Elementor widget
* Added new filter 'propertyhive_show_admin_menu_enquiry_count' to determine if open enquiry count in admin menu should be calculated and displayed. Should return true or false, defaults to true
* Corrected issue with viewing grids not loading on property and contact records when multiple attending staff

= 1.4.76 - 2021-02-11 =
* Added Barbados as supported country
* Added 'country' attribute to [properties] shortcode
* Refactored recently added second/third viewing functionality to have less impact on performance on large sites. Note: Will only effect viewings saved going forward
* Added 'Select All' checkbox to custom field grids
* Restructed the viewing, offers and sales grids on property and contact records with added filters so columns in these grids can be modified
* Added new actions and filters surrounding maps and coordinates to add support for the upcoming what3words add on
* Improved logic surrounding searching for contacts by telephone number
* Released v1 of the new tenancies/management module in BETA
* Declared compatibility for WordPress 5.6.1

= 1.4.75 - 2021-01-04 =
* Display all applicant profiles when editing requirements, if multiple exist
* Added support for multi-select dropdowns in registration and requirements forms on frontend for locations, property types and any custom fields of type multi-select
* Increased default max zoom level of OpenStreetMap on property edit screen
* Optimisations to recent 2nd/3rd viewing functionality
* Corrected issue with notes grid not re-loading after adding a note. Occured when mailouts existed in the notes history
* Add new actions to property coordinates meta box in preparation for upcoming what3words add on

= 1.4.74 - 2020-12-16 =
* Ensure viewing feedback is still shown on a property record even after it's progressed to an offer
* Flag if a viewing is a 2nd, 3rd viewing etc. Shown in the 'Status' column in every viewings grid and when on a viewing record
* Added ability to search contacts in main contacts list by address
* When doing a contact lookup, when adding a viewing for example, to help differentiate between contacts with the same or similar names we'll now display the phone number and email address in the results
* Tweaks to search logic when searching for a property when adding a viewing so any combination of house name/number and street should return the relevant result. Previously you could only search for one or the other
* Include property 'Location' field in REST API
* New Elementor widget, Floor Area, for commercial properties
* Added ability for third party add ons to add their own Property Hive Elementor widgets
* When using the [properties] shortcode and wanting to filter by commercial property type, but commercial is the only active department, don't require department to be explicilty passed through
* Added filter 'property_negotiator_exclude_roles' to negotiator drodowns to give more control over roles excluded. Added for our Property Portal add on whereby agents can now have a user login
* Added filter 'propertyhive_rest_api_property_field_callback' so data returned from REST API can be modified
* Correct PHP notice on saving property with no size entered regarding non-numeric values
* Declared compatibility for WordPress 5.6

= 1.4.73 - 2020-11-29 =
* Match properties to applicants based on freetype location and radius (if radial search add on active). New setting under 'Property Hive > Settings > General > Miscellaneous > Applicant Options' to enable this
* Added ability to filter viewings, offers and sales by office
* Added ability to filter viewings and appraisals by date
* Property searches by address on the frontend now take into account 'St.' vs 'St'
* Similar properties shortcode now doesn't require a property ID to be passed through as an attribute and works off property being viewed
* Corrected commercial properties not showing as POA when loaded via AJAX, such as when using the Infinite Scroll add on
* Corrected applicants not being carried across when using 'Book Second Viewing' action
* Display a notice if there are applicant match emails queued but the email queue cron doesn't appear to be running
* Added status of match email to notes grids to highlight if it's queued or failed
* Added new filter 'propertyhive_default_commercial_floor_area_unit' to specify default units when entering commercial properties
* Added new filter 'propertyhive_default_commercial_search_floor_area_unit' to specify units used in search forms when filtering commercial properties by size
* Declared compatibility for WordPress 5.5.3

= 1.4.72 - 2020-10-27 =
* Added ability to add Cc/Bcc email addresses when sending matches
* Corrected marker anchoring on property details map when using the Map Search add on and a custom icon has been uploaded
* Prevent notes from getting added multiple times by disabling the 'Add Note' form whilst one is getting submitted
* Run commercial rent frequency through translation function so they can be translated/substituted
* Added new filter 'propertyhive_always_show_applicant_relationship_name' to allow applicant relationship name field to be shown all the time, not only when multiple relationships exist
* Added new filters 'propertyhive_currency_symbol' and 'propertyhive_currency_prefix' so currency symbol and prefix can be overwritten
* jQuery migrate tweaks to support later jQuery version

= 1.4.71 =
* Added ability to pin notes so they stick to the top
* Added ability to add multiple applicants to a viewing
* When multiple applicant profiles exist, add the ability to give each one a name to differentiate between them
* Include images, floorplans, brochures and EPCs in REST API
* Added Morocco to list of supported countries
* Added alignment option to Elementor price widget
* Corrected issue with deleting applicant profiles deleting the wrong profile
* Corrected issue with commercial rent actual values being calculated

= 1.4.70 =
* Added ability to choose a different map provider than Google. OpenStreetMap being the only alternative at present but with scope now to add more. Can be selected under 'Property Hive > Settings > General > Map'
* Added filter 'propertyhive_remove_media_on_property_delete' to disable the fact media is deleted when properties are permanently trashed
* Ensured $property->tenure works for commercial properties
* Maps shown on the property details page caters for map markers where anchor should be centre of icon (i.e. when a circle is used as the marker icon). Inherits icon and anchor setting from Map Search add on

= 1.4.69 =
* Added new Property Enquiry Form Elementor widget
* Added new Property Floorplans Elementor widget
* Added new Property EPCs Elementor widget
* Added option to hide thumbnails in Elementor Images widget
* Corrected 'blank option' in commercial property type search dropdown not taking effect
* Added setting to allow users to change email address manual matches are sent from by default
* Added support for new 'Keyword' field in search forms allowing a generic keyword to be entered, e.g. Parking. This will then search the address, features, summary description and full description
* Added hooks to 'Generate Applicant List' page so third parties can add their own fields and filter results accordingly 
* Corrected conversion of sqm sizes to sqft for commercial properties when storing these for filtering and ordering
* Corrected class name for image setting row
* Use .on() listener when listening for search form department changes to cater for search forms written to DOM dynamically (i.e. in popups)
* Tidied up slider control code to be more generic so third parties can add their own slider controls that aren't price or bedrooms related
* Declared compatibility for WordPress 5.5.1

= 1.4.68 =
* Added a migration script upon update to set the on_market_change_date field where one doesn't exist. This will be set to the property published date. As we use this field when ordering properties by date now we need it to be set
* All shortcodes to use this _on_market_change_date if specifying they should be ordered by date. Previously only the recent_properties shortcode would do this

= 1.4.67 =
* Return JSON responses accordingly from AJAX requests where applicable to comply with recent jQuery changes made in WP 5.5

= 1.4.66 =
* Corrected jQuery changes in WordPress 5.5 breaking add media functionality
* Added filters to all notes grids so third parties can hook in and add their own notes. Done for the upcoming Microsoft Graph integration

= 1.4.65 =
* Added ability to filter commercial properties by price and/or rent. Accompanying Template Assistant add on too allowing you to add these fields to search forms
* Added ability to add classes commercial-sales-only or commercial-lettings-only to form controls to show them based on commercial for sale/to rent selection
* Corrected new 'Recently Viewed' tab/popup showing on non-Property Hive related pages
* Added Qatar to list of supported countries
* Declared compatibility for WordPress 5.5

= 1.4.64 =
* Hide users with role 'subscriber' in all negotiator related lists
* Make price qualifier on commercial not only a 'For Sale' related field
* Available date output on frontend to read 'Now' if date passed
* Replace [applicant_name] tag in owner viewing confirmation emails
* Updated Flexslider jQuery slideshow plugin to 2.7.2
* Added new 'Get Involved' tab to settings area
* Added support for image field type in admin settings. Used by Template Assistant add on when creating additional fields of type 'Image'

= 1.4.63 =
* Added new 'Property Image' Elementor widget to show specific image in single property template
* Added new 'Recently Viewed' tab to top-right of Property Hive screens (next to Screen Options) to quickly jump between recent records
* Added new [property_static_map] shortcode to pull in static image from Google Maps. Cost by Google is a lot less for static maps so useful for sites that get thousands of hits
* Set default office when adding a property to users office if present
* Tweaks to how individual address fields automatically pre-fill after typing in a display address
* Added new filter 'propertyhive_description_output' to full description output

= 1.4.62 =
* Added bedrooms and property type to admin property list
* Hid owner column in admin property list if contacts module is disabled
* Added 'Record Enquiry' action button to property record
* Added price in property enquiries
* Changed EUR currency to appear after price
* Price slider to cater for when currency not GBP
* Sort enquiry sources alphabetically preparing for when we can import enquiries from third party sources such as property portals
* Don't output POA twice if commercial is POA on both sale and rent
* Ensured children properties are deleted when parent property deleted (i.e. units of a commercial property)
* Added html_entity_decode() on 'From' headers in emails to prevent ampersands in company name appearing as &amp;
* Declared compatibility for WordPress 5.4.2

= 1.4.61 =
* Fix to recent Elementor module causing duplicate components to appear

= 1.4.60 =
* Elementor support added allowing you to build the property details page using Elementor's Theme Builder
* Added ability to generate auto-incrementing reference numbers when adding new properties. New setting found under 'Property Hive > Settings > General > Miscellanous'
* Added support for price, rent and bedrooms sliders in search forms. Can be added to search form using latest Template Assistant add on update

= 1.4.59 =
* Virtual Tour videos on YouTube and Vimeo to open in a lightbox by default on property details page
* Availability dropdown added in search forms to adhere to department selection
* Added new setting to 'General > Miscellaneous' allowing you to change how commercial properties are displayed in results in terms of whether units are returned or not
* Updated the REST API to use PH_Query methods so parameters can be passed in to filter properties accordingly (e.g /wp-json/wp/v2/property/?minimum_bedrooms=2)
* Added virtual tours to the REST API
* Changed office CPT from being publicly queryable so they don't get indexed
* Added ability for third party plugins to add additional checks in property matching

= 1.4.58 =
* Added ability to add labels to virtual tours. Will update the button shown on details page accordingly
* Added ability to specify which availabilities apply to which departments. Only the relevant availabilities will then be shown when editing a property
* Ensured contact type is set accordingly based on which list you come from when adding new contact
* Ensured full address is shown in enquiry body to agent instead of display address
* Fixed typo in text domain: properthive
* Fixed typo in enquiry form success message: succesfully
* Fixed typo in settings: atttached
* Updated license key links to new subscription product URL
* Declared compatibility for WordPress 5.4.1

= 1.4.57 =
* Corrected issue with property owner address not saving when adding appraisal
* Added Turkey to list of supported countries
* Optimisation to scheduled task that updates overseas properties prices/currencies
* Improved support for PHP 7.4
* Removed deprecated like_escape() function
* Fixed a few undefined variable errors appearing in logs
* Declared compatibility for WordPress 5.4

= 1.4.56 =
* Added new 'Property Marketing Statistics' area to property edit screen under 'Marketing' tab showing number of website hits with date range search. In future this could also hold number of impressions in search results, brochure download, enquiries and more.
* Added support for office dropdown in search forms being multiselect following last update only applying to taxonomies
* Allowed for third party relationships to be deleted by leaving category blank and saving contact
* Corrected issue with not being able to set a contact as having multiple third party relationships.

= 1.4.55 =
* Enqueue new multiselect JS and CSS for use across various parts of Property Hive and add ons
* Added new form control type of multi-select so multiple locations and property types in search forms can be searched at anyone time. Update also applied to Template Assistant add on to reflect this

= 1.4.54 =
* Added ability to assign negotiators to an office. Doesn't do anything yet but allows us in future to default lists and widgets to their office only which is normally all a negotiator is interested in
* Added company name in solicitor searches and summaries on offers and sales
* Changed applicant and solicitor summary outputs on viewings, offers and sales to be changeable via filters (e.g. 'propertyhive_sale_applicant_fields') so additional information can be added
* Ensured searching for appraisals takes full property address into account
* Corrected issue with attending staff not being saved when adding viewings or appraisals

= 1.4.53 =
* Added new filter to History & Notes grids to filter on note type (i.e. All, Note, Mailout, System Change)
* Added support for postcodes being entered in address keyword field in search forms with no space
* Automatically fill match price range fields on applicant profile when entering max price for the first time
* Small performance optimisation when generating applicant list

= 1.4.52 =
* Searching by keyword for viewings, offers and sales in admin area now also searches property address and applicant name
* Notes entered now get associated with parent record(s) also. For example, entering a note on a viewing will associate the note with the property, owner and applicant too and display the note in their respective notes grids.
* Declared compatibility for WordPress 5.3.2

= 1.4.51 =
* Added status filters to offers and sales lists
* Added ability to sort offers and sales by status column
* Changed 'Unattended' to 'Unaccompanied' when no viewing staff selected
* Checked to see if attending staff have changed on appraisals and viewings before delete. Previously it would create new meta keys each time a record is saved
* Corrected issue with notes not displaying on property record when enquiries existed under the 'Enquiries' tab due to global $post variable being overwritten. No notes were lost.
* Added Denmark, Finland and Sweden as supported countries
* Declared compatibility for WordPress 5.3.1

= 1.4.50 =
* Added the ability to mark applicants as 'Hot'.
* Clearly identify hot applicants in the main applicants list
* Updated property matches to put hot applicants first and flag them accordingly
* Moved 'History and Notes' to it's own tab for all post types. This gives it more space, makes it more readable for long notes, and also allows us to now expand on this in future by allowing notes of different types etc
* Updated edit screens so it now remembers which tab was selected and default back to that tab upon save or refreshing of the screen
* Added an office filter to the Enquiries list
* Show 'Property' or 'Properties' accordingly in enquiry email based on whether multiple properties are present or not
* Added new option to taxonomy dropdowns to only include top-level terms
* Updated price templates to include price qualifier for commercial sales properties
* Renamed 'Sale Date / Time' to just 'Sale Date' in column header in sales list
* Increased number of match emails sent every 15 minutes from 5 to 25
* Passed shortcode attributes through to property search form filters as an extra parameter

= 1.4.49 =
* Updated 'Generate Applicant List' to replace from/to on price and beds fields for single fields
* Added support for negotiator_id attribute in [recent_properties] and [featured_properties] shortcodes
* Added Malta to list of supported countries
* Corrected issue with duplicate header and footer when previewing email
* Corrected issue when generating applicant list where, when filtering on property type or location, it wouldn't take into account parent/child terms
* Added new filter propertyhive_single_property_image_size so image size on details page can be changed. Default 'original'
* Added new filters 'propertyhive_property_enquiry_property_output' and 'propertyhive_property_enquiry_post_body' to property enquiry body
* Added new filter 'propertyhive_show_my_upcoming_appointments_dashboard_widget' to allow third party add ons to specify whether upcoming appointments widget should show
* Reversed previous REST API change due where underscore was removed from emta keys due to it not returning taxonomies
* Declared compatibility for WordPress 5.3

= 1.4.48 =
* Added new dashboard widget showing users next 10 upcoming appointments
* Added number to menu showing number of unassigned open enquiries
* Added new separate Google Maps Geocoding API Key field to 'Property Hive > Settings > General > Map'. Will appear when an add on that utilises server-side geocoding requests is active and allows geocoding requests to be made even if referer restriction is applied on main API key.
* Changed 'contact' post_type so publicly_queryable property set to false
* Added ability to order by more fields (i.e modified) without having case for each
* Prevented extra line breaks being output in commercial full description
* Corrected issue with search form field defaulting to wrong value when field in search form shares same name as property object property (i.e. 'bedrooms')
* Removed paged as hidden field so fresh search is ran again on form submit instead of sometimes staying on same page

= 1.4.47 =
* Added ability to choose contact type when adding contacts direct to prevent contacts getting lost, especially if no relationship was entered right away
* Removed hardcoded departments when determining default department in search forms in the event a third party plugin has added a new one
* Prepended meta key with _ when doing REST API requests to prevent 2 queries
* Added filters to REST API so data returned can be customised
* Declared compatibility for WordPress 5.2.3

= 1.4.46 =
* Added 'Company Name' as new field on contact details
* Display price qualifier in admin property list
* Don't save negotiator ID on enquiry if set to -1 as this was causing it to think it was assigned
* Fixed typo in third party contact categories: Solictor
* Allow decimal point in sales price

= 1.4.45 =
* Cater for no availabilities having been selected under 'Property Hive > Settings > Emails > Only Include Properties With Statuses' when returning matches
* Make contact an applicant when put onto a viewing if not already and add a note to their history
* Fixed undefined index error when matching properties when 'Send Matching Properties' field not present

= 1.4.44 =
* Record when a price change occurs from anywhere (i.e. done manually or via a property import) and add it to the property history. 
* Record when a property is taken off or put on the market and add it to the property history
* If a property has already previously been sent to an applicant, then re-include it in future matches if a price change has occured or if it's come back on the market since it was last sent
* Added a new setting to choose what property statuses should be included in matches. Useful if you don't want to send Sold STC properties to applicants, for example
* Added a new 'Match Price Range' setting to applicant requirements allowing you to specify the min and max prices of properties that should match with applicants. More about this can be found under 'Property Hive > Settings > General > Miscellaneous'
* Updated help text relating to entering the Google Maps API Key

= 1.4.43 =
* Added new 'Not on Market Only' filter to backend properties list
* Corrected issue with applicant requirements not saving following the last release
* Added new 'propertyhive_search_form_fields' filter to apply changes to all search forms. Done primarily to support the latest Radial Search add on release that allows users to now search by their current location
* Added position:relative to search form control CSS. As above, done to support the latest Radial Search add on release

= 1.4.42 =
* Added min/max floor area to commercial applicant requirements
* Improved commercial floor area min/max search when both set
* Scroll page back to top upon applicant registration so the success message is visible
* Allowed custom departments to be added via third party plugins - Done primarily to support our new Student Accommodation add on
* If someone re-registers with same email address then update existing details
* Catered for when floorplan isn't an image in floorplans meta box
* Catered for array passed in querystring and put in search form hidden fields
* Added address_two to geocoding request
* Added new filter 'propertyhive_price_output' to get_formatted_price() method
* Changed priority of admin scripts loaded to fix conflict with Salient
* Removed recent geocoding restriction as causing a few issues with address in Scotland
* Declared compatibility for WordPress 5.2.2

= 1.4.41 =
* Applicant matching added to property record so you can now see all applicants with criteria matching the property in question. From there you can then email them details of the property
* Added new office filter to reports
* Postal code added as geocoding restriction when making geocoding request in JS on property record to give better accuracy
* Added before/after actions in single-property/images.php template

= 1.4.40 =
* Added ability to enter separate fees for residential lettings and commercial properties to rent
* Added support for property type and location being passed through as an array on registration
* Open Property Hive admin menu when in an appraisal record
* Small tweaks to wording and padding on viewing email confirmation actions
* Added JS event triggers on enquiry form: ph:success, ph:validation and ph:nosend
* Give settings table rows an ID so they can be shown/hidden using JS
* Wrap text in <span> tag in form functions when outputting checkboxes

= 1.4.39 =
* Removed blank fields in applicant registration email sent to agent
* Corrected the wrong property summary description being shown when performing manual applicant match
* Shortcodes that output properties now have a new filter applied to the output
* Corrected office dropdown not working in search form because change of key
* Corrected undefined index due to wrong variable name when saving media
* Fixes and improvements to email queue in settings area
* Declared compatibility for WordPress 5.2.1

= 1.4.38 =
* Added new general setting to display lettings fees link next to price in templates
* Adjusted how single-property/meta.php is contructed by doing logic outside of the template. This means meta data can be changed through use of a new 'propertyhive_single_property_meta' filter instead of having to override template
* Media uploaded manually to the property record under the 'Media' tab is now actually attached to the property post. Previously it would show as 'unattached' in the media library
* Added new save actions to commercial details and department meta boxes so custom fields added to these meta boxes via the Template Assistant add on are saved accordingly
* Features are now trimmed (i.e. whitespace is removed) before they're returned from the get_features() method. Properties sent to Zoopla using the real-time format would get rejected if additional whitespace existed in features.
* Removed request to empty actions.js file
* Performed same removal \r\n from full descriptions on commercial properties to follow suit with recent change to residential descriptions
* Move how and where Emogrifier is loaded so it's only loaded when needed
* Declared compatibility for WordPress 5.2

= 1.4.37 =
* Amended change made in latest release relating to floorplan button labels. Now we'll just use the caption if one exists. Using the title effected too many sites and often wasn't a valid title and instead contained a filename or similar.
* Fixed typo in license notice
* Removed \r\n from full descriptions after running nl2br on formatted descriptions. When sending properties to third parties they would somethings also run nl2br() causing duplicated line breaks

= 1.4.36 =
* Added ability for commercial applicants to register through frontend if commercial department active
* Removed whitespace from formatted full description which was causing problems with Zoopla Real-Time feed and formatting in Rightmove RTDF add on
* Use floorplan title and/or caption as button label if present
* Corrected status default filter in viewings and appraisals lists
* Added new filter to viewings and appraisals lists to filter by attending negotiator
* Added new column to viewings and appraisals list showing attending negotiator
* Removed unecessary call to get_properties_in_view in PH_Query which set a transient which wasn't used
* Allow for p and br HTML tags in rooms/descriptions

= 1.4.35 =
* Added New 'Appraisals' module allowing the management of upcoming appraisals. Record the status, the valued price, the potential owner/landlord, and convert to an instructed property when won
* Added ability to send owner/landlord viewing email confirmations from a viewing's 'Actions' panel
* Added new [office_map] shortcode to show all or one office location on map
* Tweaked applicant match process to allow for better third party integration such as upcoming SMS add on
* Tweaked event (i.e. viewing and appraisal) start dates and duration to support upcoming calendar add on, including new filter to customise durations
* Declared compatibility for WordPress 5.1

= 1.4.34 =
* Corrected issue with enquiries and applicant registrations (and other AJAX related functionality) potentially not going through due to whitespace output in last release

= 1.4.33 =
* Added ability to order commercial properties by price
* Corrected 'From' email address on automated match emails. Previously it would come from the admin email address but now looks at how many properties contained within each email belong to each office and uses the email address of the office with the most properties.
* Added ability to search contacts from within WordPress by email address or telephone number
* For notes older than 24 hours display the actual date and time they were created instead, for example, of '2 months ago'

= 1.4.32 =
* Added ability to store property media as URL's instead of having them uploaded to the media library. Useful if importing properties from a third party and wanting to link direct to the media on their servers (if they allow you), thus saving diskspace. Setting accessible from 'Property Hive > Settings > Miscellanouse'.
* Added ability for a property enquiry to be assigned to multiple properties. Added due to upcoming Property Shortlist add on update allowing the user to make one enquiry about all shortlisted properties at the same time.
* Added 'default_department' attribute to [property_search_form] shortcode allowing you to override the default department selected. Useful when the primary department is sales but you want to display the search form on a lettings related content page, for example.
* Added new notice to backend if license is coming up for renewal or has expired.

= 1.4.31 =
* Added ability to email applicant viewing booking confirmations. Available in the 'actions' area on a viewing record. Email content can be customised under 'Property Hive > Settings > Emails'
* Only create default set of terms if it's the first time installing Property Hive. Previously if you deleted all the terms within a custom field and updated they'd get recreated
* If no license key is present, show update messages on plugins page when updates are available
* Added 'availability_id' as a new attribute for all property related shortcodes
* Corrected batch delete functionality on custom fields not working following recent security enhancements

= 1.4.30 =
* Added new 'Generate Applicant List' functionality
* Replace [name] tag in enquiry autoresponder if present
* Remove top margin from H2 and H3 in email styles
* Added support for new parameter commercial_for_sale_to_rent

= 1.4.29 =
* New filter 'propertyhive_address_fields_to_query' to allow specifying of which address fields to include when searching by keyword
* Remove country code if included in address_keyword search.
* Allow for reCAPTCHA in registration form
* Added new class to individual results allowing differentiation of department. Useful for when wanting to add different styling to sales vs lettings
* Fixes to featured property transient. Not really used anywhere at the moment but we should make more use of this in the future. (Credit to https://github.com/Corin123555)
* Declared compatibility for WordPress 5.0.3

= 1.4.28 =
* Removed dependency on third party site by storing jQuery UI CSS locally

= 1.4.27 =
* Extensive sanitisation and validation throughout
* Declared compatibility for WordPress 5.0

= 1.4.26 =
* Corrected potential vulnerability picked up by WordPress causing plugin to be removed from plugin repository

= 1.4.25 =
* Corrected results shown when using the 'Price Range' and 'Rent Range' fields to filter properties
* Added ability to create new applicant when entering viewings from 'Viewings' area
* Further improved persisting any search parameters that are present in the querystring but don't exist as fields within the form, meaning criteria isn't lost should a new search be performed
* Corrected the offer summary tab from not showing
* Corrected compatibility issue and certain admin scripts not being loaded when White Label add on being used
* Added a new get_formatted_full_address() method to PH_Contact object

= 1.4.24 =
* Corrected scenario where a search for 'W1' would return properties in 'SW1'
* Allow for entering of user details and property selection when entering an enquiry manually via WordPress
* Added new filter 'propertyhive_search_summary_length' in search/summary.php template to allow changing of summary length (defaults to 300)
* Corrected users shown in enquiry negotiator dropdown
* Remove 'Recently Sold' and 'Recently Let' flags from default list

= 1.4.23 =
* Added viewing confirmations. When adding/editing a viewing you can select which parties have confirmed
* Added new filters to Viewings screen you can then also filter by status
* Remove 'Mine' view option from property list
* Allow for homepage to be used as search results page
* Fixed POA display when properties loaded via AJAX (i.e. infinite scroll)
* Added ability to output message/HTML in shortcodes if no results
* Added new action to matching screen to allow other sending methods to be added in future (i.e. SMS property matching)
* Display the send method (e.g. email) when showing if property previously sent when matching
* Stripslashes when setting input value from $_GET
* Added CSS allowing for adding of flags by Template Assistant add on (a new feature coming soon)

= 1.4.22 =
* Allow for multiple property types to be chosen for residential properties. If you export properties to a third party sich as Rightmove the first in the list will be used
* Ensure order and current view are maintained when a new search is ran. Previously the view and order would reset back to the defaults
* Added a new 'imported_id' property to the PH_Property object. Useful when importing properties from a third party and wanting to get the ID of the property from the third party software. Can be called like $property->imported_id;
* Added new field type of 'recaptcha' meaning a Google reCAPTCHA can be added to enquiry forms
* Sanitize user input on applicant registration
* New action 'propertyhive_user_logged_in' called when applicant/vendor/landlord logs in
* Declared compatibility for WordPress 4.9.8

= 1.4.21 =
* Migrated from PrettyPhoto lightbox plugin to Fancybox 3
* Give each floorplan it's own action button as opposed to putting them into the same lightbox. This makes it clearer that more than one floorplan exists
* If an EPC is an image now open it in a lightbox. As EPC's can also be PDF's previously we would just open them all in a new tab.
* Don't do tax_query on taxonomies that don't belong to queried department. For example, no properties would previously be returned if searching for commercial properties but residential 'property_type' parameter was found in query string.

= 1.4.20 =
* Added new warning when Google Maps API key is missing to assist with common support issue and [upcoming changes](https://wp-property-hive.com/is-your-property-website-ready-for-upcoming-google-maps-api-changes/) to Google pricing on July 16th 2018
* Added ability to dismiss all warnings/notices regarding missing search results page or Google Maps API Key
* Added new minimum_price and maximum_price attributes to [properties] shortcode
* Added new setting under 'Settings > General > International' to specify the currency that prices searches are based on. Specifically for international agents who previously had to convert prices to GBP for them to work.
* Added foundations to plugins page to display important upgrade notices when future releases require special attention or might break something. 
* Added support for the new 'added_from' parameter when performing searches. Should be a strtotime() friendly format
* Added a new action when property enquiries are successfully sent. Put in place to cater for new Jupix Enquiries add on coming very soon.

= 1.4.19 =
* New 'Marketing Status' filter on admin property screen allowing filtering by 'On Market Only', 'Featured Only', by marketing flag, and by which portals the properties are active on (i.e. Show only properties active on Rightmove)
* Display chosen marketing flags on main admin property list
* Fixed the 'Order By' options shown in orderby.php template when searching for commercial properties on frontend
* Added support for 'negotiator_id' attribute in [properties] shortcode
* Added support for querying 'negotiator_id' should it be passed in the query string to main results page
* Added support for 'commercial_for_sale' and 'commercial_for_rent' attributes in [properties] shortcode
* Add labels to all registered taxonomies should a third party plugin use them
* Add labels to 'office' post type should a third party plugin use it
* Declared compatibility for WordPress 4.9.6

= 1.4.18 =
* Added support for GDPR in new settings area, including option to specify if properties enquiries are stored or not, as well as ability to add disclaimer text to forms
* Add notice to admin screens now if no page has been selected as the 'Search Results' page
* Exclude off market properties from Yoast XML sitemap
* Use marker icon from Map Search add on on property map shortcode if add on being used and icon has been uploaded
* Cater for multiple [property_map] shortcodes being used on the same page by allowing a unique ID to be passed into it, for example [property_map id="map_X"]
* Declared compatibility for WordPress 4.9.5

= 1.4.17 =
* New template hook 'propertyhive_template_not_on_market' to display a message when off market property is being viewed
* Remove 'Delete' button from primary office when managing offices to ensure a primary one always exists
* New filter, 'propertyhive_pppw_to_consider_bedrooms', for overwriting whether to include bedrooms when calculating PPPW. Defaults to true
* Added support for new query string parameters 'commercial_for_sale' and 'commercial_to_rent' to allow filtering of commercial properties by sales and rentals

= 1.4.16 =
* Change currency exchange rate provider to Google after finance.yahoo no longer exists
* Added ability to change negotiator and office under bulk edit properties
* Declared compatibility for WordPress 4.9.4

= 1.4.15 =
* Added new 'bedroom_bounds' attribute to [similar_properties] shortcode. Previously it would include properties with same number of bedrooms
* In [similar_properties] shortcode you can now pass the 'price_percentage_bounds' attribute as an empty string to exclude filtering properties by price
* Improved handling of search form display when 'deparment' field hidden. Previously it would default to stacking the fields but should now retain CSS display property
* Removed old unused files that could potentially cause unecessary security exploits

= 1.4.14 =
* Process email queue every 15 minutes so applicants receive matches sooner. Was previously hourly
* Corrected use of incorrect filter when trying to change the default sort order of search results
* Declared compatibility for WordPress 4.9.2

= 1.4.13 =
* Corrected issue with editing existing images and floorplans not working in Firefox
* New 'propertyhive_default_property_negotiator_id' filter to set default property negotiator
* New 'propertyhive_property_query_tax_query' filter to give further control over main property query on search results
* Declared compatibility for WordPress 4.9.1

= 1.4.12 =
* Added support for comma-delimited list of IDs to be passed to shortcodes
* Added term ID as column to custom field tables. Useful for when adding shortcodes and needing to know which IDs to pass in
* Updated Flexslider JS library to customised version which includes a destroy method
* Added shortcode name to 'shortcode_atts' function so developers can add custom attributes to existing shortcodes
* Added Mauritius and Norway to list of countries
* Declared compatibility for WordPress 4.8.2

= 1.4.11 =
* Corrected the [properties] shortcode not working when only the commercial department is active
* Added a new 'availability_id' attribute to the [properties] shortcode
* Changed the 'marketing_flag' attribute to be called 'marketing_flag_id' in the [properties] shortcode to match other attribute naming and to be clearer. Also added a fallback so old sites won't be effected by this
* Corrected the 2 letter ISO code for Austria in the list of countries from AU to AT
* Added Australia to the list of supported countries

= 1.4.10 =
* Ensure term name for type and location is included within registration email, as opposed to IDs
* Added ability to record reason why viewing was cancelled
* Changes made so third parties can hook into notes meta box when they register their own custom post types, such as the recent Maintenance Jobs add on

= 1.4.9 =
* Added support for commercial property types when using the [properties] shortcode. For example: [properties department="commercial" property_type_id=X]
* Added Ireland to the list of supported countries available to choose from
* Corrected applicant registration form when loaded dynamically (i.e. via AJAX)
* Corrected issue with applicants showing in viewing negotiator searches and dropdowns
* Declared compatibility for WordPress 4.8.1

= 1.4.8 =
* Added additional filters to allow third party add ons to hook into meta box functionality. Done in this instance for the new property maintenance add on.
* Corrected owner/landlord terminology

= 1.4.7 =
* Corrected owner/landlord login not showing the right tabs when multiple properties were assigned to the contact
* New price settings for international properties: 'Thousand Separator' (default to ',') and 'Decimal Separator' (default to '.')
* Added new form field type of 'checkbox' so checkboxes can be added to search forms and enquiry forms.

= 1.4.6 =
* Added ability for vendors and landlords to have user acounts so they can login and view their properties and past/upcoming viewings
* Added new filter 'propertyhive_store_in_recently_viewed_cookie' to turn off use of 'propertyhive_recently_viewed' cookie
* Allow loading of language files. Should go in "WP_LANG_DIR/propertyhive/propertyhive-admin-$locale.mo" and "WP_LANG_DIR/propertyhive/propertyhive-$locale.mo"
* Only set 'Reply-To' header on enquiries when a valid email address exists

= 1.4.5 =
* Added 'department' and 'office_id' attributes to all property shortcodes
* Added new filter 'propertyhive_rest_api_query_args' to REST API query

= 1.4.4 =
* Excluded registered applicants (users with role 'property_hive_contact') from 'negotiator' user dropdowns
* Corrected issue with property edit screen whereby office query would overwrite main $post variable. Noted when trying to use All In One SEO Pack plugin
* Updated get_formatted_deposit() method to cater for no deposit entered to prevent division by zero warning
* Added new actions to get_property_map() and get_property_street_view() functions so map and marker options can be adjusted
* Changed 'Property Type' control in search form to be of type 'property_type' instead of 'select' meaning it uses the standard taxonomy functionality

= 1.4.3 =
* Added new 'Email Queue' section to email settings area of Property Hive giving a frontend view of the queue
* Declared compatibility for WordPress 4.8

= 1.4.2 =
* When using the shortcode [property_map], check if Map Search add on is active and use same styling if any present
* When an error occurs during applicant registration, show actual error as opposed to generic message
* Include properties with 'Private' status when applicable when querying properties for shortcodes
* Added new 'bedrooms' meta query for matching of exact number of beds
* Added more ordering capability to [featured_properties] shortcode. Can now pass in 'meta_key' attribute
* Added ability to add user from contact edit screen in WordPress, or view user record/change password if already setup as WP user
* Also check WP users when checking if email address exists on registration
* Don't require postcode when clicking 'Obtain Co-ordinates' on property edit screen

= 1.4.1 =
* Added new [property_office_details] shortcode to quickly pull in office contact details.
* Added new welcome screen when Property Hive is activated for the first time.
* Allow applicants to view past and upcoming viewings when logged into their account. New viewings tab will only show if viewings exist and viewings module is active.
* If only one active department then hide 'Looking To Buy/Rent' field on applicant registration form.

= 1.4.0 =
* BETA release of applicant login, registration and account management. Includes new 'Property Hive Contact' user role and shortcodes [propertyhive_login_form], [applicant_registration_form] and [propertyhive_my_account]
* Guaranteed 'property' class exists when running post_class() filter. Done to accommodate [Infinite Scroll add on](https://wp-property-hive.com/addons/infinite-scroll/ "Infinite Scroll add on")
* Improved reliability of geocoding when getting coords by using more of the address
* Declared compatibility for WordPress 4.7.5

= 1.3.20 =
* Added new setting to specify if search by address keyword should do exact or loose search
* Added unique classes to fields in admin edit pages so can be targeted and hidden

= 1.3.19 =
* Added support for redirecting to a new URL when enquiry is successfully submitted. Useful if tracking codes are used
* Set 'From' name on enquiry auto-responders
* Cater for place name containing dashes when searching by address keyword (i.e. properties in Walton-On-Thames wouldn't show if searching for 'walton on thames')
* Added parking and outside space to the default single-property/meta.php template
* Declared compatibility for WordPress 4.7.4

= 1.3.18 =
* Updated queries relating to searching address to query exact terms, rather than 'where contains'
* Added two new actions to the single-property/meta.php template. Primarily used by the Template Assistant add on

= 1.3.17 =
* Fixed [properties] shortcode not displaying properties when department passed in as commercial
* Added 'bedrooms' and 'address_keyword' attributes to [properties] shortcode
* Added 'price_percentage_bounds' attribute to [similar_properties] shortcode allow you to specify the price ranges classed as 'similar'. Default to 10%
* Added 'Obtain Co-ordinates' quick link to Property Co-ordinates meta box. Useful when the address changes, or you want to revert back to the original co-ordinates

= 1.3.16 =
* Added new 'Incomplete Properties' report to see which properties are missing what (photos, floorplans, map co-ordinates etc)
* Improved plugin README file to include more keywords to increase chances of coming up in plugin search results

= 1.3.15 =
* Added support for date form field type for use in search and enquiry forms
* Cater for 'Available Date From' in query if 'availble_date_from' parameter received in query string
* Added Germany to list of countries

= 1.3.14 =
* Delete media when properties are permanently deleted. Will only delete media that isn't shared with other properties
* Don't show unsubscribe link in email footer if unsubscribe URL not present

= 1.3.13 =
* Added abiltiy to send auto-responder to users making enquiries. Includes message and three similar properties. Configurable under 'Property Hive > Settings > Emails'

= 1.3.12 =
* Updated a couple of default form labels to make them shorter. Done in conjunction with our new free and open-source [Honeycomb theme](https://wp-property-hive.com/honeycomb/ "Honeycomb theme")
* Added number formatting to property result count so totals over 1,000 are formatted with commas
* Declared compatibility for WordPress 4.7.3

= 1.3.11 =
* Fixed missing delete capability for outside space custom field
* Updated HTML generated by shortcodes to contain unique classes so people can target them using CSS
* Upon installation cater for scenario where fatal error could possibly be returned causing install to fail

= 1.3.10 =
* Corrected issue with ordering properties included by [properties] shortcode
* Added new optional 'property_type_id' and 'location_id' attributes to [properties] shortcode
* Added new filters to property, contact, viewing, offer and sale actions meta box allowing third parties to add additional actions
* If a 'featured image'/'post thumbnail' is ever requested for a property, 'fake it' by serving the first photo. Useful in scenarios such as RSS feed, REST API and SEO plugins that add an og:image meta tag

= 1.3.9 =
* Added ability to store multiple owners/landlords against a property
* Made improvements to ensure a blank description.php template isn't shown on the property single page
* Added support for minimum and maximum bathrooms and reception rooms in search results property query should they be passed in the query string

= 1.3.8 =
* Allowed third party add ons to override loaded template
* Allowed for third party property popularity stats to be displayed in reports via new filter. This allows stats imported from Rightmove when using the RTDF add on to show.
* Corrected calculated increase/decrease % shown on property popularity reports when comparing stats of previous timeframe

= 1.3.7 =
* Take reference number into account when searching for address/keyword via frontend
* Allow third party add ons to modify property admin list request query when adding additional filters
* Remove 'Month' dropdown filter from viewings, offers and sales admin lists
* Declared compatibility for WordPress 4.7.2

= 1.3.6 =
* Added first bulk-edit capabilities to properties on property listing page
* Improved the formatting of the 'Add Ons' settings page
* Added 'Add Ons' to the main Property Hive menu
* Ensure search forms added by shortcode always have a department field
* Corrected CSS issue with report popularity stacking
* Ensure Property Hive menu is expanded by default when editing viewing, offer or sale record

= 1.3.5 =
* Correction regarding items in WordPress menu being duplicated

= 1.3.4 =
* New Reports module
* New Property Stock Analysis Reports
* New Property Popularity Reports
* Prefill property enquiry form with users details if they're logged in

= 1.3.3 =
* Corrected issue with latest release and form fields in enquiry form

= 1.3.2 =
* Start tracking property views for use with future reports
* Fix PHP notices on contact record, shown when error reporting was cranked up
* Fix filter for  modifying enquiry form  fields

= 1.3.1 =
* Cater for when no dpeartment field is present on a search form. Previously it would result in no fields being shown at all
* Made improvements to the way we handle potrait photos in flexslider on the property details page by utilising the smoothHeight option
* Declared compatibility for WordPress 4.7.1

= 1.3.0 =
* New Viewings, Offers and Sales modules allowing you to track the dates and statuses of these. Perfect for agents that don't use property software
* New 'Settings > Modules' area making it possible to disable unused modules
* New 'Settings > Micellaneous' area where you can now select if property features should be freetype or checkboxes. Defaults to freetype
* New WordPress filters added to property enquiry sending process so the subject, recipient, headers and email body can be amended

= 1.2.8 =
* Support for Yoast SEO added. Includes exluding taxonomies from XML sitemap, adding first property photo as og:image, removing SEO columns from property list, and changing priority of Yoast option meta box to be at the bottom of the edit property page.
* New [property_street_view] shortcode for use on property pages
* New 'From' email address setting in 'Settings > Emails'. Changes the 'From' address of property enquiries. Should improve reliability of sending enquiries when sent from the same domain.
* In conjunction with the above, added a Reply-To header to enquiry emails
* Catered for the scenario when the department option in search forms is a hidden input field

= 1.2.7 =
* Added support for the WordPress REST API introduced into core in WordPress 4.7. Means you can now obtain properties via the API for use with third party applications
* Added new 'Property Hive News' widget to administrators dashboards

= 1.2.6 =
* Added ability to record notes against properties, contacts and enquiries
* Added ability to create new contact from an enquiry
* Added ability to edit existing photos, be it changing the alt description or replacing the photo altogether
* Declared compatibility for WordPress 4.7

= 1.2.5 =
* Introducing license keys. Can be [purchased here](https://wp-property-hive.com/product/12-month-license-key-subscription/) for priority support and updates to purchased add ons.

= 1.2.4 =
* Added new tab to property record which shows enquiries made about that property
* Added new helper properties to PH_Property object ($property->parting and $property->outside_space)
* Corrected issue with recently added property admin search

= 1.2.3 =
* Corrected issue with enquiry details not saving
* Corrected property rooms/descriptions meta boxes showing when they shouldn't
* Enable searching for addresses and reference numbers from admin property screen
* Default search/summary.php template updated to show up to 300 characters
* Add a little default styling to pagination

= 1.2.2 =
* Added support for matching commercial applicants to suitable properties
* Change default flexslider thumbnail width to 150px in thumbnail template

= 1.2.1 =
* Improve searching by postcode. Now caters for when search by first part of postcode only (e.g. SC1)
* Added 'blank_option' option to taxonomy form fields, for when no label shown
* Prevent error from being shown when added dropdown to search form with no options

= 1.2.0 =
* Applicant matching and emailing introduced
* Auto-matching can be enabled via settings to automatically email new properties to applicable applicants
* Added new 'Emails' settings area and set of templates introduced allowing customisation of emails sent
* Added new 'Match History' meta box to applicants showing a list of all previously sent match emails
* Added new 'Do Not Contact Via' option to contacts
* Fixed help tooltips not displaying

= 1.1.21 =
* Updated search form JavaScript to cater for multiple search forms on the same page
* Add one-time review prompt 30 days after activation or first update

= 1.1.20 =
* Added support for a country dropdown field in search forms
* ph_form_field() function updated to fallback to getting post meta if no value passed in or not obtainable from URL. Done to support latest update to [Front End Property Submissions add on](https://wp-property-hive.com/addons/front-end-property-submissions/)

= 1.1.19 =
* Allow multiple parking options to be selected on a property
* Corrected issues when properties exist in countries that are no longer selected in settings
* Re-obtain property co-ordinates when country is changed

= 1.1.18 =
* Corrected issue with country settings not saving correctly

= 1.1.17 =
* Added helper properties/methods to property to obtain office information (e.g. $property->office_address)
* Added active class(es) to nav items when on Property Hive page

= 1.1.16 =
* Added view/edit property link to WP admin bar
* Updated single-property/summary-description.php template to include line breaks
* Declared compatibility with WordPress 4.6.1

= 1.1.15 =
* Added support for PPPW rent frequency on properties. This marks the beginning of better support for student accommodation including the new [StuRents integration](https://wp-property-hive.com/addons/sturents-wordpress-import-export/)
* Declared compatibility with WordPress 4.6

= 1.1.14 =
* Add new shortcode [property_map] and function get_property_map() to output a property map
* Made EPC's clickable under 'Media' tab on property record
* Add support for any taxonomy (i.e. location, furnished, tenure) to be added as search form dropdown

= 1.1.13 =
* Add new method and shortcut for obtaining a property's furnishing option (i.e. $property->furnished)
* Add selected furnished option to property details meta.php template

= 1.1.12 =
* Fix recurring issue where properties don't appear on search page by default after updating settings
* Small tweak to default CSS to make single column layout 100% width

= 1.1.11 =
* Add new batch delete functionality on custom fields
* Add new method and shortcut for obtaining a property's marketing flags (i.e. $property->marketing_flag)

= 1.1.10 =
* Give users with the Editor role access to Property Hive, excluding the settings area
* Fix small formatting issue in property enquiry email body
* Split 'General' settings into sections
* Add new 'Google Map API Key' field to settings
* Add new 'Location' and 'Availability' filters to properties list in admin

= 1.1.9 =
* Added inputs for commercial contact details to office settings page
* Corrected issue with commercial description meta box showing on residential properties when editing property details
* Corrected issue with commercial rent not being output by get_formatted_price() method if property was to let only

= 1.1.8 =
* Added new methods and shortcuts to make it easier to get property related information in templates (e.g. $property->availability or $property->location)
* Updated price.php templates to use new helper methods
* Updated meta.php template to use new helper methods, and add availability and tenure

= 1.1.7 =
* Corrected issue with price qualifier, sale by and tenure not saving on residential sales properties

= 1.1.6 =
* Corrected issue with save_post hook getting overwritten causing conflicts with other plugins
* Corrected issue with monthly rental values getting calculated incorrectly when converting to different currency

= 1.1.5 =
* Added support for currency calculators meaning prices can be shown in different currencies. Can be achieved by passing '&currency=USD' for example in query string on results page
* Corrected issue with ordering residential properties by price following recent commercial property support
* Add support for separate headers and footers on proeprty pages. You can now have a header-propertyhive.php, for example, if you want a different header on property pages
* Declared compatibility with WordPress 4.5.3

= 1.1.4 =
* Added support for availability filter in search forms

= 1.1.3 =
* Fixed office filter on main property list in WordPress
* Added support for price, rent and floor area range dropdowns in search forms

= 1.1.2 =
* Made price and size columns sortable in WordPress admin property list
* Properties can now be assigned to multiple locations. Useful for when a property is on the border of two locations
* Validation added to General Settings around departments and countries

= 1.1.1 =
* Correction to class names following recent commercial update

= 1.1.0 =
* Support for commercial properties added to Property Hive
* Flush rewrite rules when the settings are updated and the property search page is set or changed. Was causing issues with search page not showing when first installing Property Hive

= 1.0.25 =
* Updated single property gallery to use wp_get_attachment_image() to enable RWD images (thanks to CHEWX - https://github.com/CHEWX)
* Move shortcode and form functions/classes to be accessible from anywhere. Was causing some themes to break as the admin area tried to render shortcodes
* Fix to the single property actions filter in how it passed arguments. Required for the new Printable Brochures add on - https://wp-property-hive.com/addons/printable-brochures/

= 1.0.24 =
* Updates to support compatibility with the new Map Search add on - https://wp-property-hive.com/addons/map-search
* New filter added to archive-property.php template allowing main results to be turned off
* Added $property as a global variable in the content-single-property.php template to make theming easier
* Updated orderby.php template to not show the dropdown if there are no options

= 1.0.23 =
* Added support for searching for properties by maximum bedrooms
* Added support for searching for properties by date added to site

= 1.0.22 =
* Add support for department in [recent_properties] shortcode

= 1.0.21 =
* Fix to pagination template where in some cases you couldn't paginate past page one

= 1.0.20 =
* Removed hardcoded HTTP links as property edit screen wouldn't work if served over HTTPS

= 1.0.19 =
* Added filters to property meta boxes so add ons and third parties can inject their own meta boxes into the property record

= 1.0.18 =
* Speed up adding features to properties by providing suggestions based on previously entered features
* Fix issue with deleting newly-created features and rooms
* Updated to declare support for WP 4.5

= 1.0.17 =
* Support case where department select in search form isn't a radio button
* Don't show big gap for tabs when adding non Property Hive related post type
* Fix duplicate filter name for office details settings
* Set default primary department when installing

= 1.0.16 =
* Fixed JavaScript bug introduced as a result of the last release

= 1.0.15 =
* Add office dropdown as search form field type so it can be added to the search form using filters
* Update search form JS to support department option being a dropdown instead of default radio buttons

= 1.0.14 =
* Add location dropdown as search form field type so it can be added to the search form using filters

= 1.0.13 =
* Add new latitude and longitude fields to office for displaying office map on website.
* Add new action 'propertyhive_save_office' for when saving office details
* Include street when searching for address keyword
* Fix entire enquiries section not showing

= 1.0.12 =
* Return [property_search_form] shortcode output as opposed to echoing it direct

= 1.0.11 =
* New [property_search_form] shortcode
* New [similar_properties] shortcode
* Fix custom fields getting reset on each update

= 1.0.10 =
* New 'Marketing Flags' custom taxonomy. Used for things such as 'Recently Sold', 'Chain Free' etc
* Updated to declare support for WP 4.4.2

= 1.0.9 =
* Added [properties] shortcode for displaying property results on other pages/posts. eg. [properties department="residential-sales"], [properties posts_per_page="2"]

= 1.0.8 =
* Stop offices, contacts and enquiries from appearing in front end search results
* Add 'propertyhive_countries' filter so custom countries can be added (https://wordpress.org/support/topic/can-add-more-countries-in-countries-where-you-operate?replies=3)

= 1.0.7 =
* Added support for third party contacts (Solicitors, Board Contractors etc) as a new contact relationship type

= 1.0.6 =
* Add support for adding properties in multiple countries
* Includes daily currency exchange rate update to normalise prices to GBP used for sorting

= 1.0.5 =
* Fixed major bug with wrong post ID potentially not being correct and data therefore not being saved correctly
* Incorrect display of owner details on property list when no owner set
* Tested with version 4.4.1 of WordPress
* Fix JS error on applicant relationship and improve contact relationships layout

= 1.0.4 =
* Add POA option to rental properties
* In property list only format price if one exists preventing PHP error
* Fix bug when trying to add applicant relationship to existing property owner
* Improved support for add ons wanting to integrate with core Property Hive plugin by adding more filters etc
* Fixed typo in virtual tour filter name

= 1.0.3 =
* Updates to README; updated description and screenshots

= 1.0.2 =
* New applicant management feature

= 1.0.1 =
* Added support for virtual tours

= 1.0.0 =
* First beta release of the software

== Upgrade Notice ==

= 1.4.21 =
We've swapped out the lightbox plugin from PrettyPhoto to Fancybox 3. Please ensure that you take a full site backup and test the site after updating, specifically anything related to lightboxes (photo gallery, make enquiry).