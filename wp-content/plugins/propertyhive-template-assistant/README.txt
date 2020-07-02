=== PropertyHive Template Assistant ===
Contributors: PropertyHive,BIOSTALL
Tags: property hive, propertyhive
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=N68UHATHAEDLN&lc=GB&item_name=BIOSTALL&no_note=0&cn=Add%20special%20instructions%20to%20the%20seller%3a&no_shipping=1&currency_code=GBP&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted
Requires at least: 3.8
Tested up to: 5.4.1
Stable tag: trunk
Version: 1.0.34
Homepage: http://wp-property-hive.com/addons/template-assistant/

This add on for Property Hive assists with the layout of property pages and more.

== Description ==

This add on for Property Hive assists with the layout of property search page, the fields shown on search forms and allows you to manage custom fields on the property record.

== Installation ==

= Manual installation =

The manual installation method involves downloading the Property Hive Template Assistant Add-on plugin and uploading it to your webserver via your favourite FTP application. The WordPress codex contains [instructions on how to do this here](http://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation).

Once installed and activated, you can access the settings for this add on by navigating to 'Property Hive > Settings > Template Assistant' from within WordPress.

= Updating =

Updating should work like a charm; as always though, ensure you backup your site just in case.

== Changelog ==

= 1.0.34 =
* Added new price, rent and bedroom slider fields as options when building search forms

= 1.0.33 =
* Added ability to specify property custom field as match field on applicants. This field will appear on property, applicant requirements, and impact properties returned in matches
* Hide/show additional fields tickboxes based on meta box chosen
* Added missing text domain to a few labels

= 1.0.32 =
* Added ability to re-order additional fields
* Added ability to specify additional fields should appear on registration form and user details
* Resizing of images in grid change to ensure right <li> is targeted
* Declare support for WordPress 5.4.1

= 1.0.31 =
* Allowed for office dropdown in search forms to also be multi-select following last release which only applied to taxonomies

= 1.0.30 =
* Added option to set search form controls such as location and property type as new multi-select control type

= 1.0.29 =
* Added setting to apply search results CSS to all pages so layout chosen effects shortcodes etc throughout site

= 1.0.28 =
* Added 'Top Level Terms Only' option when specifying fields such as property type and location in search forms
* Correction to past release relating to passing custom fields as attributes and making it work when custom field is of type multi-select
* Declare support for WordPress 5.3.2

= 1.0.27 =
* Added ability to pass any additional fields setup as attributes to property related shortcodes
* Declare support for WordPress 5.3

= 1.0.26 =
* If additional fields exist assigned to the 'Room Details' section exist (when the Rooms add on is active) and 'Display on Website' is ticked, ensure these fields show accordingly
* Declare support for WordPress 5.2.4

= 1.0.25 =
* Display radius as an option when customising search form fields, even if the Radial Search add on isn't active and providing a link to it.

= 1.0.24 =
* Added new filters to give third party plugins more control over the showing and saving of additional fields. In this scenario it was done for our rooms add on where each individual room can now have it's own additional fields.
* Declare support for WordPress 5.2.3

= 1.0.23 =
* Rename 'Custom Fields' to 'Additional Fields' to avoid confusion with the existing custom fields like property type and availability
* Added more sections to choose from when adding additional fields so you can now add these fields to viewings, offers etc.
* Added a new filter 'propertyhive_template_assistant_custom_field_sections' allowing third party plugins to add more sections that additional fields can be added to

= 1.0.22 =
* Removed hardcoded departments when building search forms and instead use new ph_get_departments() function for when third party plugins (such as the new student accommodation add on) ad their own department
* Declare support for WordPress 5.2.2

= 1.0.21 =
* Extended 'Flags' settings allowing you to choose whether flags should also appear over images on single property details page
* Declare support for WordPress 5.2.1

= 1.0.20 =
* Optimised the Text Substitution feature
* Declare support for WordPress 5.1

= 1.0.19 =
* Added commercial tenure and commercial for sale/to rent fields to list of available fields when building search forms
* Make radial search dropdown options translatable
* Also run JS to set image heights on window load
* Declare support for WordPress 5.0.3

= 1.0.18 =
* Added new settings area relating to flags, including whether they are enabled, colours etc
* Added new setting to change the default sort order of properties on search results
* Added new 'Text Substitution' area for changing labels/words
* Added ability to choose and reorder fields shown on search results
* Added ability to reorder custom field dropdown options
* Added price/rent range fields to list of available fields in search forms
* Added delete confirmations before deleting search forms and custom fields
* Declare support for WordPress 4.9.8

= 1.0.17 =
* Added ability to create custom fields of type 'Date'
* Added new option to specify whether custom fields should appear in admin lists
* Added new option to specify whether custom fields in admin lists should be sortable

= 1.0.16 =
* Added ability to add custom fields to contacts
* Declare support for WordPress 4.9.6

= 1.0.15 =
* Allow individual options to be reordered when setting form dropdown options
* Allow options to be added to department field when editing search forms
* Register template-assistant.js globally so can be enqueued at any time
* Correct typos in field names for max bed, min baths and max baths
* Declare support for WordPress 4.9.5

= 1.0.14 =
* Added support for custom fields of type multi-select
* Declare support for WordPress 4.8.3

= 1.0.13 =
* Corrected issue when filtering properties by custom fields added to search forms
* Enqueue jquery-ui-sortable for use in search form builder page

= 1.0.12 =
* Added maximum bedrooms field to the list of fields available to choose from in search form builder
* Declare support for WordPress 4.8.1

= 1.0.11 =
* Ensure that when the active departments are updated in settings that any department fields in search forms are updated accordingly

= 1.0.10 =
* Added ability to enter a 'Blank Option' when adding taxonomy field or custom field to search forms. Sets the first option in the dropdown and defaults to 'No Preference'

= 1.0.9 =
* When adding custom fields you can now choose the type of field; text, textarea or dropdown. Choosing dropdown you can then customise the options
* Added the ability to add any custom fields to the search form
* Added the ability to add placeholder to text inputs in search form builder
* Declare support for WordPress 4.8

= 1.0.8 =
* Added new 'Office' field to search form fields
* Added new 'Bedrooms' field to search form fields. This does an exact match on number of beds as opposed to min/max
* Tweaked CSS regarding column layouts in search results
* Declare support for WordPress 4.7.5

= 1.0.7 =
* Choose if custom fields should be displayed on the website. Any chosen will be appended to the bullet points in the single-property/meta.php template.

= 1.0.6 =
* Added ability to add and manage custom fields on property record

= 1.0.5 =
* Added new 'Available From' field available for selection when building search forms
* Declare support for WordPress 4.7.3

= 1.0.4 =
* Allow changing of type of department control between select and radio
* Tweaks to default search results CSS loaded

= 1.0.3 =
* Added new min/max bathrooms to list of selectable control in search form builder
* Corrected some of the classes being used on controls in search form builder
* Declare support for WordPress 4.7.2

= 1.0.2 =
* Added delete and reset options to search forms
* Corrected issue where field type was sometimes being saved as blank

= 1.0.1 =
* Added a new search form builder allowing customisation of search forms through a settings UI as opposed to having to know about PHP hooks
* Declare support for WordPress 4.7.1

= 1.0.0 =
* First working release of the add on. Contains assistance with search results page only