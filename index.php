<?php
/*
 * Copyright 2016 Sean Proctor
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace PhpCalendar;

require_once 'vendor/autoload.php';

/*
 * The following variables are intended to be modified to fit your
 * setup.
 */

/*
 * $root_path gives the location of the base calendar install.
 * if you move this file to a new location, modify $root_path to point
 * to the location where the support files for the callendar are located.
 */
if(!defined('PHPC_ROOT_PATH'))
	define('PHPC_ROOT_PATH', dirname(__FILE__));

// path of index.php. ex. /php-calendar/index.php
if(!defined('PHPC_SCRIPT'))
	define('PHPC_SCRIPT', htmlentities($_SERVER['SCRIPT_NAME']));
if(!defined('PHPC_URL_PATH'))
	define('PHPC_URL_PATH', dirname($_SERVER['SCRIPT_NAME']));

// Port
if(!defined('PHPC_PORT'))
	define('PHPC_PORT', empty($_SERVER["SERVER_PORT"]) || $_SERVER["SERVER_PORT"] == 80 ? ""
			: ":{$_SERVER["SERVER_PORT"]}");

// ex. www.php-calendar.com
if(!defined('PHPC_SERVER'))
	define('PHPC_SERVER', $_SERVER['SERVER_NAME'] . $port);

// Protcol ex. http or https
if(!defined('PHPC_PROTOCOL'))
	define('PHPC_PROTOCOL', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off'
		|| $_SERVER['SERVER_PORT'] == 443
		|| isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https'
		|| isset($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on'
		?  "https"
		: "http");

if(!defined('PHPC_HOME_URL'))
	define('PHPC_HOME_URL', PHPC_PROTOCOL . '://' . PHPC_SERVER . PHPC_SCRIPT);
$url = $home_url . (empty($_SERVER['QUERY_STRING']) ? ''
		: '?' . $_SERVER['QUERY_STRING']);

$min = defined('PHPC_DEBUG') ? '' : '.min';

$theme = $context->calendar->theme;
if(empty($theme))
	$theme = 'smoothness';
$jquery_version = "1.11.1";
$jqueryui_version = "1.11.2";
$fa_version = "4.2.0";

if(!isset($jqui_path))
	$jqui_path = "//ajax.googleapis.com/ajax/libs/jqueryui/$jqueryui_version";
if(!isset($fa_path))
	$fa_path = "//maxcdn.bootstrapcdn.com/font-awesome/$fa_version";
if(!isset($jq_file))
	$jq_file = "//ajax.googleapis.com/ajax/libs/jquery/$jquery_version/jquery$min.js";

/*
 * Do not modify anything under this point
 */
define('IN_PHPC', true);

require_once(PHPC_ROOT_PATH . '/src/helpers.php');
try {
	require_once(PHPC_ROOT_PATH . '/src/setup.php');
} catch(\Exception $e) {
	header("Content-Type: text/html; charset=UTF-8");
	echo "<!DOCTYPE html>\n";
	echo display_exception($e)->toString();
	exit;
}

use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\MessageSelector;
use Symfony\Component\Translation\Loader\MoFileLoader;

if($lang != 'en') {
	$translator = new Translator($lang, new MessageSelector());
	$translator->addLoader('mo', new MoFileLoader());
	$translator->addResource('mo', "$locale_path/$lang/LC_MESSAGES/messages.mo", $lang);
}

if ($vars["content"] == "json") {
	header("Content-Type: application/json; charset=UTF-8");
	echo do_action();
} else {
	header("Content-Type: text/html; charset=UTF-8");

	// This sets global variables that determine the title in the header
	$content = display_phpc($context);
	$embed_script = '';
	if($vars["content"] == "embed") {
		$underscore_version = "1.5.2";
		$embed_script = array(tag("script",
					attrs('src="//cdnjs.cloudflare.com/ajax/libs/underscore.js/'
						."$underscore_version/underscore-min.js\""), ''),
				tag('script', attrs('src="' . PHPC_URL_PATH . '/static/embed.js"'), ''));
	}

	$html = tag('html', attrs("lang=\"$lang\""),
			tag('head',
				tag('title', $context->calendar->get_title()),
				tag('link', attrs('rel="icon"',
						'href="' . PHPC_URL_PATH . '/static/office-calendar.png"')),
				tag('meta', attrs('http-equiv="Content-Type"',
						'content="text/html; charset=UTF-8"')),
				tag('link', attrs('rel="stylesheet"', 'href="' . PHPC_URL_PATH . '/static/phpc.css"')),
				tag('link', attrs('rel="stylesheet"', "href=\"$jqui_path/themes/$theme/jquery-ui$min.css\"")),
				tag('link', attrs('rel="stylesheet"', 'href="' . PHPC_URL_PATH
						. '/static/jquery-ui-timepicker.css"')),
				tag('link', attrs('rel="stylesheet"', "href=\"$fa_path/css/font-awesome$min.css\"")),
				tag("script", attrs("src=\"$jq_file\""), ''),
				tag("script", attrs("src=\"$jqui_path/jquery-ui$min.js\""), ''),
				tag('script', attrs('src="' . PHPC_URL_PATH . '/static/phpc.js"'), ''),
				tag("script", attrs('src="' . PHPC_URL_PATH . '/static/jquery.ui.timepicker.js"'), ''),
				tag("script", attrs('src="' . PHPC_URL_PATH . '/static/farbtastic.min.js"'), ''),
				tag('link', attrs('rel="stylesheet"', 'href="' . PHPC_URL_PATH . '/static/farbtastic.css"'))
			),
			tag('body', $embed_script, $content));

	echo "<!DOCTYPE html>\n", $html->toString();
}
?>
