# OGraphr for WordPress

[![GitHub version](https://badge.fury.io/gh/idleberg%2FOGraphr.svg)](http://badge.fury.io/gh/idleberg%2FOGraphr)
[![Build Status](https://secure.travis-ci.org/idleberg/OGraphr.svg)](http://travis-ci.org/idleberg/OGraphr)
[![devDependency Status](https://david-dm.org/idleberg/OGraphr/dev-status.svg)](https://david-dm.org/idleberg/OGraphr#info=devDependencies)
[![Wordpress Plugin Directory](https://img.shields.io/wordpress/plugin/dt/meta-ographr.svg)](http://wordpress.org/plugins/meta-ographr/)

## Description

This plugin adds several [Open Graph][1] tags to the header of your WordPress site. These include meta information such as site name, a description (the excerpt of a post), the permalink, author, categories and images for your post. As a specialty, images from embedded video and audio players are retrieved as well. Other types of meta-tags are supported as well, including [Google+ Snippets][2], [Twitter Cards][3] and [canonical links][4].

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

## Installation

### Plugin Directory

The easiest way to install OGraphr is through the [Plugin Directory](http://wordpress.org/plugins/meta-ographr/), which you can [access](http://codex.wordpress.org/Managing_Plugins) in the WordPress backend

### GitHub

1. Change directory to `wp-content/plugins`

2. Clone repository `git clone https://github.com/idleberg/OGraphr.git meta-ographr`

3. Activate and set up the plug-in in the WordPress backend

## Developers

The provided `gulpfile.js` will serve as our build tool. In order to use it, we need to have [Node.js](http://nodejs.org/download/) and [Bower](http://bower.io/) installed.

```bash
# install Gulp globally (can be skipped using npm 2.x)
npm install gulp -g

# install Node dependencies
npm install
```

Several gulp tasks are now available. Use `gulp make` to build OGraphr or make use of the `gulp lint` feature. You can also lint files by extensions (`gulp css`, `gulp js` & `gulp php`.)

## FAQ

The [Frequently Asked Questions](https://github.com/idleberg/OGraphr/wiki/Frequently-Asked-Questions) have moved the Wiki

## License

OGraphr is dual-licensed under [The GNU General Public License v2.0][5] and [The MIT License][6].

## Donate

You are welcome support this project using [Flattr](https://flattr.com/submit/auto?user_id=idleberg&url=https://github.com/idleberg/OGraphr) or Bitcoin `17CXJuPsmhuTzFV2k4RKYwpEHVjskJktRd`

[1]: http://ogp.me/
[2]: https://developers.google.com/+/plugins/snippet/
[3]: https://dev.twitter.com/docs/cards
[4]: http://developers.whatwg.org/links.html
[5]: http://www.gnu.org/licenses/gpl-2.0.html
[6]: http://opensource.org/licenses/MIT
