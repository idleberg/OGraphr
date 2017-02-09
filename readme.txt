=== OGraphr for WordPress ===
Contributors: yathosho
Donate link: https://www.gittip.com/idleberg/
Tags: opengraph,open-graph,open graph,open graph meta,metatags,facebook,google+,google snippets,twitter cards,thumbnails,soundcloud,mixcloud,bandcamp,vimeo,youtube,dailymotion,blip.tv,hulu,internet archive,archive.org,myvideo,official.fm,ustream,viddler,html5,livestream video,jwplayer,nvbplayer,flickr,justin.tv,twitch.tv,8tracks,bambuser,rdio,socialcam,spotify,muzu.tv
Requires at least: 3.0
Tested up to: 4.7.2
Stable tag: 0.8.39
License: GPLv2, MIT

Retrieves the images of audio/video players in your posts and embeds them as thumbnails on Facebook and other social networks.

== Description ==

This plugin adds several Open Graph meta-tags to the header of your theme. These include meta information such as site name, a description (the excerpt of a post), the permalink, author, categories and images for your post. As a specialty, images from embedded video and audio players are retrieved as well. Other types of meta-tags are supported as well, including Google+ Snippets, Twitter Cards and canonical links.

Currently, these media embeds are supported:

* 8tracks
* Bambuser
* Bandcamp
* DailyMotion
* Flickr videos
* Hulu
* Internet Archive
* Livestream
* Mixcloud
* MyVideo
* Official.fm
* Rdio
* SoundCloud
* Spotify
* Twitch.tv
* Ustream
* Viddler
* Vimeo
* YouTube
* JW Player
* NVB Player
* Standard HTML5 video-tags

Open Graph tags will be used by social network sites such as Facebook, Twitter or Google+ to style a shared link or webpages "liked" by any user. Images supposedly attract more attention, so you might find this plug-in useful.

== Installation ==

1. It is required to use PHP 5.2 (or later)

2. Upload the folder `meta-ographr` with all its contents to the `/wp-content/plugins/` directory

3. Activate the plugin through the 'Plugins' menu in WordPress

4. Review the preferences to make use of all features

== Frequently Asked Questions ==

= What is the Open Graph protocol? =

The [Open Graph protocol](http://ogp.me/ "Open Graph protocol") enables any web page to become a rich object in a social graph. For instance, this is used on Facebook to allow any web page to have the same functionality as any other object on Facebook. The OGraphr plugin for WordPress detects images from popular media players and adds them, alongside other information, to the metadata of your page.

= I don't use social networks, why would I use this? =

People share links with their friends on social network sites whether you like it or not. This plug-in gives you some control over how your content is presented on platforms such as Facebook and Google+. Displaying cover artwork or video snapshots with your link usually looks nicer and attracts the attention of potential visitors.

= Why doesn't Facebook display the cover artwork? =

Facebook caches previously submitted links for an undisclosed time. If your page has been shared/liked on Facebook before, the cover artwork will not appear until that cache has expired or the page is opened in the [Facebook debugger](http://developers.facebook.com/tools/debug "Facebook debugger"). To make sure the plugin is active and working, you can always look for the og:image tags in the source of your page - or you can force a cache refresh using the (http://developers.facebook.com/tools/debug "Facebook debugger").

= Why do I need a Bandcamp API key? =

Bandcamp is rather restrictive with access to their API, usually only allowing access to owners of material hosted on their platform. In order to get an API key, you have to apply via email.

= Do I really need a Viddler API key? =

You probably don't. All new Viddler players use HTML5-compliant poster images and these can be detected without making an API call. It's only old "Legacy" players rely on Viddler's API and you need a valid developer key to access it.

= What about site performance? =

Depending on the amount of embed codes in your site, retrieving images and other informations can delay the rendering of a page. This can be avoided by retrieving images only once when an article has been published or updated. You can further restrict OGraphr to trigger only when called by Facebook, Google+, LinkedIn or Twitter.

= Why are there no Google+ meta-tags in my page source? =

Since Google+ is probably the only site using these meta-tags, they will only be added to the source when a link is posted on a profile. However, you can force displaying these meta-tags when activating the WordPress debug-mode.

= Is there a good reason to add link elements for thumbnails? =

Probably not. Link elements were a common way to add website thumbnails before Facebook introduced its Open Graph protocol. There might be a couple of sites still retrieving thumbnails through link elements, Digg used to be one of them.

= How can I use new features marked as beta? =

As beta features can be unstable, they can only be enabled through the plugin's source. Open the file `index.php` and set `OGRAPHR_DEVMODE` to `TRUE`. From now on, you will see developer settings on the plugin options page, where you can enable beta features.

= Why am I getting a class error when activating the plug-in? =
There's a [well-known bug](http://xcache.lighttpd.net/ticket/300 "well-known bug") in XCache that will make it impossible to run OGraphr (and many other WordPress plug-ins) at the current moment. You can either disable XCache or hope for a future version to fix this. Sorry!

== Screenshots ==

1. a link with a Mixcloud player shared on Facebook

2. a link with a SoundCloud player shared on Google+

3. a link with a Vimeo player shared on Twitter

4. standard settings page for OGraphr 0.8

5. advanced settings page for OGraphr 0.8

== Changelog ==

= 0.8.39 =
* remove support for discontinued services
* fix jqPlot concatention

= 0.8.38 =
* load jqPlot from GitHub repository
* update devDependencies

= 0.8.37 =
* fix: re-add missing assets

= 0.8.36 =
* add support for YouTube's privacy-enhanced mode

= 0.8.35 =
* reverted previous changes concerning debug mode, longterm fix required

= 0.8.34 =
* escape special characters in title (#4)
* removed OGRAPHR_DEVMODE, use WP_DEBUG instead
* reduced warnings in displayed in debug mode
* also: Houston, we need a rewrite!

= 0.8.33 =
* concatenated all assets

= 0.8.32 =
* reverted new configuration file (to be addressed in 0.9)

= 0.8.31 =
* moved configuration to separate file
* fixed timer in debug mode

= 0.8.30 =
* CSS fixes and changes on admin page
* updated devDependencies

= 0.8.29 =
* fixed admin page layout
* renamed PHP files
* renamed assets folder
* removed duplicate command in gulp help
* improved gulp make task

= 0.8.28 =
* added more quicklinks to top-menu
* improved build tools

= 0.8.27 =
* displays link to screenshot guidelines if no screenshot was found
* render_stats() is only called when visual graph is enabled
* updated devDependencies

= 0.8.26 =
* fixed JavaScript for checkbox groups
* fixed padding of right column
* various build tools improvements 

= 0.8.25 =
* added Gulp build & lint tasks
* added Travis CI integration
* modified help button
* tidied some HTML
* updated CSS rules
* changed file structure
* dual licensed code

= 0.8.24.1 =
* re-added missing JavaScript files
* version bump

= 0.8.24 =
* updated patterns to work with protocol-relative URLs
* updated function name to disable Jetpack Open Graph tags (for Jetpack 2.0.3 or later)

= 0.8.23 =
* code clean-up
* fixed Flattr link

= 0.8.22 =
* improved option page layout

= 0.8.21 =
* added support for Muzu.tv

= 0.8.20 =
* fixed nonce field
* updated contact information

= 0.8.19 =
* added support for Spotify (thanks MiniGod - http://stackoverflow.com/a/18294883/1329116)

= 0.8.18 =
* fixed bug in Twitter Card output
* improved debugger output for age restrictions
* removed localization for save button

= 0.8.17 =
* adding a trailing slash to URLs is now optional
* fixed several warnings occuring in WordPress debug mode 

= 0.8.16 =
* adds trailing slash to URLs if missing

= 0.8.15 =
* added support for new Bandcamp players

= 0.8.14 =
* added support for relative URLs in HTML5 video players and image tags
* added option to filter images in wp-includes directory
* added tooltips to some controls on the options page

= 0.8.13 =
* fixed a bug appearing occasionally with single images
* increased default image size

= 0.8.12 =
* fixed bug overwriting retrieved images from HTML5 players

= 0.8.11 =
* fixed activation warning, added PHP version check on plugin activation
* removed unsupported ages from age restrictions
* replaced donation option

= 0.8.10 =
* added filters for plugin and upload directories
* fixed bug with default thumbnails as twitter:image
* modified some dialogs on option page

= 0.8.9 =
* fixed bug with default thumbnails
* fixed bug in update routine
* fixed minor bug when adding og:url tag 
* improved some conditionals
* removed some old commented code

= 0.8.8 =
* removed some old update functions
* upgraded jqPlot and plugins to 1.0.8 r1250
* fixed javascript behaviour on option page

= 0.8.7.1 =
* fixed minor display bug in beta warning

= 0.8.7 =
* added option to always display developer settings
* added option for user agent testing
* fixed bug displaying beta warning

= 0.8.6 =
* support for embedding on Twitter is now public
* debug mode only visible when user can edit plugins
* improved posts settings 
* improved Mixcloud player handling
* article tags only added when object type is article
* increased default Vimeo thumbnail size to meet Twitter Cards requirements
* renamed Twitter image setting

= 0.8.5 =
* re-added duplicate checker
* seperated Facebook-specific tags from Open Graph tags, limited display to Facebook scrapers
* removed legacy PHP fallback queries
* fixed glitch on settings page

= 0.8.4 =
* added option to restrict linking admin profile (article:author)
* added option to abuse a user's Jabber field for Twitter name
* minor debug mode adjustments
* minor performance improvements

= 0.8.3 =
* added URL validator before writing output
* added new media player patterns
* added support for twitter:domain
* fixed SoundCloud image replacement
* fixed bug in Twitter image selection
* fixed warnings appearing in WordPress debug mode
* improved beta/debug warning

= 0.8.2 =
* added fallback method to store data on older PHP versions
* added debugger options to developer settings menu
* modified debugger output format
* fixed behaviour for empty video players

= 0.8.1 =
* changed method to write/read data
* fixed Rdio and Ustream player strings

= 0.8 =
* added support to play video/audio content directly on Facebook
* β: added support to play video/audio content directly on Twitter
* added support for age, country and content restrictions
* added support for iOS and Android mobile apps
* added optional settings per post
* added support for retrieving Twitter IDs from user profile
* merged detection functions into one
* removed cURL support
* disabled link and title tags on category, archive search result pages
* modified Vimeo query to use JSON
* modified option page layout
* modified tooltips for statistics graph
* updated API method for Dailymotion
* improved SoundCloud detection
* fixed bug in Socialcam detection
* renamed "Post thumbnails" label to "Featured images"

= 0.7.11 =
* added support for Mixcloud iFrame embeds
* removed support for languages not supported by Open Graph standard

= 0.7.10 =
* fixed locale for Hebrew

= 0.7.9 =
* added option to choose between permalink or shortlink
* updated Google+ user-agent

= 0.7.8 =
* fixed several errors when running WordPress in debug mode
* disabled statistics in WordPress debug mode (until fixed)
* modified standard settings view
* modified fade effect

= 0.7.7 =
* improved method to disable Jetpack's OpenGraph function
* removed unused functions and code pieces
* modified option page

= 0.7.6 =
* optimized meta-tag output, using less memory
* added support for retrieving Twitter names from user profile
* modified debug output
* modified internal data handling
* fixed bug in Twitter ID output

= 0.7.5 =
* added support for NVB Player
* modified option page layout to better match standard WordPress UI
* fixed result returned from Official.fm
* fixed several errors when running WordPress in debug mode
* fixed bugs in options page HTML
* improved option page descriptions
* removed unnecessary comments

= 0.7.4 =
* added option to disable Jetpack's OpenGraph function (and avoid duplicates)
* added option to add OpenGraph prefix

= 0.7.3 =
* improved input validation
* modified option page layout
* fixed bug in settings on first install
* fixed bug not enabling checkbox to delete indexed data
* fixed bug not using 8tracks API key

= 0.7.2 =
* added support for Twitter IDs
* improved javascript code on option page

= 0.7.1 =
* unlocked Twitter Cards for public
* upgraded jqPlot and plugins to 1.0.4 r1121
* improved language indication
* improved defaults handling
* removed update function

= 0.7 =
* β: added support for Twitter Cards
* added support for Twitter user-agent
* added alternative/cURL-fallback method for JSON retrieval (new default)
* added support for further Open Graph tags
* added option to display Open Graph tags on Facebook only
* improved metatag ordering and output
* improved scaling of visual graph
* modified option page order
* removed function to retrieve Bandcamp artwork
* reduced number of warnings in wp_debug
* fixed debug output for Vimeo and YouTube

= 0.6.14 =
* improved update function
* removed support for Digg user-agent
* modified dialogs

= 0.6.13 =
* added support for Socialcam
* added support for multiple YouTube thumbnails
* added support for link elements (for legacy purposes)
* added new expiry options for debug mode
* added icon on help links
* fixed bug with attached images
* restructured code

= 0.6.12 =
* improved method to set initial options
* fixed display bug after restoring default options
* fixed css

= 0.6.11 =
* added support for BMP (in all unlikeliness) and WebP images
* fixed bug when retrieving attached image
* removed suffix removal

= 0.6.10 =
* added support for blog language (og:locale)
* removed support for schema properties
* moved javascript to footer of options page
* minified options page css and javascript
* upgraded jqPlot and plugins to 1.0.0 r1095
* fixed debug output for custom filters

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
* plugin now uses WordPress' internal JQuery
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
* added a filter for WordPress smilies
* fixed bug not adding all images displayed in a post

= 0.1.1 =
* first public release
* fixed plugin naming inconsistencies

= 0.1 =
* first release

== Upgrade Notice ==

= 0.8.30 =
Some files have been renamed, hence there could be problems updating. Please check whether the plugin is still activated. In worst case, a re-install should fix all issues. Don't worry, your settings will be preserved.

= 0.8.25 =
Should you find yourself in an update loop, please remove the old plugin first. Sorry about this!

= 0.8 =
All previously indexed data will be overwritten

= 0.7.11 =
Review language settings

= 0.7.10 =
Review language settings

= 0.7.3 =
Review settings

= 0.6 =
Due to the ongoing Official.tv make-over, API requests will not work for an undisclosed time

= 0.5 =
Review your settings!

= 0.2.3 =
Activate triggers after upgrading

= 0.2 =
Please adjust your settings after upgrading