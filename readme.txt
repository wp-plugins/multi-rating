=== Multi Rating ===
Contributors: dpowney
Donate link: http://www.danielpowney.com/donate
Tags: rating, multi-rating, post rating, star, multi, criteria, rich snippets
Requires at least: 3.0.1
Tested up to: 3.8
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

The simplest star rating plugin which allows visitors to rate a post based on multiple criteria and questions

== Description ==

The simplest star rating plugin which allows visitors to rate a post based on multiple criteria and questions

= Features =

* Star rating is based on multuple rating criteria and questions
* Shortcodes to display the rating form, single rating results and top rating results
* API functions for using Multi Rating in your theme
* View the rating results and entry values from the WP-admin
* Widgets to display the top rating results
* SEO rich snippets are added to markup
* Options to apply to different post types
* Add custom weights to each multi-rating item
* Position settings for the placement of the rating form before_content or after_contens and also rating results before_title or after_title
* Settings to modify text, apply different styles, clear database etc...

Here's a demo http://www.danielpowney.com/multi-rating

= Shortcode Examples =

* '[display_rating_form]'
* '[display_rating_form post_id="100" title="My rating form" submit_button_text="Submit"]'
* '[display_rating_result]'
* '[display_rating_result post_id="100" no_rating_results_text="No rating result yet"]'
* '[display_top_rating_results]'
* '[display_top_rating_results title="Top Rating Results" count="10"]'

= API Functions =

* 'get_rating_items( $post_type? )'
* 'get_rating_items_by_rating_item_entry( $rating_item_entry_id )'
* 'get_total_weight( $rating_items )'
* 'calculate_rating_result( $post_id, $rating_items )'
* 'calculate_rating_item_entry_result( $rating_item_entry_id, $rating_items? )'
* 'get_top_rating_results( $count )'
* 'display_rating_form( $post_id? , $title? , $before_title? , $after_title? , $submit_button_text? )'
* 'display_rating_result( $post_id?, $no_rating_results_text?  )'
* 'display_top_rating_results( $count?, $title?, $before_title?, $after_title? )'

where ? = optional

= Settings =

There following settings are available:

* Select the post types that are applicable for multi rating
* Choose to automatically display the rating form before or after the post content for all selected post types
* Choose to automatically display the rating result before or after the post title for all selected post types
* Change the default text and messages
* Restrict the same IP address from submitting a rating form within 24 hours for the same post
* Custom styles and star height
* Clear all rating results

= Rating Results =
You can view the details of all rating results and entry values in the WP admin including:
* Rating result entry details including the post id, entry date, IP address, WP username, rating result and shortcode
* Rating Result entry values: Each rating item value selected as a part of the rating result entry along with the description and max option value for each rating item

= Weights =
By default, each rating item has a weight of 1.0. You can modify the weights of each rating item to adjust the rating results.


== Installation ==

1. Install plugin via the WordPress.org plugin directory. Unzip and place plugin folder in /wp-content/plugins/ directory for manual installation
1. Activate the plugin through the 'Plugins' menu in WordPress admin
1. Go to 'Settings' menu 'Multi Rating' option in WordPress admin

== Frequently Asked Questions ==

= What is the algorithm for weighted rating? =

Each multi rating is adjusted based on the weight.

Let V = value of multi-rating

M = max rating value for multi-rating item

W = weight for multi-rating item

C = count or multi-rating items

TW = total weights of multi-rating items

First we figure out the adjustment percentage we need to make based on the current multi-rating weight and the total multi-rating weights

A = adjustment percentage = (W / TW) * C

i.e. a count of 3 and a total weight of 4 (2, 1 and 1 for each multi-rating item)

this will create the following adjustment:

(2 / 4) * 3 = 1.5

(1 / 4) * 3 = 0.75

So if we add up the adjustments it equals the the count 1.5 + 0.75 + 0.75 = 3. If all the weights are the same, it will still equal the count.

This adjustment is then multiplied to the overall rating result = (V / M) * A

= How do I add a rating form into my post =

There are three ways to place a rating form into a post:

* Setting display position of rating form in plugin settings to before_content or after_content
* Inserting the '[display_rating_form]' shorcode into your post. You can use a different post ID by adding parameter post_id i.e. '[display_rating_form post_id="10" title="My rating form"]' for post ID 10. If you do not set a post_id, the current post ID from the WP loop is used.
* Modifying theme code to call API function 'Multi_Rating_API::display_rating_form()'. This function must be called with the loop or the post ID must be provided as a parameter i.e. 'Multi_Rating_API::display_rating_form(array('post_id' => "10", 'title' => 'My rating form')' for post ID 10. If you do not set a post_id, the current post ID from the WP loop is used.

= How do I add a rating results into my post =

There are three ways to place the rating results into a post:

* Setting display position of rating results in plugin settings to before_title or after_title
* Inserting the '[display_Rating_result]' shorcode into your post. You can use a different post ID by adding parameter post_id i.e. '[display_Rating_result post_id="10"]' for post ID 10
* Modifying theme code to call API function 'Multi_Rating_API::display_rating_result()'. This function must be called with the loop or the post ID must be provided as a parameter i.e. 'Multi_Rating_API::display_rating_result(array('post_id' => '10'))' for post ID 10.

= How do I display the top rating results? =

There are three ways to display the top rating results:

* Add the top rating results widget to a widget area in your theme
* Insert the '[display_top_rating_results]' shortcode into your post. By default, the top 10 rating results are returned but you can customise this by passing in a parameter for the count i.e. '[display_top_rating_results count="10" title="My top rating results"]'
* Modifying theme code to call API function echo 'Multi_Rating_API::display_rating_top_results()'. By default, the top 10 rating results are returned but you can customise this by passing in a parameter for the count i.e. 'Multi_Rating_API::display_top_rating_results(array('count' => '20', 'title => 'My top rating results'))' for 20 top rating results.

= How is the rating calculated? =

Each rating item currently carries an equal weight. Each rating item is given a rating out of 5 and then the average is calculated to produce the overall rating result.

= What happens if I delete a rating item even though visitors have submitted a rating previously with this rating item? =

All prior rating entry results will include the deleted rating item in their rating result. The rating item is not included in any new rating entry results.

= How can I change the style of the rating form or rating results? =

You can change the style by adding CSS in your theme. There is a custom CSS option in the plugin settings page.

= Can I prevent visitors from submitting the same rating forms multiple times for the same post? =

Yes, there is an option to prevent visitors this in the plugin settings page. Check the rating form IP address and date validation setting. This will prevent a visitor from submitting a rating form multiple times within a 24 hour period. The visitors IP address is used to identify them.

= Are the rating form and rating results responsive? =

Yes they are OK in a responsive web design except the star rating image is 130px fixed width. The rating results text wrap onto the next line and the rating form is a HTML table.

== Screenshots ==
1. Post with star rating results after_title and rating form after_content.
2. Rating result entries
3. Rating result entry values
4. Rating items
5. Add new rating item
6. Settings 1
7. Settings 2
8. Settings 3

== Upgrade Notice ==

= 2.0 =
Major refactor of plugin. Old Shortcodes deprecated and replaced with new shortcodes. Old settings and API functions renamed and may not backward compatible. Old rating results will not be migrated. If you wish to keep your rating results, you must continue to use the version 1.1.8.

== Changelog ==

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