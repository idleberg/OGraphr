# OGraphr for Wordpress

## Description

This plugin adds several [Open Graph][1] tags to the header of your Wordpress site. These include meta information such as site name, a description (the excerpt of a post), the permalink, author, categories and images for your post. As a specialty, images from embedded video and audio players are retrieved as well. Other types of meta-tags are supported as well, including [Google+ Snippets][2], [Twitter Cards][3] and [canonical links][4].

Currently, these media embeds are supported:

* 8tracks
* Bambuser
* Bandcamp
* Blip.tv
* DailyMotion
* Flickr videos
* Hulu
* Internet Archive
* Justin.tv/Twitch.tv
* Livestream
* Mixcloud
* Muzu.tv
* MyVideo
* Official.fm
* Rdio
* Socialcam
* SoundCloud
* Spotify
* Ustream
* Viddler
* Vimeo
* YouTube
* JW Player
* NVB Player
* Standard HTML5 video-tags

Open Graph tags will be used by social network sites such as Facebook, Twitter or Google+ to style a shared link or webpages "liked" by any user. Images supposedly attract more attention, so you might find this plug-in useful.

[View Screenshots](http://wordpress.org/plugins/meta-ographr/screenshots/)

## FAQ

### What is the Open Graph protocol?
The [Open Graph protocol](http://ogp.me/) enables any web page to become a rich object in a social graph. For instance, this is used on Facebook to allow any web page to have the same functionality as any other object on Facebook. The OGraphr plugin for WordPress detects images from popular media players and adds them, alongside other information, to the metadata of your page.

### I don't use social networks, why would I use this?
People share links with their friends on social network sites whether you like it or not. This plug-in gives you some control over how your content is presented on platforms such as Facebook and Google+. Displaying cover artwork or video snapshots with your link usually looks nicer and attracts the attention of potential visitors.

### Why doesn't Facebook display the cover artwork?
Facebook caches previously submitted links for an undisclosed time. If your page has been shared/liked on Facebook before, the cover artwork will not appear until that cache has expired or the page is opened in the [Facebook debugger](http://developers.facebook.com/tools/debug). To make sure the plugin is active and working, you can always look for the og:image tags in the source of your page - or you can force a cache refresh using the [Facebook debugger](http://developers.facebook.com/tools/debug).

### Why do I need a Bandcamp API key?
Bandcamp is rather restrictive with access to their API, usually only allowing access to owners of material hosted on their platform. In order to get an API key, you have to apply via email.

### Do I really need a Viddler API key?
You probably don't. All new Viddler players use HTML5-compliant poster images and these can be detected without making an API call. It's only old "Legacy" players rely on Viddler's API and you need a valid developer key to access it.

### What about site performance?
Depending on the amount of embed codes in your site, retrieving images and other informations can delay the rendering of a page. This can be avoided by retrieving images only once when an article has been published or updated. You can further restrict OGraphr to trigger only when called by Facebook, Google+, LinkedIn or Twitter.

### Why are there no Google+ meta-tags in my page source?
Since Google+ is probably the only site using these meta-tags, they will only be added to the source when a link is posted on a profile. However, you can force displaying these meta-tags when activating OGraphr's debug-mode.

### Is there a good reason to add link elements for thumbnails?
Probably not. Link elements were a common way to add website thumbnails before Facebook introduced its Open Graph protocol. There might be a couple of sites still retrieving thumbnails through link elements, Digg used to be one of them.

### How can I use new features marked as beta?
As beta features can be unstable, they can only be enabled through the plugin's source. Open the file `meta-ographr_index.php` and set `OGRAPHR_DEVMODE` to `TRUE`. From now on, you will see developer settings on the plugin options page, where you can enable beta features.

### Why am I getting a class error when activating the plug-in?
There's a [well-known bug](http://xcache.lighttpd.net/ticket/300) in XCache that will make it impossible to run OGraphr (and many other WordPress plug-ins) at the current moment. You can either disable XCache or hope for a future version to fix this. Sorry!

## License

### GNU GENERAL PUBLIC LICENSE

Version 2, June 1991

	Copyright (C) 1989, 1991 Free Software Foundation, Inc.  
	51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA

	Everyone is permitted to copy and distribute verbatim copies
	of this license document, but changing it is not allowed.

[full license text][5]

## Donate

You are welcome support this project using [Flattr](https://flattr.com/submit/auto?user_id=idleberg&url=https://github.com/idleberg/OGraphr) or Bitcoin `17CXJuPsmhuTzFV2k4RKYwpEHVjskJktRd`

[1]: http://ogp.me/
[2]: https://developers.google.com/+/plugins/snippet/
[3]: https://dev.twitter.com/docs/cards
[4]: http://developers.whatwg.org/links.html
[5]: http://www.gnu.org/licenses/gpl-2.0.html

[![Bitdeli Badge](https://d2weczhvl823v0.cloudfront.net/idleberg/ographr/trend.png)](https://bitdeli.com/free "Bitdeli Badge")

