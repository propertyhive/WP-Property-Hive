=== PropertyHive ===
Contributors: PropertyHive,BIOSTALL
Tags: property, real estate, software, estate agents, estate agent, property management, propertyhive, property hive, properties, property plugin, estate agent plugin
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=N68UHATHAEDLN&lc=GB&item_name=BIOSTALL&no_note=0&cn=Add%20special%20instructions%20to%20the%20seller%3a&no_shipping=1&currency_code=GBP&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted
Requires at least: 3.8
Tested up to: 4.7.3
Stable tag: 1.3.12
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

You can [view all of our add-ons](http://wp-property-hive.com/add-ons/ "Property Hive Add-Ons") on our website.

**Works with any new or existing theme**

Property Hive isn't a theme. It's a platform allowing you to integrate property search into any website, new or existing. For more details on supported themes [click here](http://wp-property-hive.com/which-wordpress-themes-work-with-property-hive/) or view our [pre-built themes](https://wp-property-hive.com/property-hive-website-themes/).

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

= 1.3.12 =
* Updated a couple of default form labels to make them shorter. Done in conjunction with our new free and open-source [Honeycomb theme](http://wp-property-hive.com/honeycomb/ "Honeycomb theme")
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
* Fix to the single property actions filter in how it passed arguments. Required for the new Printable Brochures add on - http://wp-property-hive.com/addons/printable-brochures/

= 1.0.24 =
* Updates to support compatibility with the new Map Search add on - http://wp-property-hive.com/addons/map-search
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