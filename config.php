<?php

// OGRAPHR OPTIONS
    define("OGRAPHR_VERSION", "0.8.31");
    // enables developer settings on WordPress interface, can be overwritten from plug-in settings once activated
    define("OGRAPHR_DEVMODE", FALSE);
    // replace default description with user agent in use
    define("OGRAPHR_UATEST", FALSE);
    // specify timeout for all HTTP requests (default is 1 second, http://googlecode.blogspot.co.at/2012/01/lets-make-tcp-faster.html)
    define("OGRAPHR_TIMEOUT", 1);

// ATTACHMENT IMAGE
    // default image size (thumbnail, medium, large, full)
    define("ATTACHMENT_IMAGE_SIZE", "medium");
    
// 8TRACKS
    // no need to change this unless you want to use your own 8tracks API key (-> http://8tracks.com/developers/new)
    define("ETRACKS_API_KEY", "e310c354bf4633de8dca0e7fb0a3a23fcc1614fe");
    // default artwork size (sq56=56x56, sq100=100x100, sq133=133x133, sq250=250x250, sq500=500x500, max133w=133 on longest side, max200=200 on longest side, max1024=1024 on longest side, original)
    define("ETRACKS_IMAGE_SIZE", "max200");
    
// BAMBUSER
    // no need to change this unless you want to use your own Bambuser API key (-> http://bambuser.com/api/keys)
    define("BAMBUSER_API_KEY", "0b2d6b4a0c990fe87c64af3fff13832e");

// BANDCAMP
    // default artwork size (small_art_url=100x100, large_art_url=350x350)
    define("BANDCAMP_IMAGE_SIZE", "large_art_url");
    
// FLICKR
    // no need to change this unless you want to use your own Flickr API key (-> http://www.flickr.com/services/apps/create/apply/)
    define("FLICKR_API_KEY", "2250a1cc92a662d9ea156b4e04ca7a88");
    // default artwork size (s=75x75, q=150x150, t=100 on longest side, m=240 on longest side, n=320 on longest side)
    define("FLICKR_IMAGE_SIZE", "n");
    
// MIXCLOUD
    // default artwork size (small=25x25, thumbnail=50x50, medium_mobile=80x80, medium=150x150, large=300x300, extra_large=600x600)
    define("MIXCLOUD_IMAGE_SIZE", "extra_large");

// OFFICIAL.FM
    // default artwork size (tiny=40x40, small=120x120, medium=300x300, large=600x600)
    define("OFFICIAL_IMAGE_SIZE", "large");

// SOCIALCAM
    // default artwork size (main_thumb, small_thumb)
    define("SOCIALCAM_IMAGE_SIZE", "small_thumb");

// SOUNDCLOUD
    // no need to change this unless you want to use your own SoundCloud API key (-> http://soundcloud.com/you/apps)
    define("SOUNDCLOUD_API_KEY", "15fd95172fa116c0837c4af8e45aa702");
    // default artwork size (mini=16x16, tiny=20x20, small=32x32, badge=47x47, t67x67, large=100x100, t300x300, crop=400x400, t500x500)
    define("SOUNDCLOUD_IMAGE_SIZE", "t500x500");
    
// SPOTIFY
    // default artwork size (60, 85, 120, 300, and 640)
    define("SPOTIFY_IMAGE_SIZE", "640");

// VIMEO
    // default snapshot size (thumbnail_small=100, thumbnail_medium=200, thumbnail_large=640)
    define("VIMEO_IMAGE_SIZE", "thumbnail_large");
    
// USTREAM
    // no need to change this unless you want to use your own Ustream.fm API key (-> http://developer.ustream.tv/apikey/generate)
    define("USTREAM_API_KEY", "8E640EF9692DE21E1BC4373F890F853C");
    // default artwork size (small=120x90, medium=240x180)
    define("USTREAM_IMAGE_SIZE", "medium");
    
// JUSTIN.TV
    // default snapshot size (small=100, medium=200, large=640)
    define("JUSTINTV_IMAGE_SIZE", "image_url_large");

// TWITTER CARD
    // default size for Twitter Card (summary=120x120, summary_large_image=438x?)
    define("TWITTER_CARD_TYPE", "summary");

// USER-AGENTS
    // facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)
    define('FACEBOOK_USERAGENT', '/facebookexternalhit/i');
    // Google (+https://developers.google.com/+/web/snippet/)
    define('GOOGLEPLUS_USERAGENT', '/Google \(\+https:\/\/developers\.google\.com\/\+\/web\/snippet\/\)/i');
    // LinkedInBot/1.0 (compatible; Mozilla/5.0; Jakarta Commons-HttpClient/3.1 +http://www.linkedin.com)
    define('LINKEDIN_USERAGENT', '/LinkedInBot/i');
    // Twitterbot
    define('TWITTER_USERAGENT', '/Twitterbot/i');

?>