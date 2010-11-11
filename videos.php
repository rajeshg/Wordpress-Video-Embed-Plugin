<?php
/*
Plugin Name: Wordpress Video Embed Plugin
Plugin URI: http://rajeshg.com/
Description: A filter for WordPress that displays videos from many video services. If you have a supported video link in your post, it will be automatically embedded.
Author: Rajesh Gollapudi
Author URI: http://rajeshg.com/
*/

/**
 *  Supported video sites: YouTube, Megavideo
 */

// Hulu Code
// eg: http://www.hulu.com/watch/192162/
define("HULU_WIDTH", 480); // default width
define("HULU_HEIGHT", 270); // default height
define("HULU_REGEXP", "/https?\:\/\/\S*hulu\S+\/watch\/\S+/");
define("HULU_TARGET", "<object width=\"###WIDTH###\" height=\"###HEIGHT###\" codebase=\"http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,40,0\"><param name=\"allowFullScreen\" value=\"true\" /><param name=\"src\" value=\"###URL###\" /><param name=\"allowfullscreen\" value=\"true\" /><embed type=\"application/x-shockwave-flash\" width=\"###WIDTH###\" height=\"###HEIGHT###\" src=\"###URL###\" allowfullscreen=\"true\"></embed></object>");

function get_hulu_embed_code($url) {
  // get hulu embed code
  // look for <link rel="media:video" href="http://www.hulu.com/embed/FKFbQqkrg5KRJnmRsToqhg">

  $yql_base_url = "http://query.yahooapis.com/v1/public/yql";
  $yql_query = "select * from html where url = '$url' and xpath='//html/head/link[@rel=\"media:video\"]'";
  $yql_query_url = $yql_base_url . "?q=" . urlencode($yql_query);
  $yql_query_url .= "&format=json";
  $session = curl_init($yql_query_url);
  curl_setopt($session, CURLOPT_RETURNTRANSFER,true);
  $json = curl_exec($session);
  $phpObj =  json_decode($json);
  $embed_url = $phpObj->query->results->link->href;
  return $embed_url;
}

function hulu_plugin_callback($match)
{
  $embed_url = get_hulu_embed_code($match[0]);
  $output = HULU_TARGET;
  $output = str_replace("###URL###", $embed_url, $output);
  $output = str_replace("###WIDTH###", HULU_WIDTH, $output);
  $output = str_replace("###HEIGHT###", HULU_HEIGHT, $output);
  return ($output);
}
function hulu_plugin($content)
{
  return (preg_replace_callback(HULU_REGEXP, 'hulu_plugin_callback', $content));
}

add_filter('the_content', 'hulu_plugin',1);
add_filter('the_content_rss', 'hulu_plugin');
add_filter('comment_text', 'hulu_plugin');


// Novamov Code

define("NOVAMOV_WIDTH", 590); // default width
define("NOVAMOV_HEIGHT", 430); // default height
define("NOVAMOV_REGEXP", "/\[novamov ([[:print:]]+)\]/");
define("NOVAMOV_TARGET", "<iframe style=\"overflow: hidden; border: 0; width: ".NOVAMOV_WIDTH."px; height: ".NOVAMOV_HEIGHT."px\" src=\"http://www.novamov.com/embed.php?v=###URL###\" scrolling=\"no\"></iframe>");

function novamov_plugin_callback($match) {
  $output = NOVAMOV_TARGET;
  $output = str_replace("###URL###", $match[1], $output);
  return ($output);
}

function novamov_plugin($content) {
  return preg_replace_callback(NOVAMOV_REGEXP, 'novamov_plugin_callback', $content);
}

add_filter('the_content', 'novamov_plugin');
add_filter('the_content_rss', 'novamov_plugin');
add_filter('comment_text', 'novamov_plugin');
add_filter('the_excerpt', 'novamov_plugin');

// FLICKR CODE by an anonymous user

define("FLICKR_WIDTH", 308); // default width
define("FLICKR_HEIGHT", 250); // default height
define("FLICKR_REGEXP", "/\[flickr ([[:print:]]+)\]/");
define("FLICKR_TARGET", "<object type=\"application/x-shockwave-flash\" width=\"###WIDTH###\" height=\"###HEIGHT###\" data=\"http://www.flickr.com/apps/video/stewart.swf?v=71377\"><param name=\"flashvars\" value=\"intl_lang=en-us&photo_secret=1669be43ac&photo_id=###URL###&hd_default=false\"></param><param name=\"movie\" value=\"http://www.flickr.com/apps/video/stewart.swf?v=71377\" /><param name=\"bgcolor\" value=\"#000000\"></param><param name=\"allowFullScreen\" value=\"true\" /><embed src=\"http://www.flickr.com/apps/video/stewart.swf?v=71377\" type=\"application/x-shockwave-flash\" allowfullscreen=\"true\" bgcolor=\"#0000000\" flashvars=\"intl_lang=en-us&photo_secret=1669be43ac&photo_id=###URL###&hd_default=false\" width=\"###WIDTH###\" height=\"###HEIGHT###\"></embed></object>");

function flickr_plugin_callback($match)
{
  $tag_parts = explode(" ", rtrim($match[0], "]"));
  $output = FLICKR_TARGET;
  $output = str_replace("###URL###", $tag_parts[1], $output);
  if (count($tag_parts) > 2) {
    if ($tag_parts[2] == 0) {
      $output = str_replace("###WIDTH###", FLICKR_WIDTH, $output);
    } else {
      $output = str_replace("###WIDTH###", $tag_parts[2], $output);
    }
    if ($tag_parts[3] == 0) {
      $output = str_replace("###HEIGHT###", FLICKR_HEIGHT, $output);
    } else {
      $output = str_replace("###HEIGHT###", $tag_parts[3], $output);
    }
  } else {
    $output = str_replace("###WIDTH###", FLICKR_WIDTH, $output);
    $output = str_replace("###HEIGHT###", FLICKR_HEIGHT, $output);
  }
  return ($output);
}
function flickr_plugin($content)
{
  return (preg_replace_callback(FLICKR_REGEXP, 'flickr_plugin_callback', $content));
}

add_filter('the_content', 'flickr_plugin',1);
add_filter('the_content_rss', 'flickr_plugin');
add_filter('comment_text', 'flickr_plugin');
add_filter('the_excerpt', 'flickr_plugin');

// FB Code
// Code for FaceBook video
// credits: roberto scano http://robertoscano.info

define("FB_WIDTH", 470);
define("FB_HEIGHT", 306);
define("FB_REGEXP", "/\[FB ([[:print:]]+)\]/");
define("FB_TARGET", "<object type=\"application/x-shockwave-flash\"
data=\"http://www.facebook.com/v/###URL###\" width=\"".FB_WIDTH."\"
height=\"".FB_HEIGHT."\"><param name=\"autostart\" value=\"false\" /><param
name=\"movie\" value=\"http://www.facebook.com/v/###URL###\" /></object>");

function FB_plugin_callback($match) {
  $output = FB_TARGET;
  $output = str_replace("###URL###", $match[1], $output);
  return ($output);
}

function FB_plugin($content) {
  return preg_replace_callback(FB_REGEXP, 'FB_plugin_callback',
$content);
}

add_filter('the_content', 'FB_plugin');
add_filter('the_content_rss', 'FB_plugin');
add_filter('comment_text', 'FB_plugin');
add_filter('the_excerpt', 'FB_plugin');

// current code

define("CURRENT_WIDTH", 400); // default width
define("CURRENT_HEIGHT", 342); // default height
define("CURRENT_REGEXP", "/\[current ([[:print:]]+)\]/");
define("CURRENT_TARGET", "<object width=\"###WIDTH###\"  height=\"###HEIGHT###\"><param name=\"movie\" value=\"http://current.com/e/###URL###/en_US\"></param><param name=\"wmode\" value=\"transparent\"></param><param name=\"allowfullscreen\" value=\"true\"></param><param name=\"allowscriptaccess\" value=\"always\"></param><embed src=\"http://current.com/e/###URL###/en_US\" type=\"application/x-shockwave-flash\"  width=\"###WIDTH###\"  height=\"###HEIGHT###\" wmode=\"transparent\" allowfullscreen=\"true\" allowscriptaccess=\"always\"></embed></object>");

function current_plugin_callback($match)
{
  $tag_parts = explode(" ", rtrim($match[0], "]"));
  $output = CURRENT_TARGET;
  $output = str_replace("###URL###", $tag_parts[1], $output);
  if (count($tag_parts) > 2) {
    if ($tag_parts[2] == 0) {
      $output = str_replace("###WIDTH###", CURRENT_WIDTH, $output);
    } else {
      $output = str_replace("###WIDTH###", $tag_parts[2], $output);
    }
    if ($tag_parts[3] == 0) {
      $output = str_replace("###HEIGHT###", CURRENT_HEIGHT, $output);
    } else {
      $output = str_replace("###HEIGHT###", $tag_parts[3], $output);
    }
  } else {
    $output = str_replace("###WIDTH###", CURRENT_WIDTH, $output);
    $output = str_replace("###HEIGHT###", CURRENT_HEIGHT, $output);
  }
  return ($output);
}
function current_plugin($content)
{
  return (preg_replace_callback(CURRENT_REGEXP, 'current_plugin_callback', $content));
}

add_filter('the_content', 'current_plugin');
add_filter('the_content_rss', 'current_plugin');
add_filter('comment_text', 'current_plugin');
add_filter('the_excerpt', 'current_plugin');


// screencast-o-matic code

define("SCREENCAST_WIDTH", 504); // default width
define("SCREENCAST_HEIGHT", 424); // default height
define("SCREENCAST_REGEXP", "/\[screencast ([[:print:]]+)\]/");
define("SCREENCAST_TARGET", "<object width=\"###WIDTH###\"  height=\"###HEIGHT###\" data=\"http://www.screencast-o-matic.com/embed?sc=###URL###&w=500&np=0&v=2\" type=\"text/html\"></object>");

function screencast_plugin_callback($match)
{
  $tag_parts = explode(" ", rtrim($match[0], "]"));
  $output = SCREENCAST_TARGET;
  $output = str_replace("###URL###", $tag_parts[1], $output);
  if (count($tag_parts) > 2) {
    if ($tag_parts[2] == 0) {
      $output = str_replace("###WIDTH###", SCREENCAST_WIDTH, $output);
    } else {
      $output = str_replace("###WIDTH###", $tag_parts[2], $output);
    }
    if ($tag_parts[3] == 0) {
      $output = str_replace("###HEIGHT###", SCREENCAST_HEIGHT, $output);
    } else {
      $output = str_replace("###HEIGHT###", $tag_parts[3], $output);
    }
  } else {
    $output = str_replace("###WIDTH###", SCREENCAST_WIDTH, $output);
    $output = str_replace("###HEIGHT###", SCREENCAST_HEIGHT, $output);
  }
  return ($output);
}
function screencast_plugin($content)
{
  return (preg_replace_callback(SCREENCAST_REGEXP, 'screencast_plugin_callback', $content));
}

add_filter('the_content', 'screencast_plugin');
add_filter('the_content_rss', 'screencast_plugin');
add_filter('comment_text', 'screencast_plugin');
add_filter('the_excerpt', 'screencast_plugin');

// d1g.com

define("D1G_WIDTH", 400); // default width
define("D1G_HEIGHT", 300); // default height
define("D1G_REGEXP", "/\[d1g ([[:print:]]+)\]/");
define("D1G_TARGET", "<object width=\"###WIDTH###\" height=\"###HEIGHT###\"><param value=\"#000000\" name=\"bgcolor\"><param name=\"movie\" value=\"http://www.d1g.com/swf/embedded_video_player.swf?id=2378&usefullscreen=false&file=http://www.d1g.com/video/play_video/###URL###&autostart=false&overstretch=false&repeat=false&shuffle=false\"></param><embed src=\"http://www.d1g.com/swf/embedded_video_player.swf?id=2378&file=http://www.d1g.com/video/play_video/###URL###&usefullscreen=false&autostart=false&overstretch=false&repeat=false&shuffle=false\" type=\"application/x-shockwave-flash\" width=\"###WIDTH###\" height=\"###HEIGHT###\" bgcolor=\"#000000\"></embed></object>");

function d1g_plugin_callback($match)
{
  $tag_parts = explode(" ", rtrim($match[0], "]"));
  $output = D1G_TARGET;
  $output = str_replace("###URL###", $tag_parts[1], $output);
  if (count($tag_parts) > 2) {
    if ($tag_parts[2] == 0) {
      $output = str_replace("###WIDTH###", D1G_WIDTH, $output);
    } else {
      $output = str_replace("###WIDTH###", $tag_parts[2], $output);
    }
    if ($tag_parts[3] == 0) {
      $output = str_replace("###HEIGHT###", D1G_HEIGHT, $output);
    } else {
      $output = str_replace("###HEIGHT###", $tag_parts[3], $output);
    }
  } else {
    $output = str_replace("###WIDTH###", D1G_WIDTH, $output);
    $output = str_replace("###HEIGHT###", D1G_HEIGHT, $output);
  }
  return ($output);
}
function d1g_plugin($content)
{
  return (preg_replace_callback(D1G_REGEXP, 'd1g_plugin_callback', $content));
}

add_filter('the_content', 'd1g_plugin');
add_filter('the_content_rss', 'd1g_plugin');
add_filter('comment_text', 'd1g_plugin');
add_filter('the_excerpt', 'd1g_plugin');

// MEGAVIDEO
// eg: http://megavideo.com/?v=RXT2BEGL
define("MEGAVIDEO_WIDTH", 432); // default width
define("MEGAVIDEO_HEIGHT", 351); // default height
// define("MEGAVIDEO_REGEXP", "/\[megavideo ([[:print:]]+)\]/");
define("MEGAVIDEO_REGEXP", "/https?\:\/\/\S*megavideo\S+\?(\S+)/");
//define("MEGAVIDEO_TARGET", "<object width=\"###WIDTH###\" height=\"###HEIGHT###\"><param name=\"movie\" value=\"http://www.megavideo.com/v/###URL###.3920544471.0\"></param><param name=\"wmode\" value=\"transparent\"></param><embed src=\"http://www.megavideo.com/v/###URL###\" type=\"application/x-shockwave-flash\" wmode=\"transparent\" width=\"###WIDTH###\" height=\"###HEIGHT###\"></embed></object>");
define("MEGAVIDEO_TARGET", "<object width=\"###WIDTH###\" height=\"###HEIGHT###\"><param name=\"movie\" value=\"http://www.megavideo.com/v/###URL###\"></param><param name=\"allowFullScreen\" value=\"true\"></param><embed src=\"http://www.megavideo.com/v/###URL###\" type=\"application/x-shockwave-flash\" allowfullscreen=\"true\" width=\"###WIDTH###\" height=\"###HEIGHT###\"></embed></object>");
function megavideo_plugin_callback($match)
{
  $output = MEGAVIDEO_TARGET;
  parse_str($match[1], $myarr);
  $output = str_replace("###URL###", $myarr['v'], $output);
  $output = str_replace("###WIDTH###", MEGAVIDEO_WIDTH, $output);
  $output = str_replace("###HEIGHT###", MEGAVIDEO_HEIGHT, $output);
  return ($output);
}
function megavideo_plugin($content)
{
  return (preg_replace_callback(MEGAVIDEO_REGEXP, 'megavideo_plugin_callback', $content));
}

add_filter('the_content', 'megavideo_plugin');
add_filter('the_content_rss', 'megavideo_plugin');
add_filter('comment_text', 'megavideo_plugin');
add_filter('the_excerpt', 'megavideo_plugin');

// MSN Video (soapbox)

define("MSN_WIDTH", 432); // default width
define("MSN_HEIGHT", 364); // default height
define("MSN_REGEXP", "/\[msn ([[:print:]]+)\]/");
define("MSN_TARGET", "<embed src=\"http://images.video.msn.com/flash/soapbox1_1.swf\" quality=\"high\" width=\"###WIDTH###\" height=\"###HEIGHT###\" base=\"http://images.video.msn.com\" type=\"application/x-shockwave-flash\" allowFullScreen=\"true\" allowScriptAccess=\"always\" pluginspage=\"http://macromedia.com/go/getflashplayer\" flashvars=\"c=v&v=###URL###\"></embed>");

function msn_plugin_callback($match)
{
  $tag_parts = explode(" ", rtrim($match[0], "]"));
  $output = MSN_TARGET;
  $output = str_replace("###URL###", $tag_parts[1], $output);
  if (count($tag_parts) > 2) {
    if ($tag_parts[2] == 0) {
      $output = str_replace("###WIDTH###", MSN_WIDTH, $output);
    } else {
      $output = str_replace("###WIDTH###", $tag_parts[2], $output);
    }
    if ($tag_parts[3] == 0) {
      $output = str_replace("###HEIGHT###", MSN_HEIGHT, $output);
    } else {
      $output = str_replace("###HEIGHT###", $tag_parts[3], $output);
    }
  } else {
    $output = str_replace("###WIDTH###", MSN_WIDTH, $output);
    $output = str_replace("###HEIGHT###", MSN_HEIGHT, $output);
  }
  return ($output);
}
function msn_plugin($content)
{
  return (preg_replace_callback(MSN_REGEXP, 'msn_plugin_callback', $content));
}

add_filter('the_content', 'msn_plugin');
add_filter('the_content_rss', 'msn_plugin');
add_filter('comment_text', 'msn_plugin');
add_filter('the_excerpt', 'msn_plugin');

// Youtube Playlist Code

define("YTPLAYLIST_WIDTH", 470); // default width
define("YTPLAYLIST_HEIGHT", 406); // default height
define("YTPLAYLIST_REGEXP", "/\[youtubeplaylist ([[:print:]]+)\]/");
define("YTPLAYLIST_TARGET", "<object width=\"###WIDTH###\" height=\"###HEIGHT###\"><param name=\"movie\" value=\"http://de.youtube.com/p/###URL###\" /><param name=\"wmode\" value=\"transparent\" /><embed src=\"http://de.youtube.com/p/###URL###\" type=\"application/x-shockwave-flash\" width=\"###WIDTH###\" height=\"###HEIGHT###\" wmode=\"transparent\"></embed></object>
");

function ytplaylist_plugin_callback($match)
{
  $tag_parts = explode(" ", rtrim($match[0], "]"));
  $output = YTPLAYLIST_TARGET;
  $output = str_replace("###URL###", $tag_parts[1], $output);
  if (count($tag_parts) > 2) {
    if ($tag_parts[2] == 0) {
      $output = str_replace("###WIDTH###", YTPLAYLIST_WIDTH, $output);
    } else {
      $output = str_replace("###WIDTH###", $tag_parts[2], $output);
    }
    if ($tag_parts[3] == 0) {
      $output = str_replace("###HEIGHT###", YTPLAYLIST_HEIGHT, $output);
    } else {
      $output = str_replace("###HEIGHT###", $tag_parts[3], $output);
    }
  } else {
    $output = str_replace("###WIDTH###", YTPLAYLIST_WIDTH, $output);
    $output = str_replace("###HEIGHT###", YTPLAYLIST_HEIGHT, $output);
  }
  return ($output);
}
function ytplaylist_plugin($content)
{
  return (preg_replace_callback(YTPLAYLIST_REGEXP, 'ytplaylist_plugin_callback', $content));
}

add_filter('the_content', 'ytplaylist_plugin',1);
add_filter('the_content_rss', 'ytplaylist_plugin',1);
add_filter('comment_text', 'ytplaylist_plugin');
add_filter('the_excerpt', 'ytplaylist_plugin');

// Collegehumor Code

define("COLLEGEHUMOR_WIDTH", 480); // default width
define("COLLEGEHUMOR_HEIGHT", 360); // default height
define("COLLEGEHUMOR_REGEXP", "/\[collegehumor ([[:print:]]+)\]/");
define("COLLEGEHUMOR_TARGET", "<object type=\"application/x-shockwave-flash\" data=\"http://www.collegehumor.com/moogaloop/moogaloop.swf?clip_id=###URL###&fullscreen=1\" width=\"###WIDTH###\" height=\"###HEIGHT###\"><param name=\"allowfullscreen\" value=\"true\" /><param name=\"movie\" quality=\"best\" value=\"http://www.collegehumor.com/moogaloop/moogaloop.swf?clip_id=###URL###&fullscreen=1\" /></object>");

function collegehumor_plugin_callback($match)
{
  $tag_parts = explode(" ", rtrim($match[0], "]"));
  $output = COLLEGEHUMOR_TARGET;
  $output = str_replace("###URL###", $tag_parts[1], $output);
  if (count($tag_parts) > 2) {
    if ($tag_parts[2] == 0) {
      $output = str_replace("###WIDTH###", COLLEGEHUMOR_WIDTH, $output);
    } else {
      $output = str_replace("###WIDTH###", $tag_parts[2], $output);
    }
    if ($tag_parts[3] == 0) {
      $output = str_replace("###HEIGHT###", COLLEGEHUMOR_HEIGHT, $output);
    } else {
      $output = str_replace("###HEIGHT###", $tag_parts[3], $output);
    }
  } else {
    $output = str_replace("###WIDTH###", COLLEGEHUMOR_WIDTH, $output);
    $output = str_replace("###HEIGHT###", COLLEGEHUMOR_HEIGHT, $output);
  }
  return ($output);
}
function collegehumor_plugin($content)
{
  return (preg_replace_callback(COLLEGEHUMOR_REGEXP, 'collegehumor_plugin_callback', $content));
}

add_filter('the_content', 'collegehumor_plugin');
add_filter('the_content_rss', 'collegehumor_plugin');
add_filter('comment_text', 'collegehumor_plugin');
add_filter('the_excerpt', 'collegehumor_plugin');

// Jumpcut Code

define("JUMPCUT_WIDTH", 408); // default width
define("JUMPCUT_HEIGHT", 324); // default height
define("JUMPCUT_REGEXP", "/\[jumpcut ([[:print:]]+)\]/");
define("JUMPCUT_TARGET", "<embed type=\"application/x-shockwave-flash\" src=\"http://jumpcut.com/media/flash/jump.swf?id=###URL###&asset_type=movie&asset_id=###URL###&eb=1\" width=\"###WIDTH###\" height=\"###HEIGHT###\"></embed>");

function jumpcut_plugin_callback($match)
{
  $tag_parts = explode(" ", rtrim($match[0], "]"));
  $output = JUMPCUT_TARGET;
  $output = str_replace("###URL###", $tag_parts[1], $output);
  if (count($tag_parts) > 2) {
    if ($tag_parts[2] == 0) {
      $output = str_replace("###WIDTH###", JUMPCUT_WIDTH, $output);
    } else {
      $output = str_replace("###WIDTH###", $tag_parts[2], $output);
    }
    if ($tag_parts[3] == 0) {
      $output = str_replace("###HEIGHT###", JUMPCUT_HEIGHT, $output);
    } else {
      $output = str_replace("###HEIGHT###", $tag_parts[3], $output);
    }
  } else {
    $output = str_replace("###WIDTH###", JUMPCUT_WIDTH, $output);
    $output = str_replace("###HEIGHT###", JUMPCUT_HEIGHT, $output);
  }
  return ($output);
}
function jumpcut_plugin($content)
{
  return (preg_replace_callback(JUMPCUT_REGEXP, 'jumpcut_plugin_callback', $content));
}

add_filter('the_content', 'jumpcut_plugin');
add_filter('the_content_rss', 'jumpcut_plugin');
add_filter('comment_text', 'jumpcut_plugin');
add_filter('the_excerpt', 'jumpcut_plugin');

// SlideShare Slides

define("SS_WIDTH", 425);
define("SS_HEIGHT", 355);
define("SS_REGEXP", "/\[slideshare ([[:print:]]+)\]/");
define("SS_TARGET", "<object style=\"margin:0px\" width=\"".SS_WIDTH."\" height=\"".SS_HEIGHT."\"><param name=\"movie\" value=\"http://static.slidesharecdn.com/swf/ssplayer2.swf?doc=###ID###\" /><param name=\"allowFullScreen\" value=\"true\"/><param name=\"allowScriptAccess\" value=\"always\"/><param name=\"wmode\" value=\"transparent\" /><embed src=\"http://static.slidesharecdn.com/swf/ssplayer2.swf?doc=###ID###\" type=\"application/x-shockwave-flash\" allowscriptaccess=\"always\" allowfullscreen=\"true\" width=\"".SS_WIDTH."\" height=\"".SS_HEIGHT."\" wmode=\"transparent\"></embed></object>");

function ss_plugin_callback($match){
   $output = SS_TARGET;
  $output = str_replace("###ID###", $match[1], $output);
  return ($output);
}

function ss_plugin($content){
  return (preg_replace_callback(SS_REGEXP, 'ss_plugin_callback', $content));
}

add_filter('the_content', 'ss_plugin');
add_filter('the_content_rss', 'ss_plugin');
add_filter('comment_text', 'ss_plugin');
add_filter('the_excerpt', 'ss_plugin');

// Brightcove code

define("BRIGHTCOVE_WIDTH", 486);
define("BRIGHTCOVE_HEIGHT", 412);
define("BRIGHTCOVE_REGEXP", "/\[brightcove ([[:print:]]+)\]/");
define("BRIGHTCOVE_TARGET", "<embed src=\"http://c.brightcove.com/services/viewer/federated_f9/10172910001?isVid=1\" bgcolor=\"#FFFFFF\" flashVars=\"videoId=###URL###&playerID=10172910001&domain=embed&\" base=\"http://admin.brightcove.com\" name=\"flashObj\" width=\"".BRIGHTCOVE_WIDTH."\" height=\"".BRIGHTCOVE_HEIGHT."\" seamlesstabbing=\"false\" type=\"application/x-shockwave-flash\" swLiveConnect=\"true\" swLiveConnect=\"true\" pluginspage=\"http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash\"></embed>");

function brightcove_plugin_callback($match)
{
        $output = BRIGHTCOVE_TARGET;
        $output = str_replace("###URL###", $match[1], $output);
        return ($output);
}

function brightcove_plugin($content)
{
        return (preg_replace_callback(BRIGHTCOVE_REGEXP, 'brightcove_plugin_callback', $content));
}

add_filter('the_content', 'brightcove_plugin');
add_filter('the_content_rss', 'brightcove_plugin');
add_filter('comment_text', 'brightcove_plugin');
add_filter('the_excerpt', 'brightcove_plugin');

// Yahoo! Video code

define("YAHOO_WIDTH", 512);
define("YAHOO_HEIGHT", 322);
define("YAHOO_REGEXP", "/\[yahoo ([[:print:]]+)\]/");
define("YAHOO_TARGET", "<object width=\"###WIDTH###\" height=\"###HEIGHT###\"><param name=\"movie\" value=\"http://d.yimg.com/static.video.yahoo.com/yep/YV_YEP.swf?ver=2.2.30\" /><param name=\"allowFullScreen\" value=\"true\" /><param name=\"AllowScriptAccess\" VALUE=\"always\" /><param name=\"bgcolor\" value=\"#000000\" /><param name=\"flashVars\" value=\"id=###URL###&embed=1\" /><embed src=\"http://d.yimg.com/static.video.yahoo.com/yep/YV_YEP.swf?ver=2.2.30\" type=\"application/x-shockwave-flash\" width=\"###WIDTH###\" height=\"###HEIGHT###\" allowFullScreen=\"true\" AllowScriptAccess=\"always\" bgcolor=\"#000000\" flashVars=\"id=###URL###&embed=1\" ></embed></object>");

function yahoo_plugin_callback($match)
{
  $tag_parts = explode(" ", rtrim($match[0], "]"));
  $output = YAHOO_TARGET;
  $output = str_replace("###URL###", $tag_parts[1], $output);
  if (count($tag_parts) > 2) {
    if ($tag_parts[2] == 0) {
      $output = str_replace("###WIDTH###", YAHOO_WIDTH, $output);
    } else {
      $output = str_replace("###WIDTH###", $tag_parts[2], $output);
    }
    if ($tag_parts[3] == 0) {
      $output = str_replace("###HEIGHT###", YAHOO_HEIGHT, $output);
    } else {
      $output = str_replace("###HEIGHT###", $tag_parts[3], $output);
    }
  } else {
    $output = str_replace("###WIDTH###", YAHOO_WIDTH, $output);
    $output = str_replace("###HEIGHT###", YAHOO_HEIGHT, $output);
  }
  return ($output);
}

function yahoo_plugin($content)
{
        return (preg_replace_callback(YAHOO_REGEXP, 'yahoo_plugin_callback', $content));
}

add_filter('the_content', 'yahoo_plugin');
add_filter('the_content_rss', 'yahoo_plugin');
add_filter('comment_text', 'yahoo_plugin');
add_filter('the_excerpt', 'yahoo_plugin');

// MyspaceTV code

define("MYSPACETV_WIDTH", 425);
define("MYSPACETV_HEIGHT", 360);
define("MYSPACETV_REGEXP", "/\[myspacetv ([[:print:]]+)\]/");
define("MYSPACETV_TARGET", "<object width=\"".MYSPACETV_WIDTH."\" height=\"".MYSPACETV_HEIGHT."\"><param name=\"allowFullScreen\" value=\"true\"/><param name=\"vmode\" value=\"transparent\"></param><param name=\"movie\" value=\"http://mediaservices.myspace.com/services/media/embed.aspx/m=###URL###,t=1,mt=video\"/><embed src=\"http://mediaservices.myspace.com/services/media/embed.aspx/m=###URL####,t=1,mt=video\" width=\"".MYSPACETV_WIDTH."\" height=\"".MYSPACETV_HEIGHT."\" allowFullScreen=\"true\" type=\"application/x-shockwave-flash\" wmode=\"transparent\"></embed></object>");

function myspacetv_plugin_callback($match)
{
        $output = MYSPACETV_TARGET;
        $output = str_replace("###URL###", $match[1], $output);
        return ($output);
}

function myspacetv_plugin($content)
{
        return (preg_replace_callback(MYSPACETV_REGEXP, 'myspacetv_plugin_callback', $content));
}

add_filter('the_content', 'myspacetv_plugin');
add_filter('the_content_rss', 'myspacetv_plugin');
add_filter('comment_text', 'myspacetv_plugin');
add_filter('the_excerpt', 'myspacetv_plugin');

// Veoh code

define("VEOH_WIDTH", 410);
define("VEOH_HEIGHT", 341);
define("VEOH_REGEXP", "/\[veoh ([[:print:]]+)\]/");
define("VEOH_TARGET", "<embed src=\"http://www.veoh.com/veohplayer.swf?permalinkId=###URL###&id=anonymous&player=videodetailsembedded&videoAutoPlay=0\" allowFullScreen=\"true\" width=\"".VEOH_WIDTH."\" height=\"".VEOH_HEIGHT."\" bgcolor=\"#FFFFFF\" type=\"application/x-shockwave-flash\" pluginspage=\"http://www.macromedia.com/go/getflashplayer\"></embed>");

function veoh_plugin_callback($match)
{
        $output = VEOH_TARGET;
        $output = str_replace("###URL###", $match[1], $output);
        return ($output);
}

function veoh_plugin($content)
{
        return (preg_replace_callback(VEOH_REGEXP, 'veoh_plugin_callback', $content));
}

add_filter('the_content', 'veoh_plugin');
add_filter('the_content_rss', 'veoh_plugin');
add_filter('comment_text', 'veoh_plugin');
add_filter('the_excerpt', 'veoh_plugin');

// blip.tv Code

define("BLIPTV_WIDTH", 400);
define("BLIPTV_HEIGHT", 294);
define("BLIPTV_REGEXP", "/\[bliptv ([[:print:]]+)\]/");
define("BLIPTV_TARGET", "<embed src=\"http://blip.tv/play/###URL###\" type=\"application/x-shockwave-flash\" width=\"".BLIPTV_WIDTH."\" height=\"".BLIPTV_HEIGHT."\" allowscriptaccess=\"always\" allowfullscreen=\"true\"></embed>");

function bliptv_plugin_callback($match) {
  $output = BLIPTV_TARGET;
  $output = str_replace("###URL###", $match[1], $output);
  return ($output);
}

function bliptv_plugin($content) {
  return preg_replace_callback(BLIPTV_REGEXP, 'bliptv_plugin_callback', $content);
}

add_filter('the_content', 'bliptv_plugin');
add_filter('the_content_rss', 'bliptv_plugin');
add_filter('comment_text', 'bliptv_plugin');
add_filter('the_excerpt', 'bliptv_plugin');

// Videotube Code

define("VIDEOTUBE_WIDTH", 480);
define("VIDEOTUBE_HEIGHT", 400);
define("VIDEOTUBE_REGEXP", "/\[videotube ([[:print:]]+)\]/");
define("VIDEOTUBE_TARGET", "<object type=\"application/x-shockwave-flash\" data=\"http://www.videotube.de/ci/flash/videotube_player_4.swf?videoId=###URL###&svsf=0&lang=german&host=www.videotube.de\" width=\"".VIDEOTUBE_WIDTH."\" height=\"".VIDEOTUBE_HEIGHT."\" wmode=\"transparent\"><param name=\"movie\" value=\"http://www.videotube.de/ci/flash/videotube_player_4.swf?videoId=###URL###&svsf=0&lang=german&host=www.videotube.de\" /></object>");

function videotube_plugin_callback($match) {
  $output = VIDEOTUBE_TARGET;
  $output = str_replace("###URL###", $match[1], $output);
  return ($output);
}

function videotube_plugin($content) {
  return preg_replace_callback(VIDEOTUBE_REGEXP, 'videotube_plugin_callback', $content);
}

add_filter('the_content', 'videotube_plugin');
add_filter('the_content_rss', 'videotube_plugin');
add_filter('comment_text', 'videotube_plugin');
add_filter('the_excerpt', 'videotube_plugin');

// Vimeo Code

define("VIMEO_WIDTH", 400); // default width
define("VIMEO_HEIGHT", 225); // default height
define("VIMEO_REGEXP", "/\[vimeo ([[:print:]]+)\]/");
define("VIMEO_TARGET", "<iframe src=\"http://player.vimeo.com/video/###URL###\" width=\"###WIDTH###\" height=\"###HEIGHT###\" frameborder=\"0\"></iframe>");

function vimeo_plugin_callback($match)
{
  $tag_parts = explode(" ", rtrim($match[0], "]"));
  $output = VIMEO_TARGET;
  $output = str_replace("###URL###", $tag_parts[1], $output);
  if (count($tag_parts) > 2) {
    if ($tag_parts[2] == 0) {
      $output = str_replace("###WIDTH###", VIMEO_WIDTH, $output);
    } else {
      $output = str_replace("###WIDTH###", $tag_parts[2], $output);
    }
    if ($tag_parts[3] == 0) {
      $output = str_replace("###HEIGHT###", VIMEO_HEIGHT, $output);
    } else {
      $output = str_replace("###HEIGHT###", $tag_parts[3], $output);
    }
  } else {
    $output = str_replace("###WIDTH###", VIMEO_WIDTH, $output);
    $output = str_replace("###HEIGHT###", VIMEO_HEIGHT, $output);
  }
  return ($output);
}
function vimeo_plugin($content)
{
  return (preg_replace_callback(VIMEO_REGEXP, 'vimeo_plugin_callback', $content));
}

add_filter('the_content', 'vimeo_plugin');
add_filter('the_content_rss', 'vimeo_plugin');
add_filter('comment_text', 'vimeo_plugin');
add_filter('the_excerpt', 'vimeo_plugin');

// Metacafe Code

define("METACAFE_WIDTH", 400);
define("METACAFE_HEIGHT", 345);
define("METACAFE_REGEXP", "/\[metacafe ([[:print:]]+)\]/");
define("METACAFE_TARGET", "<object type=\"application/x-shockwave-flash\" data=\"http://www.metacafe.com/fplayer/###URL###/.swf\" width=\"".METACAFE_WIDTH."\" height=\"".METACAFE_HEIGHT."\" wmode=\"transparent\"><param name=\"movie\" value=\"http://www.metacafe.com/fplayer/###URL###/.swf\" /></object>");

function metacafe_plugin_callback($match) {
  $output = METACAFE_TARGET;
  $output = str_replace("###URL###", $match[1], $output);
  return ($output);
}

function metacafe_plugin($content) {
  return preg_replace_callback(METACAFE_REGEXP, 'metacafe_plugin_callback', $content);
}

add_filter('the_content', 'metacafe_plugin');
add_filter('the_content_rss', 'metacafe_plugin');
add_filter('comment_text', 'metacafe_plugin');
add_filter('the_excerpt', 'metacafe_plugin');

// Break.com Codes

define("BREAK_WIDTH", 425);
define("BREAK_HEIGHT", 350);
define("BREAK_REGEXP", "/\[break ([[:print:]]+)\]/");
define("BREAK_TARGET", "<object type=\"application/x-shockwave-flash\" data=\"http://embed.break.com/###URL###\" width=\"".BREAK_WIDTH."\" height=\"".BREAK_HEIGHT."\" wmode=\"transparent\"><param name=\"movie\" value=\"http://embed.break.com/###URL###\" /></object>");

function break_plugin_callback($match) {
  $output = BREAK_TARGET;
  $output = str_replace("###URL###", $match[1], $output);
  return ($output);
}

function break_plugin($content) {
  return preg_replace_callback(BREAK_REGEXP, 'break_plugin_callback', $content);
}

add_filter('the_content', 'break_plugin');
add_filter('the_content_rss', 'break_plugin');
add_filter('comment_text', 'break_plugin');
add_filter('the_excerpt', 'break_plugin');

// MyVideo Code

define("MYVIDEO_WIDTH", 470);
define("MYVIDEO_HEIGHT", 406);
define("MYVIDEO_REGEXP", "/\[myvideo ([[:print:]]+)\]/");
define("MYVIDEO_TARGET", "<object style=\"width:###WIDTH###px;height:###HEIGHT###px;\" type=\"application/x-shockwave-flash\" data=\"http://www.myvideo.de/movie/###URL###\"> <param name=\"movie\" value=\"http://www.myvideo.de/movie/###URL###\" />  <param name=\"AllowFullscreen\" value=\"true\" /> </object>");

function myvideo_plugin_callback($match)
{
  $tag_parts = explode(" ", rtrim($match[0], "]"));
  $output = MYVIDEO_TARGET;
  $output = str_replace("###URL###", $tag_parts[1], $output);
  if (count($tag_parts) > 2) {
    if ($tag_parts[2] == 0) {
      $output = str_replace("###WIDTH###", MYVIDEO_WIDTH, $output);
    } else {
      $output = str_replace("###WIDTH###", $tag_parts[2], $output);
    }
    if ($tag_parts[3] == 0) {
      $output = str_replace("###HEIGHT###", MYVIDEO_HEIGHT, $output);
    } else {
      $output = str_replace("###HEIGHT###", $tag_parts[3], $output);
    }
  } else {
    $output = str_replace("###WIDTH###", MYVIDEO_WIDTH, $output);
    $output = str_replace("###HEIGHT###", MYVIDEO_HEIGHT, $output);
  }
  return ($output);
}
function myvideo_plugin($content)
{
  return (preg_replace_callback(MYVIDEO_REGEXP, 'myvideo_plugin_callback', $content));
}

add_filter('the_content', 'myvideo_plugin');
add_filter('the_content_rss', 'myvideo_plugin');
add_filter('comment_text', 'myvideo_plugin');
add_filter('the_excerpt', 'myvideo_plugin');

// Dailymotion Code

define("DAILYMOTION_WIDTH", 420);
define("DAILYMOTION_HEIGHT", 336);
define("DAILYMOTION_REGEXP", "/\[dailymotion[:\s]([[:print:]]+)\]/");
define("DAILYMOTION_TARGET", "<object width=\"###WIDTH###\" height=\"###HEIGHT###\"><param name=\"movie\" value=\"http://www.dailymotion.com/swf/###URL###\" /><param name=\"allowFullScreen\" value=\"true\" /><param name=\"allowScriptAccess\" value=\"always\" /><embed src=\"http://www.dailymotion.com/swf/###URL###\" allowscriptaccess=\"always\" allowfullscreen=\"true\" width=\"###WIDTH###\" height=\"###HEIGHT###\"></embed></object>");

function dailymotion_plugin_callback($match) {
  $tag_parts = explode(" ", rtrim($match[0], "]"));
  $replacements = array(
    "###URL###" => preg_match('!/video/(.*?)(_|\s|$)!', $tag_parts[1], $m) ? $m[1] : $tag_parts[1],
    "###WIDTH###" => isset($tag_parts[2]) && $tag_parts[2] ? $tag_parts[2] : DAILYMOTION_WIDTH,
    "###HEIGHT###" => isset($tag_parts[3]) && $tag_parts[3] ? $tag_parts[3] : DAILYMOTION_WIDTH,
  );
  return str_replace(array_keys($replacements), array_values($replacements), DAILYMOTION_TARGET);
}

function dailymotion_plugin($content) {
  return preg_replace_callback(DAILYMOTION_REGEXP, 'dailymotion_plugin_callback', $content);
}

add_filter('the_content', 'dailymotion_plugin');
add_filter('the_content_rss', 'dailymotion_plugin');
add_filter('comment_text', 'dailymotion_plugin');
add_filter('the_excerpt', 'dailymotion_plugin');

// GoogleVideo Code

define("GOOGLE_WIDTH", 425);
define("GOOGLE_HEIGHT", 350);
define("GOOGLE_REGEXP", "/\[google ([[:print:]]+)\]/");
define("GOOGLE_TARGET", "<object type=\"application/x-shockwave-flash\" data=\"http://video.google.com/googleplayer.swf?docId=###URL###\" width=\"".GOOGLE_WIDTH."\" height=\"".GOOGLE_HEIGHT."\" wmode=\"transparent\"><param name=\"movie\" value=\"http://video.google.com/googleplayer.swf?docId=###URL###\" /></object>");

function google_plugin_callback($match) {
  $output = GOOGLE_TARGET;
  $output = str_replace("###URL###", $match[1], $output);
  return ($output);
}

function google_plugin($content) {
  return preg_replace_callback(GOOGLE_REGEXP, 'google_plugin_callback', $content);
}

add_filter('the_content', 'google_plugin');
add_filter('the_content_rss', 'google_plugin');
add_filter('comment_text', 'google_plugin');
add_filter('the_excerpt', 'google_plugin');


// Youtube Code

define("YOUTUBE_WIDTH", 425); // default width
define("YOUTUBE_HEIGHT", 344); // default height
  // define("YOUTUBE_REGEXP", "/\[youtube ([[:print:]]+)\]/");
  define("YOUTUBE_REGEXP", "/https?\:\/\/\S+?youtube\S+\?(\S+)/");
  define("YOUTUBE_TARGET", "<object width=\"###WIDTH###\" height=\"###HEIGHT###\" type=\"application/x-shockwave-flash\" data=\"http://www.youtube.com/v/###URL###&amp;hl=de&amp;fs=1&amp;rel=0\"><param name=\"movie\"  value=\"http://www.youtube.com/v/###URL###&amp;hl=de&amp;fs=1&amp;rel=0\"></param><param name=\"allowFullScreen\" value=\"true\"></param><param name=\"allowscriptaccess\" value=\"always\"></param></object>");

  function youtube_plugin_callback($match)
  {
    parse_str($match[1], $myarr);
//    echo $myarr['v'];
    $output = YOUTUBE_TARGET;
    $output = str_replace("###URL###", $myarr['v'], $output);
    $output = str_replace("###WIDTH###", YOUTUBE_WIDTH, $output);
    $output = str_replace("###HEIGHT###", YOUTUBE_HEIGHT, $output);
    return ($output);
  }
  function youtube_plugin($content)
  {
    return (preg_replace_callback(YOUTUBE_REGEXP, 'youtube_plugin_callback', $content));
  }

 // youtube_plugin('http://www.youtube.com/watch?v=TpEctXsTDQg')

add_filter('the_content', 'youtube_plugin',1);
add_filter('the_content_rss', 'youtube_plugin');
add_filter('comment_text', 'youtube_plugin');
add_filter('the_excerpt', 'youtube_plugin');

?>