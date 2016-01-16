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

class Context {
	var $calendar;
	var $user;
	var $messages;
	var $action;
	var $tz;
	var $year;
	var $month;
	var $day;
	var $db;

	function __construct() {
		$this->db = new Database();

		$this->action = empty($_REQUEST['action']) ? 'display_month' : $_REQUEST['action'];

		// Find current user
		if(!empty($_SESSION[PHPC_PREFIX . 'uid'])) {
			$this->user = $this->db->get_user($_SESSION[PHPC_PREFIX . 'uid']);
		} else {
			$anonymous = array('uid' => 0,
					'username' => 'anonymous',
					'password' => '',
					'admin' => false,
					'password_editable' => false,
					'default_cid' => NULL,
					'timezone' => NULL,
					'language' => NULL,
					'disabled' => 0,
					);
			if(isset($_REQUEST[PHPC_PREFIX . 'tz'])) {
				$tz = $_COOKIE[$_REQUEST[PHPC_PREFIX . "tz"]];
				// If we have a timezone, make sure it's valid
				if(in_array($tz, timezone_identifiers_list())) {
					$anonymous['timezone'] = $tz;
				} else {
					$anonymous['timezone'] = '';
				}
			}
			if(isset($_REQUEST[PHPC_PREFIX . 'lang']))
				$anonymous['language'] = $_REQUEST[PHPC_PREFIX . 'lang'];
			$this->user = new User($this->db, $anonymous);
		}

		// Find current callendar
		if(!empty($_REQUEST['phpcid'])) {
			if(!is_numeric($_REQUEST['phpcid']))
				soft_error(__("Invalid calendar ID."));
			$cid = $_REQUEST['phpcid'];
		} elseif(!empty($_REQUEST['eid'])) {
			if(is_array($_REQUEST['eid'])) {
				$eid = $_REQUEST['eid'][0];
			} else {
				$eid = $_REQUEST['eid'];
			}
			$event = $this->db->get_event_by_eid($eid);
			if(empty($event))
				soft_error(__("Invalid event ID."));

			$cid = $event['cid'];
		} elseif(!empty($_REQUEST['oid'])) {
			$event = $this->db->get_event_by_oid($_REQUEST['oid']);
			if(empty($event))
				soft_error(__("Invalid occurrence ID."));

			$cid = $event['cid'];
		} else {
			$calendars = $this->db->get_calendars();
			if(empty($calendars)) {
				// TODO: create a page to fix this
				soft_error("There are no calendars.");
			} else {
				if ($this->user->get_default_cid() !== false)
					$default_cid = $this->user->get_default_cid();
				else
					$default_cid = $this->db->get_config('default_cid');
				if (!empty($calendars[$default_cid]))
					$cid = $default_cid;
				else
					$cid = reset($calendars)->get_cid();
			}
		}

		$this->calendar = $this->db->get_calendar($cid);
		if(empty($this->calendar))
			soft_error(__("Bad calendar ID."));

		$messages = array();

		// Set timezone
		if(!empty($this->user->tz))
			$tz = $this->user->timezone;
		else
			$tz = $this->calendar->timezone;

		if(!empty($tz))
			date_default_timezone_set($tz); 
		$tz = date_default_timezone_get();

		// set day/month/year - This needs to be done after the timezone is set.
		if(isset($_REQUEST['month']) && is_numeric($_REQUEST['month'])) {
			$this->month = $_REQUEST['month'];
			if($this->month < 1 || $this->month > 12)
				soft_error(__("Month is out of range."));
		} else {
			$this->month = date('n');
		}

		if(isset($_REQUEST['year']) && is_numeric($_REQUEST['year'])) {
			$time = mktime(0, 0, 0, $this->month, 1, $_REQUEST['year']);
			if(!$time || $time < 0) {
				soft_error(__('Invalid year') . ": {$_REQUEST['year']}");
			}
			$this->year = date('Y', $time);
		} else {
			$this->year = date('Y');
		}

		if(isset($_REQUEST['day']) && is_numeric($_REQUEST['day'])) {
			$this->day = ($_REQUEST['day'] - 1) % date('t', mktime(0, 0, 0, $this->month, 1, $this->year)) + 1;
		} else {
			if($this->month == date('n') && $this->year == date('Y')) {
				$this->day = date('j');
			} else {
				$this->day = 1;
			}
		}

	}

	function add_message($message) {
		$this->messages[] = $message;
	}
}

?>
