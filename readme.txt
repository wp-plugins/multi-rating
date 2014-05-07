=== Multi Rating ===
Contributors: dpowney
Donate link: http://www.danielpowney.com/donate
Tags: rating, multi-rating, post rating, star, multi, criteria, rich snippet, testimonial, review
Requires at least: 3.0.1
Tested up to: 3.9
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Multi Rating is a simple rating plugin which allows visitors to rate a post based on multiple criteria and questions.

== Description ==

I developed Multi Rating because I could not find a single multi rating plugin for WordPress that a) worked, b) was simple and easy to use, and c) had the right features. GD Star Rating was far too complicated and the other rating plugins were either too basic or did not work at all.

Multi Rating is a simple rating plugin which allows visitors to rate a post based on multiple criteria and questions. It is responsive, easy to use and integrates seamlessly into any WordPress theme.

Here's a live demo: http://danielpowney.com/multi-rating/

= Features =

* 5 star rating, percentage and score results based on multuple rating criteria and questions
* Shortcodes to display the rating form, rating results and top rating results
* Rich snippet added to markup for 5 star rating results
* View the rating results and entry values from the WP-admin
* API functions for using Multi Rating in your theme
* Widget to display the top rating results
* Options to apply to different post types
* Add custom weights to each rating item to adjust the overall rating results
* Automatic placement settings to display the rating form and rating results on every post in different positions
* Meta box on the edit post page to override the default automatic placement settings
* Settings to restrict post types, turn on validation, modify text, apply different styles and clear the database etc...

= Shortcodes =
* [display_rating_form]
* [display_rating_result]
* [display_top_rating_results]

e.g. [display_rating_form post_id="100" title="My Rating Form" submit_button_text="Submit" class="my-css-class" before_title="" after_title""]

= API Functions =
* display_top_rating_results
* display_rating_result
* display_rating_form

= Multi Rating Pro =

Check it out here: http://danielpowney.com/downloads/multi-rating-pro/

The following key features are available in the Pro version:

* Multiple rating forms with different rating items
* Logged in users can update and delete their existing ratings
* New shortcodes, API functions and widgets for displaying rating results in a review format, displaying individual rating item results and displaying rating results belonging to a specific user
* Rating forms can optionally include a name, e-mail and comment fields
* Ability to use text descriptions for select options instead of numbers
* View rating results per post and rating form in WP-admin backend
* Post, category and specific page filters to include (whitelist) or exclude (blacklist) automatic placement of the rating form and rating results
* Options to exclude the home page and archive pages (i.e. Category, Tag, Author or a Date based pages)

Other features available in the Pro version include:

* Allow/disallow anonymous user ratings option
* Change the defaults settings for each post in the Edit Post page including the default rating form and allow anonymous ratings option
* Option to display the rating result back to the user when they submit a rating form
* Modify the duration in days for the IP address date validation check for users submitting the rating form
* More filters on WP-admin rating results tables
* More star rating image sprites

== Installation ==

1. Install plugin via the WordPress.org plugin directory. Unzip and place plugin folder in /wp-content/plugins/ directory for manual installation
1. Activate the plugin through the 'Plugins' menu in WordPress admin
1. Go to 'Settings' menu 'Multi Rating' option in WordPress admin

== Frequently Asked Questions ==

Documentation here: http://danielpowney.com/multi-rating/

== Screenshots ==
1. Demo of rating results after page title, rating form and top rating results
2. View rating results in WP-admin
3. Edit post page in WP-admin showing Multi Rating meta box and shortcode sample in visual editor
4. Rating items table
5. Add a new rating item
6. Top Rating Results widget
7. Rating result details including values
8. Settings page

== Upgrade Notice ==

= 2.1 =
Changes to the generated HTML and CSS styles

== Changelog ==

= 2.1 (07/05/2014) =
* Refactored HTML for rating form and rating results including CSS styles
* Added Multi Rating meta box in edit page to override default settings for automatic placements of rating form and rating results per post or page
* Refactored how rating results are returned in API
* Added more params to API functions and more attributes to shortcodes e.g. class, result_type, show_category_filter, show_rich_snippets etc...
* Added show category filter to widget

= 2.0.4 (12/04/2014) =
* Fixed rich snippets bug
* Refactored API functions and added more params that can be used in shortcodes

= 2.0.3 =
* Information on multi-rating-pro plugin features added

= 2.0.2 =
* Rating results table in WP-admin query updated to order by entry_date desc

= 2.0.1 =
* Fixed top rating results widget bug

= 2.0 =
* Major refactor of plugin.
* Old Shortcodes deprecated and replaced with new shortcodes. Old settings and API functions renamed and may not backward compatible.
* Old settings have been renamed
* Old rating result will not be migrated. If you wish to keep your rating results, you must continue to use the version 1.1.8.
* Improved WP admin including view rating result entries and values

= 1.1.8 (15/01/2014) =
* Allow removing title from rating form and top rating results

= 1.1.7 (13/01/2014) =
* Added settings for default rating form title and default top rating results title

= 1.1.6 (7/01/2014) =
* Fixed bug in displaying top results for multiple post types

= 1.1.5 (6/01/2014) =
* Fixed custom title for widgets

= 1.1.4 (19/12/2013) =
* Added support for character encoding

= 1.1.3 = (14/12/2013)
* Fixed post title on top rating results widget

= 1.1.2 = (14/12/2013)
* Removed debugging comment accidentally left behind

= 1.1.1 (12/12/2013) =
* Changed shortcode parameter for post id from id to post_id
* Fixed default values in API functions for themes
* Fixed bug which caused only 5 top rating results being displayed

= 1.1 =
* Added weight rating for multi criteria

= 1.0.3=
* Fixed activation for older versions of PHP

= 1.0.2 =
* Added option to change rating results stars to small, medium or large size
* Fixed some CSS issues

= 1.0.1 =
* Added check is_singular() to add rich snippets to rating results

= 1.0 =
* Initial release