=== PropertyHive ===
Contributors: PropertyHive,BIOSTALL
Tags: property, real estate, software, estate agents, estate agent, property management, propertyhive, property hive, properties, property plugin, estate agent plugin
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=N68UHATHAEDLN&lc=GB&item_name=BIOSTALL&no_note=0&cn=Add%20special%20instructions%20to%20the%20seller%3a&no_shipping=1&currency_code=GBP&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted
Requires at least: 3.8
Tested up to: 4.5.2
Stable tag: 1.1.0
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Property Hive is estate agency software for WP. Market properties on your website, manage contacts and applicants, or expand the features through add-ons

== Description ==

Property Hive is the first plugin that aims to bring all of the features you'd normally find in estate agency software into WordPress.

From managing residential and commercial properties, owners and landlords, to tracking applicants and matching them to suitable properties, we want to do it all.

= Why Property Hive? =

There are many estate agency software options available on the market, but here's why we think you'll love Property Hive:

**Only use the features your business needs**
By default Property Hive is a property and contact management tool, allowing you to manage your property stock, list properties on your website, record owners details, and store applicants requirements. Through use of premium add-ons however, you can choose which features you want to bolt on. The add-ons fit seamlessly into the existing plugin, and with each other.
 
Want to send your properties to property portals? There's an add-on for that.

Our add-ons are priced individually from just £24.99 meaning you only pay for what you use.

You can [view all of our add-ons](http://wp-property-hive.com/add-ons/ "Property Hive Add-Ons") on our website.

**Works with any new or existing theme**
Property Hive isn't a theme. It's a platform allowing you integrate property search into any website, new or existing. For more details on supported themes [click here](http://wp-property-hive.com/which-wordpress-themes-work-with-property-hive/) or view our [pre-built themes](https://wp-property-hive.com/property-hive-website-themes/).

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

In the search field type “Property Hive” and click Search Plugins. Once you’ve found our eCommerce plugin you can view details about it such as the the point release, rating and description. Most importantly of course, you can install it by simply clicking “Install Now”.

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
12. View a list of properties that match the applicant's requirements.
13. Enquiries made on the site are available from within WordPress. Assign them to a negotiator and mark them as 'Closed' once complete.
14. The 'Settings' section gives you control over which departments are active, add and edit offices, and edit the custom fields (types, locations etc) that appear within your install

== Changelog ==

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