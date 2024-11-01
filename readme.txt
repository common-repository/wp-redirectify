 === Redirectify ===
Contributors: philiprabbett
Tags: jquery, redirectify, redirection, permalink, redirect
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=EC24Z9TS85QSG
Requires at least: 3.5
Tested up to: 4.8
Stable tag: 2.2.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A plugin that redirects posts and pages to specified url.

== Description ==

This plugin will redirect visitors to an alternative post / page or external URL when they attempt to view the specific post / page. The redirect will only happen when the post / page is viewed by itself, post content and other post data such as excerpts will still appear on the homepage and archives. This is an easy way to give permalinks to pages that are outside the control of WordPress and integrate them into your site structure.

This plugin adds a custom form field just above the submit box (where the publish button is shown), the value of this field should be the URL to redirect when that page is viewed.

Can we have an example?

Say you already have an external gallery not controlled by WordPress and you want to include a link to it using the page functionality of WordPress. By using this Plugin, the page redirecting to the external Gallery will appear in your page menu (wherever that is on your site, typically in your sidebar) and when a user clicks it (accesses the permalink in the address bar) they will be redirected to your gallery, wherever that happens to be.

== Installation ==
1. Upload the `wp-redirectify` folder and all contents to `/wp-content/plugins`
2. Activate the plugin through the 'Plugins' menu in WordPress

== Screenshots ==

1. Screenshot of the Publish Meta box (Redirection Disabled)
2. Screenshot of the Publish Meta box input field (Blank)
3. Screenshot of the Publish Meta box (Redirection Enabled)
4. Screenshot of the Publish Meta box input field (Filled In)

== Frequently Asked Questions ==
= How to redirect Posts / Pages? =

1. Open the post for editing
2. Click Edit in the publish module beside `Redirection`
3. Enter a full URL and click OK
4. Click Update Post to save changes
5. You should be good to go

== Changelog ==
= 2.2.1 =
* Minor fix to the save function

= 2.2 =
* Minor fix to post state display.

= 2.1 =
* Added option to use redirect url as permalink or use standard url. You can access it via your Settings -> Reading page.
* Two new filters for permalink filtering:
 1. *redirectify_filter* - filter to disable url display
 2. *redirectify_permalink_filter* - filter to input additional permalink filters
* Tidied up some code.

= 2.0 =
* Complete re-write.

== Upgrade Notice ==
= 2.2.1 =
Fixed PHP notice issue that appears when first creating new posts.

= 2.2 =
Fixed post state notifying what posts are redirection posts.

= 2.1 =
Added a new option to display the redirect url instead of the normal url including two new filters to assist this new function.

= 2.0 =
Completely re-write.