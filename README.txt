=== PropertyHive ===
Contributors: PropertyHive,BIOSTALL
Tags: property, real estate, estate agents, estate agent, property management, propertyhive, property hive, properties, property plugin, estate agent plugin, rightmove, zoopla, blm, rtdf, jupix, vebra, expertagent, dezrez, expert agent, expertagent, reapit, reaxml, letmc, acquaint
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=N68UHATHAEDLN&lc=GB&item_name=BIOSTALL&no_note=0&cn=Add%20special%20instructions%20to%20the%20seller%3a&no_shipping=1&currency_code=GBP&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted
Requires at least: 3.8
Tested up to: 5.2.2
Stable tag: 1.4.42
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

If you already use software such as Jupix, Vebra, Dezrez, Reapit, ExpertAgent, LetMC and more then it's easy to get your properties imported on an automatic basis at regular intervals so they display on your website. Our [Property Import Add On](https://wp-property-hive.com/addons/property-import/) can have you and running in minutes.

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
* Changde priority of admin scripts loaded to fix conflict with Salient
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
* Introducing license keys. Can be [purchased here](https://wp-property-hive.com/product/12-month-license-key/) for priority support and updates to purchased add ons.

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