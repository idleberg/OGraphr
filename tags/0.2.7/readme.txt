=== OGraphr ===
Contributors: yathosho
Tags: opengraph, metatag, metatags, facebook, google+, thumbnail, thumbnails, preview, previews, image, images, soundcloud, mixcloud, bandcamp, vimeo, youtube, dailymotion
Requires at least: 3.0
Tested up to: 3.3.1
Stable tag: 0.2.8

This retrieves the artwork of embedded Vimeo, YouTube, Dailymotion, SoundCloud, Mixcloud, Bandcamp player widget and puts them in the meta-tags.

== Description ==

This plugin adds several OpenGraph meta-tags to the header of your theme. These include site name, a description (the excerpt of a post), the permalink, and images for embedded media widgets. The images will be retrieved from the audio player embedded in your post ("cover artwork") or snapshots from embedded videos.

Currently, these widgets are supported:

*  SoundCloud (HTML5 and Flash), single tracks and albums
*  Mixcloud (APIv2 players)
*  Bandcamp, single tracks and albums - requires valid Bandcamp API key!
*  Vimeo (embed and iframe players)
*  YouTube (embed and iframe players)
*  DailyMotion

OpenGraph tags will be used by social-media sites such as Facebook or Google+ to style a shared link or webpages "liked" by any user. As images attract more attention, you will hopeful find this plug-in useful.

== Installation ==

1. Upload the folder `meta-ographr` with all its contents to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Review the preferences to make use of all features

== Frequently Asked Questions ==

= Why doesn't Facebook display the cover artwork? =

Facebook caches previously submitted links for an undisclosed time. If your post has been shared/liked on Facebook before, the cover artwork will not appear until the cache has expired. To make sure the plugin is active and working, you can always look for the og:image tags in the source of your post.

= I don't use social media, why would I use this? =

People share links with their friends on social media sites whether you like it or not. This plug-in gives you some control over how your content is presented on platforms such as Facebook and Google+. Displaying cover artwork or video snapshots with your link usually looks nicer and attracts the attention of potential visitors.

= Do I need a SoundCloud API key? =

No. By default this plug-in uses its own registered API key, but if you prefer -for whatever reason- to use your own,
you can easily do so.

= Why do I need a Bandcamp API key? =

Bandcamp is rather restrictive with access to their API, usually only allowing access to owners of material hosted on
their platform. In order to get an API key, you have to apply via email.

== Screenshots ==

1. a link with a Mixcloud widget added as Facebook status update

2. a link with a SoundCloud widget added as Google+ status update


== Changelog ==

= 0.2.7 =
* akternative bandcamp detection set to default
* fixed bug when no website thumbnail is specified

= 0.2.7 =
* added option to show default thumbnail conditionally
* β: added alternative method to get bandcamp artwork for tracks (must enable in source)
* modified plugin URL
* fixed bug adding empty value to array
* fixed possible bug when no images available

= 0.2.6 =
* added validating function for facebook options
* modified options page

= 0.2.5 =
* added more advanced Facebook-specific options
* added %siteurl% placeholder for description and site name
* modified behaviour of trigger checkboxes
* fixed behaviour on front page
* displays shortened url in comment

= 0.2.4 =
* added Facebook-specific options
* removed function deleting 0.1 settings
* restyled options page

= 0.2.3 =
* added color indicator when site title is missing
* added color indicator when tagline is missing
* added checkboxes to enable/disable triggers
* adjusted default image sizes for Vimeo (large->medium)
* improved support for Viper's Video Quicktags

= 0.2.2 =
* added hardcoded option to set Bandcamp imagesize
* added option to display plug-in name and version in HTML source
* fixed permalink on front page

= 0.2.1 =
* wrapped functions inside class
* added option to use tagline as custom description
* added rudimentary support for Viper's Video Quicktags
* added support for SoundCloud shortcodes
* compacted detection code
* improved Dailymotion detection
* fixed disabling 'Add permalink'
* fixed bug retrieving website thumbnail
* fixed typo in debugger output
* fixed typo in the SoundCloud API label
* fixed typo in the 'Add excerpt' label

= 0.2 =

* reworked option page

= 0.1.4 =
* limited plugin functionality to posts only (option to follow in future)	
* added default image-size definitions for developers
* added debug option
* fixed detection for embedded images
* fixed bug in Mixcloud widget detection

= 0.1.3 =
* added option to format title
* modified style of save button

= 0.1.2 =
* added a filter for Wordpress smilies
* fixed bug not adding all images displayed in a post

= 0.1.1 =
* first public release
* fixed plugin naming inconsistencies

= 0.1 =
* first release

== Upgrade Notice ==

= 0.2.3 =
* activate triggers after upgrading

= 0.2 =
Please adjust your settings after upgrading