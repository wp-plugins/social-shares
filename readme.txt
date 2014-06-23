=== Social Shares ===
Contributors: Waterloo Plugins
Tags: social, share, facebook, twitter, like, tweet, facebook share, twitter share, facebook like, twitter tweet, social media, social network, social share, post
Requires at least: 3.0
Tested up to: 3.9.1
Stable tag: 1.0.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Get the number of Likes and Twitter Tweets for each post. Sort your posts by share count or display the share count.

== Description ==

Social Shares uses the Facebook and Twitter APIs to fetch the total number of shares for each of your posts. The number of shares is stored in your database for any plugins to use. Social Shares can optionally display the number of shares on your posts. The number of shares is styleable. You can use the shortcode `[social_shares]` anywhere in your posts.

In addition, you can sort your posts by the number of shares. You can make a page that displays your posts with the most shares. Simply add `?sort_shares=desc` or `?sort_shares=asc` to the end of any WordPress URL to sort by the number of shares.

== Installation ==

This section describes how to install the plugin and get it working.

= Using The WordPress Dashboard =

1. Navigate to the 'Add New' in the plugins dashboard
2. Search for 'social_shares'
3. Click 'Install Now'
4. Activate the plugin on the Plugin dashboard

= Uploading in WordPress Dashboard =

1. Navigate to the 'Add New' in the plugins dashboard
2. Navigate to the 'Upload' area
3. Select `social_shares.zip` from your computer
4. Click 'Install Now'
5. Activate the plugin in the Plugin dashboard


== Frequently Asked Questions ==

= Will you add other services? =

Yes. I plan on adding Google+, LinkedIn, Reddit, Digg, and possibly other services to get shares from.

= Will this slow down my site? =

No. When users visit your site, the plugin fetches the shares from your database. Unlike other social button plugins, this plugin does not need to load buttons from other websites.
Also, to fetch the share count, this plugin fetches shares periodically in the background and stores it in your database. 

== Screenshots ==

1. Options page.

== Changelog ==

= 1.0 =
* Initial release

= 1.0.1 =
* Added shortcode
* Improved readme