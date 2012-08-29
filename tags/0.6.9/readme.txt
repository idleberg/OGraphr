=== OGraphr ===
Contributors: yathosho
Tags: opengraph,open-graph,open graph,open graph meta,metatags,facebook,google+,schema,thumbnails,soundcloud,mixcloud,bandcamp,vimeo,youtube,dailymotion,blip.tv,hulu,internet archive,archive.org,myvideo,official.fm,ustream,viddler,html5,livestream video,jwplayer,flickr,justin.tv,twitch.tv,8tracks,bambuser,rdio
Requires at least: 3.0
Tested up to: 3.4.1
Stable tag: 0.6.9

Retrieves the images of audio/video player widgets in your posts and embeds them as metatags compatible with Facebook, Google+ and other social media sites.

== Description ==

This plugin adds several OpenGraph meta-tags to the header of your theme. These include site name, a description (the excerpt of a post), the permalink, and images for embedded media widgets. The images will be retrieved from the audio player embedded in your post ("cover artwork") or snapshots from embedded videos.

Currently, these widgets are supported:

*  8tracks
*  Bambuser
*  Bandcamp
*  Blip.tv
*  DailyMotion
*  Flickr videos
*  Hulu
*  Internet Archive
*  Justin.tv/Twitch.tv
*  Livestream
*  Mixcloud
*  MyVideo
*  Official.fm
*  Rdio
*  SoundCloud
*  Ustream
*  Viddler
*  Vimeo
*  YouTube
*  JWPlayer
*  Standard HTML5 video-tags

OpenGraph tags will be used by social-media sites such as Facebook or Google+ to style a shared link or webpages "liked" by any user. As images attract more attention, you should find this plug-in useful.

== Installation ==

1. Upload the folder `meta-ographr` with all its contents to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Review the preferences to make use of all features

== Frequently Asked Questions ==

= What is the Open Graph protocol? =

The [Open Graph protocol](http://ogp.me/ "Open Graph protocol") enables any web page to become a rich object in a social graph. For instance, this is used on Facebook to allow any web page to have the same functionality as any other object on Facebook. The OGraphr plugin for Wordpress detects images from popular media players and adds them, alongside other information, to the metadata of your page.

= I don't use social media, why would I use this? =

People share links with their friends on social media sites whether you like it or not. This plug-in gives you some control over how your content is presented on platforms such as Facebook and Google+. Displaying cover artwork or video snapshots with your link usually looks nicer and attracts the attention of potential visitors.

= Why doesn't Facebook display the cover artwork? =

Facebook caches previously submitted links for an undisclosed time. If your page has been shared/liked on Facebook before, the cover artwork will not appear until that cache has expired. To make sure the plugin is active and working, you can always look for the og:image tags in the source of your page.

= Why do I need a Bandcamp API key? =

Bandcamp is rather restrictive with access to their API, usually only allowing access to owners of material hosted on their platform. In order to get an API key, you have to apply via email.

= Do I really need a Viddler API key? =

You probably don't. All new Viddler players use HTML5-compliant poster images and these can be detected without making an API call. It's only old "Legacy" players rely on Viddler's API and you need a valid developer key to access it.

= What about site performance? =

Depending on the amount of embed codes in your site, retrieving images and other informations can delay the rendering of a page. This can be avoided by retrieving images only once when an article has been published or updated. You can further restrict OGraphr to trigger only when called by Facebook, Google+ or LinkedIn.

== Screenshots ==

1. a link with a Mixcloud widget added as Facebook status update

2. a link with a SoundCloud widget added as Google+ status update

3. standard settings page for OGraphr

4. advanced settings page for OGraphr

== Changelog ==

= 0.6.9 =
* added support for MyVideo and Rdio
* modified visual graph
* improved suffix removal
* fixed image-type detection

= 0.6.8 =
* added support for new Official.fm (API v2)
* improved scaling of visual graph
* removed code leftovers on admin page

= 0.6.7 =
* added support for attached images
* added debug output for post thumbnails
* fixed suffix removal for thumbnail URLs
* fixed JW Player support, only worked for relative URLs

= 0.6.6.1 =
* user agent tester was accidently enabled - fixed!

= 0.6.6 =
* added support for Bambuser iFrame players
* extended Justin.tv support to Twitch.tv domain
* improved Ustream support to work with both live and recorded streams
* improved scaling of visual graph

= 0.6.5 =
* visual graph scales better over time (there's room for improvement)
* fixed bug in conditional checkbox states

= 0.6.4 =
* visual graph can be enabled on standard options page
* fixed bug in conditional checkbox states

= 0.6.3 =
* added support for Internet Archive
* added options for visual graph

= 0.6.2 =
* improved stats visualization
* modified curve type in visual graph

= 0.6.1 =
* fixed and unlocked visual graph for public
* optimized code for options page
* increased timeout for API queries

= 0.6 =
* added support for Livestream
* β: added visual graph for statistics (there ARE bugs!)
* added switch for quick user agent tests
* improved stylesheet and jscript loading for options page
* improved suffix removal for soundcloud thumbnails
* improved debugger for Google+ snippets
* fixed bug in Google+ snippets output
* updated Digg user agent

= 0.5.14 =
* plugin now uses Wordpress' internal JQuery
* disabled upgrade function, needs fixing

= 0.5.13 =
* enabled easy index deletion in debug mode
* improved debugging for Google+ image properties
* removed fallback option

= 0.5.12 =
* limited image properties to Google+ user agent
* fixed bug adding image properties
* fixed bug adding Google+ metatags

= 0.5.11 =
* added option to add image properties
* modified upgrade function

= 0.5.10 =
* added support for Bambuser
* improved debugger output
* fixed bug in markup of option page

= 0.5.9 =
* fixed bug limiting the deletion of the index

= 0.5.8 =
* added option to delete indexed data
* fixed bug saving empty data
* modified upgrade function

= 0.5.7 =
* added option for data expiry
* added percentage indicator to statistics
* modified debugger permissions
* regrouped advanced options
* removed obsolete update warning

= 0.5.6 =
* added upgrade call to option page
* hidden some unused controls (oops!)
* restructured code

= 0.5.5 =
* added option to show menu in admin bar
* added upgrade function
* added options to disable support for images, video posters or jw player
* modified admin bar menu

= 0.5.4 =
* added admin bar link when browsing in debug mode
* added option to add metatags for Google+ snippets
* added image type declaration for single image posts
* (re-)added suffix filtering
* unlocked statistics on option page

= 0.5.3 =
* added options to limit User Agent access to Digg
* added option to filter images in theme directory
* β: added statistics on option page
* improved fallback harvester
* moved shifting function
* tweaked CSS

= 0.5.2.1 =
* fixed bug on plugins page

= 0.5.2 =
* wrapped admin functions in class
* improved on-post fallback to harvest new data (disable in source)
* improved input validation
* updated FAQ

= 0.5.1 =
* interface refinements

= 0.5 =
* added option to save thumbnail URLs as post-meta ("on-post")
* added support for 8tracks
* added option to disable plugin on front page
* added timer to debugger output
* added admin notice when running debug mode
* added admin notice for new settings
* replaced file exist function
* enabled file exist function for on-post mode
* fixed CSS on option page
* fixed bug in Viddler image retrieval
* increased cURL timeout

= 0.4.5 =
* added support for legacy Viddler widgets
* enabled Official.fm (waiting for the API to work)
* changed Blip.tv detection from XML to JSON
* modified debugger output
* modified option page dialogs
* ordered code segements alphabetically

= 0.4.4 =
* unified function to query JSON files, removed old functions
* added permission check for Flickr videos
* improved API key handling

= 0.4.3 =
* added support for Flickr videos
* added support Justin.tv

= 0.4.2 =
* added support for Ustream
* added support for HTML5 video posters
* added support for JWPlayer
* improved API key handling

= 0.4.1 =
* added nonce to options page form
* fixed possible update problem

= 0.4 =
* redesigned option page
* β: added option to filter custom URLs

= 0.3.5 =
* improved detection for Google+ user-agent (still inaccurate)

= 0.3.4 =
* fixed bug in YouTube image URL
* modified option page dialogs

= 0.3.3 =
* added option to show/hide advanced features

= 0.3.2 =
* added options to limit User Agent access to Google+ and LinkedIn
* added option to control emoticon filtering
* added Gravatar filtering
* improved emoticon detection
* modified option page dialogs

= 0.3.1 =
* added option to limit User Agent access to Facebook (careful with that axe, Eugene!)
* modified options page

= 0.3 =
* added support for Blip.tv and Hulu
* added support for Official.fm (not yet enabled, API is broken)
* added %screenshot% placeholder for website thumbnail
* added settings link in plugins overview
* fixed detection for featured image
* improved debugger output
* improved regexp to support https throughout
* removed option to use old bandcamp detection

= 0.2.8 =
* alternative bandcamp detection set to default
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
* wrapped functions in class
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

= 0.6 =
Due to the ongoing Official.tv make-over, API requests will not work for an undisclosed time

= 0.5 =
Review your settings!

= 0.2.3 =
Activate triggers after upgrading

= 0.2 =
Please adjust your settings after upgrading