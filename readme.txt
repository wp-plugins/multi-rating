=== Multi Rating ===
Contributors: dpowney
Donate link: http://www.danielpowney.com/donate
Tags: rating, multi-rating, post rating, star, multi, criteria, rich snippets
Requires at least: 3.0.1
Tested up to: 3.8
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A simple star rating plugin which allows visitors to rate a post based on multiple criteria and questions

== Description ==

A simple star rating plugin which allows visitors to rate a post based on multiple criteria and questions

= Features =
* Star rating is based on multuple rating criteria and questions
* Shortcodes to display the rating form, single rating results and top rating results
* API to add custom rating forms, single rating results and top rating results
* Widgets to display the rating form and top rating results
* SEO rich snippets are added to markup
* Options to apply to different post types
* Add custom weights to each multi-rating item
* Options for position of rating form before_content or after_content and rating results before_title or after_title

Here's a demo http://www.danielpowney.com/multi-rating

== Installation ==

1. Install plugin via the WordPress.org plugin directory. Unzip and place plugin folder in /wp-content/plugins/ directory for manual installation
1. Activate the plugin through the 'Plugins' menu in WordPress admin
1. Go to 'Settings' menu 'Multi Rating' option in WordPress admin

== Frequently Asked Questions ==
= How do I add a rating form into my post =

There are three ways to place a rating form into a post:
* Setting display position of rating form in plugin settings to before_content or after_content
* Inserting the '[displayRatingForm]' shorcode into your post. You can use a different post ID by adding parameter post_id i.e. '[displayRatingForm post_id=10]' for post ID 10
* Modifying theme code to call API function 'display_rating_form()'. This function must be called with the loop or the post ID must be provided as a parameter i.e. 'echo display_rating_form(array('post_id' => 10)' for post ID 10.

= How do I add a rating results into my post =

There are three ways to place the rating results into a post:
* Setting display position of rating results in plugin settings to before_title or after_title
* Inserting the '[displayRatingResult]' shorcode into your post. You can use a different post ID by adding parameter post_id i.e. '[displayRatingResult post_id=10]' for post ID 10
* Modifying theme code to call API function 'display_rating_result()'. This function must be called with the loop or the post ID must be provided as a parameter i.e. 'echo display_rating_result(array('post_id' => 10))' for post ID 10.

= How do I display the top rating results? =

There are three ways to display top rating results:
* Add the top rating results widget to a widget area in your theme
* Insert the '[displayRatingTopResults]' shortcode into your post. By default, the top 10 rating results are returned but you can customise this by passing in a parameter for the count i.e. '[displayRatingTopResults count=10]'
* Modifying theme code to call API function echo 'display_rating_top_results()'. By default, the top 10 rating results are returned but you can customise this by passing in a parameter for the count i.e.' echo display_rating_top_results(array('count' => 20))' for 20 top rating results. 

= How is the rating calculated? =

Each rating item currently carries an equal weight. Each rating item is given a rating out of 5 and then the average is calculated to produce the overall rating result.

= What happens if I delete a rating item even though visitors have submitted a rating previously with this rating item? =

All prior rating entry results will include the deleted rating item in their rating result. The rating item is not included in any new rating entry results.

= How can I change the style of the rating form or rating results? =

You can change the style by adding CSS in your theme. There is a custom CSS option in the plugin settings page.

= Can I prevent visitors from submitting the same rating forms multiple times for the same post? =

Yes, there is an option to prevent visitors this in the plugin settings page. Check the rating form IP address date time validation option. This will prevent a visitor from submitting a rating form multiple times within a 24 hour period. The visitors IP address is used to identify them.

= Are the rating form and rating results responsive? =

Yes they are OK in a responsive web design except the star rating image is 130px fixed width. The rating results text wrap onto the next line and the rating form is a HTML table.

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

== Screenshots ==
1. Blog post with star rating results after_title. You can change the position of the star rating results to before_title, add a shortcode [displayRatingResult] or add function call display_rating_results() in theme PHP code
2. Default rating form. You can customise the CSS in the plugin options
3. Page showing star rating results after post title, rating form after_content and top rating results widget in sidebar. You can customise the position of the rating form to before_content, after_content, add a shortcode [displayRatingForm] or add function call display_rating_form() in thme PHP code
4. Plugin settings 1
5. Plugin settings 2
6. Plugin settings 3

== Changelog ==

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