=== Meta-OGraphr ===
Contributors: yathosho
Donate link: http://example.com/
Tags: opengraph, metatag, metatags, facebook, google+, thumbnail, thumbnails, preview, previews, image, images, soundcloud, mixcloud, bandcamp, vimeo, youtube, dailymotion
Requires at least: 3.0
Tested up to: 3.3.1
Stable tag: 0.1.2

This retrieves the artwork of embedded Vimeo, YouTube, Dailymotion SoundCloud, Mixcloud, Bandcamp player widget and puts them in the meta-tags.

== Description ==

This plugin adds several OpenGraph meta-tags to the header of your theme. These include site name, a description (the excerpt of a post), the permalink, and images for embedded media widgets. 

Currently, these widgets are supported:

*  SoundCloud (HTML5 and Flash), single tracks and albums
*  Mixcloud (APIv1 and APIv2 players)
*  Bandcamp, single tracks and albums - requires valid Bandcamp API key!
*  Vimeo (embed and iframe players)
*  YouTube (embed and iframe players)
*  DailyMotion

== Installation ==

1. Upload the folder `meta-ographr` with all its contents to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Review the preferences to make use of all features

== Frequently Asked Questions ==

= Why does Facebook not display the cover artwork? =

Facebook caches previously submitted links for an undisclosed time. If your post has been shared/liked on Facebook before, the cover artwork will not appear until the cache has expired. To make sure the plugin is active and working, you can always look for the og:image tags in the source of your post.

= Do I need a SoundCloud API key? =

No. By default this plugin uses its own registered API key, but if you prefer -for whatever reason- to use your own,
you can easily do so.

= Why do I need a Bandcamp API key? =

Bandcamp is rather restrictive with access to their API, usually only allowing access to owners of material hosted on
their platform. In order to get an API key, you have to apply via email.

= Why is the artwork of a Bandcamp track not displayed? =

As of now, this plugin will only retrieve the artwork assigned to a track. Many tracks are part of an album where no
artwork has been assigned to its individual tracks. Support for this is planned for a future version.

== Screenshots ==

1. a link with a Mixcloud widget added as Facebook status update

2. a link with a SoundCloud widget added as Google+ status update

== Changelog ==

= 0.1.2 =
* added a filter for Wordpress smilies
* fixed bug not adding all images displayed in a post

= 0.1.1 =
* first public release
* fixed plugin naming inconsistencies

= 0.1 =
* first release

