=== SimpleModal Janrain Engage ===
Contributors: PerS
Donate link: http://soderlind.no/donate/
Tags: simplemodal login, janrain engage, rpx, modal, login, authentication,  facebook connect,  google, linkedin,  myspace, oauth, openid,  twitter, windows live, yahoo, wp-symposium
Requires at least: 2.8.6
Tested up to: 3.0.4
Stable tag: trunk

SimpleModal Janrain Engage adds Janrain Engage (aka rpx) to your SimpleModal Login

== Description ==

SimpleModal Janrain Engage adds Janrain Engage (aka rpx) to your SimpleModal Login. The [Janrain Engage](http://wordpress.org/extend/plugins/rpx/) and [SimpleModal Login](http://wordpress.org/extend/plugins/simplemodal-login/) plugins must be installed and working.

The Janrain Engage embedded widget support several languages. The SimpleModal Janrain Engange plugin will try to set the language for the embedded Janrain Engage widget based on your locale. If the plugin doesn't find a match, it will use the fallback language set by you in the plugin settings page.

For more information and larger screenshots, please see the [plugin home page](http://soderlind.no/archives/2010/12/03/simplemodal-janrain-engage/)

== Installation ==

= Requirement =
* PHP: 5.2.x or newer
* [SimpleModal Login](http://wordpress.org/extend/plugins/simplemodal-login/)
* [Janrain Engage](http://wordpress.org/extend/plugins/rpx/)

= Manual Installation =
* Upload the files to wp-content/plugins/simplemodal-janrain-engage/
* Activate the plugin

= Automatic Installation =
* On your WordPress blog, open the Dashboard
* Go to Plugins->Install New
* Search for "SimpleModal Janrain Engage"
* Click on install to install SimpleModal Janrain Engage

= Configuration = 
In Settings -> SimpleModal Janrain Engage, set the fallback language for the Janrain Engage widget. Default is English.
Translate the login/register form, by using the included ps_simplemodal_janrain_engage.pot file in the wp-content/plugins/simplemodal-janrain-engage/languages folder

== Screenshots ==

1. Login
2. Register
3. Welcome back
4. Integrated with SI Captcha

== Changelog ==
= 1.2.9 =
Bugfix, fixed bad path to language file. Many thanks to vinoowijn1 for pointing out this bug.
= 1.2.8 =
Bugfix (removed the spinner/loading icon)
= 1.2.7 =
Fixed bug that prevented using LinkedIn and Twitter as a identity provider. My bad, many thanks to mattp and Robert for pointing out this bug.
= 1.2.5 =
Added "set modal width" in the settings page + minor bug fixes
= 1.2.0 =
I should have read the Janrain Engage doc a litle better, discovered a paramenter for the inline widget and "had" to rewrite the plugin. Now you can change the heading above the Janrain Engage widget using the ps_simplemodal_janrain_engage.pot file
= 1.1.1 =
Minor style adjustment
= 1.1.0 = 
Added language support for the Janrain Engange embedded widget and updated the ps_simplemodal_janrain_engage.pot file
= 1.0.0 = 
* initial release