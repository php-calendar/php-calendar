<?php
/*
 * Copyright 2012 Sean Proctor
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

/*
   This file sets up the global variables to be used later
*/

namespace PhpCalendar;

if ( !defined('IN_PHPC') ) {
       die("Hacking attempt");
}

// Displayed in admin
$version = "2.1";

// Run the installer if we have no config file
// This doesn't work when embedded from outside
if(!file_exists(PHPC_ROOT_PATH . '/config.php')) {
        redirect('install.php');
        exit;
}
require_once(PHPC_ROOT_PATH . '/config.php');

if(!defined('SQL_TYPE')) {
        redirect('install.php');
        exit;
}

ini_set('arg_separator.output', '&amp;');
mb_internal_encoding('UTF-8');
mb_http_output('pass');

if(defined('PHPC_DEBUG')) {
	error_reporting(E_ALL);
	ini_set('display_errors', 1);
	ini_set('html_errors', 1);
}

$prefix = "phpc_" . SQL_PREFIX . SQL_DATABASE;

$title = "";

session_start();

$context = new Context();

require_once(PHPC_ROOT_PATH . '/src/schema.php');
if ($context->db->get_config('version') < PHPC_DB_VERSION) {
	if(isset($_GET['update'])) {
		phpc_updatedb($context);
	} else {
		print_update_form();
	}
	exit;
}

if(empty($_SESSION["{$prefix}uid"])) {
	if(!empty($_COOKIE["{$prefix}login"])
			&& !empty($_COOKIE["{$prefix}uid"])
			&& !empty($_COOKIE["{$prefix}login_series"])) {
		// Cleanup before we check their token so they can't login with
		//   an ancient token
		$context->db->cleanup_login_tokens();

	// FIXME should this be _SESSION below?
		$uid = $_COOKIE["{$prefix}uid"];
		$login_series = $_COOKIE["{$prefix}login_series"];
		$token = $context->db->get_login_token($uid, $login_series);
		if($token) {
			if($token == $_COOKIE["{$prefix}login"]) {
				$user = $context->db->get_user($uid);
				phpc_do_login($context, $user, $login_series);
			} else {
				$context->db->remove_login_tokens($uid);
				soft_error(__("Possible hacking attempt on your account."));
			}
		} else {
			$uid = 0;
		}
	}
} else {
	$token = $_SESSION["{$prefix}login"];
}

if(empty($token))
	$token = '';

if(empty($vars['content']))
	$vars['content'] = "html";

// setup translation stuff
if(!empty($vars['lang'])) {
	$lang = $vars['lang'];
} elseif(!empty($user_lang)) {
	$lang = $user_lang;
} elseif(!empty($cal->language)) {
	$lang = $cal->language;
} elseif(!empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
	$lang = substr(htmlentities($_SERVER['HTTP_ACCEPT_LANGUAGE']),
			0, 2);
} else {
	$lang = 'en';
}

// Require a 2 letter language
if(!preg_match('/^\w+$/', $lang, $matches))
	$lang = 'en';

if(!empty($vars['clearmsg']))
	$_SESSION["{$prefix}messages"] = NULL;

$messages = array();

if(!empty($_SESSION["{$prefix}messages"])) {
	foreach($_SESSION["{$prefix}messages"] as $message) {
		$messages[] = $message;
	}
}

?>
