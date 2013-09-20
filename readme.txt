=== Multi rating ===
Contributors: dpowney
Donate link: http://www.danielpowney.com/donate
Tags: rating, multi-rating, post rating, star, multi, criteria, rich snippets
Requires at least: 3.0.1
Tested up to: 3.6
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
* Options for position of rating form before_content or after_content and rating results before_title or after_title

Here's a demo http://www.danielpowney.com/multi-rating

== Installation ==

1. Install plugin via the WordPress.org plugin directory. Unzip and place plugin folder in /wp-content/plugins/ directory for manual installation
1. Activate the plugin through the 'Plugins' menu in WordPress admin
1. Go to 'Settings' menu 'Multi Rating' option in WordPress admin

== Frequently Asked Questions ==
*How do I add a rating form into my post*

There are three ways to place a rating form into a post:
* Setting display position of rating form in plugin settings to before_content or after_content
* Inserting the [displayRatingForm] shorcode into your post
* Modifying theme code to call API function display_rating_form()

*How do I add a rating results into my post*

There are three ways to place the rating results into a post:
* Setting display position of rating results in plugin settings to before_title or after_title
* Inserting the [displayRatingResults] shorcode into your post
* Modifying theme code to call API function display_rating_results()

*How is the rating calculated?*

Each rating item currently carries an equal weight. Each rating item is given a rating out of 5 and then the average is calculated to produce the overall rating result.

*What happens if I delete a rating item even though visitors have submitted a rating previously with this rating item?*

All prior rating entry results will include the deleted rating item in their rating result. The rating item is not included in any new rating entry results.

*How can I change the style of the rating form or rating results?*

There is a custom CSS option in the plugin settings page

*Can I prevent visitors from submitting the same rating forms multiple times for the same post?*

Yes, there is an option to prevent visitors this in the plugin settings page. Check the rating form IP address date time validation option. This will prevent a visitor from submitting a rating form multiple times within a 24 hour period. The visitors IP address is used to identify them.

*Are the rating form and rating results responsive?*

Yes they are OK in a responsive web design except the star rating image is 130px fixed width. The rating results text wrap onto the next line and the rating form is a HTML table.

== Screenshots ==
1. Blog post with star rating results after_title. You can change the position of the star rating results to before_title, add a shortcode [displayRatingResults] or add function call display_rating_results() in theme PHP code
2. Default rating form. You can customise the CSS in the plugin options
3. Page showing star rating results after post title, rating form after_content and top rating results widget in sidebar. You can customise the position of the rating form to before_content, after_content, add a shortcode [displayRatingForm] or add function call display_rating_form() in thme PHP code
4. Plugin settings 1
5. Plugin settings 2
6. Plugin settings 3

== Changelog ==

= 1.0 =
* Initial release