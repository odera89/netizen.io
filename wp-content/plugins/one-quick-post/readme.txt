=== One Quick Post ===
Contributors: G.Breant
Donate link: http://dev.pellicule.org/?page_id=19#donate
Tags: Quickpress,post,quick post,frontend,custom post,geo-location,custom post type,form
Requires at least: 3.1
Tested up to: 3.3.2
Stable tag: 0.9.6.3-beta

One Quick Post is a WordPress plugin that allows you to enable frontend posting on your blog; even for custom post types.

== Description ==

One Quick Post is a WordPress plugin that allows you to enable frontend posting on your blog; even for custom post types.

**Please [make a donation](http://dev.pellicule.org/?page_id=19#donate) if you use this plugin !  It would be much appreciated.**

* [Demo Site](http://dev.pellicule.org/classified-ads)

= Features =

* Build your own submissions forms. You can split a form into several steps; and add fields for the title, description, taxonomies, meta keys, geo-location, file upload...
* Used as frontend editor to post ads with [Your Classified Ads](http://wordpress.org/extend/plugins/your-classified-ads/) plugin.
* Works with the WordPress roles & capabilities system : eg. if a user send a post; will it pend or will it published ?  Is the user allowed to edit a published post ?
* Notifications: you can enable the notifications; that will email the user when his post is pending, has been published or has been deleted.  As admin; you can write the reason of the deletion of a post in an optional box.  This would be included in the notification message.
* BuddyPress compatible.  (Soon : specific BuddyPress features)

= Premium extensions =

* Query subscribe : allows user to save custom searches, and to follow authors or particular posts.  User will be notified when posts matching the subscriptions settings are published (or edited, in the case of particular posts).
* Geo-location : adds a form field to allow the user to set the post location; and allow user to limit search to a maximum distance of a certain location.
* Expiration : makes posts expire after a certain delay; adds an "expired" class to those posts.  Ability to hide those expired posts from Wordpress and to auto-delete them after a certain delay (not yet implemented).
* Freshness : check which posts are "new" (published since X hours) : changes the regular post format to "published ... ago" (seconds,minutes,hours) for those posts.  Also check which posts are "fresh" (not seen since last visit) and storing the info in a meta key or in a cookie (for non-logged visitors).  Adds specific post classes for those new or fresh posts.
* Terms and conditions : adds a checkbox that the user has to check before creating a new post, linked to a "terms & conditions" page.

== Installation ==

1. Check you have the least required WordPress version
2. Download the plugin
3. Unzip and upload to plugins folder
4. Activate the plugin
5. Create a new page that will "host" your OQP form.
6. Open the One Quick Post settings and create a new form, using the ID of the new page.  Setup the form fields and the form extensions.

== Frequently Asked Questions ==

= How can I enable guest posting ? =
Guest posting was a previous feature of OQP.  It has been currently disabled because the system used was a little tricky.  
Meanwhile, you can enable guest posting for Facebook members using an extensions like [WP-FacebookConnect](http://wordpress.org/extend/plugins/wp-facebookconnect)


= I've got problems with the CSS styles = 
Every theme uses different styles.  OQP tries to be the cleanest possible but you probably will have to refine some of the styles.
Also; a lot of the CSS used by OQP are selected through the body class.  Check that your theme IS using the function body_class().

= How to change how pages are rendered ? =

The best idea would be to use action hooks :
* 'oqp_before_content' & 'oqp_before_content' : before / after page content
* 'oqp_before_loop' & 'oqp_after_loop' : before / after loop of posts
* 'oqp_before_loop_item' & 'oqp_after_loop_item': before / after single item is displayed in the loop
* 'oqp_before_step' & 'oqp_after_step': before / after step
* 'oqp_before_field' & 'oqp_after_field': before / after step

= How can I filter the fields content ? =
Use those filters : 'oqp_get_field' and 'oqp_get_edit_field' (for the editable field).

Inside those hook you may use those conditionnal functions :

* oqp_is_directory()
* is_oqp_form()
* oqp_is_single()


OQP uses several main templates; that you can find in /wp-content/plugins/one-quick-post/theme :
* oqp-archive.php - displays the main page with a loop of posts
* single/oqp-single.php - displays a single post
* single/oqp-form.php - displays a post in edition mode (creation/admin).

If you want to use a custom template for the archive pages, change the page template while editing the page that loads the form (check your form parameters).

You can also erase the default templates by creating a "oqp-theme" directory under wp_content/themes/YOURTHEME; using the same file structure : the plugin will
first try to load your custom template before loading the plugin one.

The other way is to add a filter on 'oqp_oqp_locate_theme_template'.

= Debug mode is not working =
http://www.firephp.org/Wiki/Reference/FAQ

== Screenshots ==

1. Directory listing
2. Single post display
3. Single post admin
4. Admin options : form fields
5. Admin options : form extensions
6. Query Subscribe extension : subscribe to a custom search.

== Changelog ==
= 0.9.6.3 =
* CSS fixes
= 0.9.6.2 =
* Added tinyMCE wysiwyg editor for text sections + media uploader (optional)
* jquery.easyListSplitter to split taxonomies in columns while editing fields
* merged stylesheets
* Fixed some bugs
* Added ability to delete an attachment / change the default thumb 
= 0.9.4 = 
* Major release !  Most of the code has been rewritten.  We just wait to fix some stuff to launch v1.0.0. !

== Support ==
[Support forums](http://dev.pellicule.org/bbpress/forum/one-click-post)

== Roadmap ==
* BuddyPress integration

== Bugs ==
* If the poster has not at least the contributor status; he is no more credited as author when the post is updated.