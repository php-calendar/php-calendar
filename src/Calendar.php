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

namespace PhpCalendar;

class Calendar {
	var $cid;
	var $title;
	var $user_perms = array();
	var $categories;
	var $hours_24;
	var $date_format;
	var $week_start;
	var $subject_max;
	var $events_max;
	var $anon_permission;
	var $timezone;
	var $language;
	var $theme;
	var $groups;
	var $fields;
	var $db;

	function __construct(Database $db, $result) {
		$this->db = $db;

		$this->cid = $result['cid'];
		$this->title = $result['title'];
		$this->hours_24 = $result['hours_24'];
		$this->date_format = $result['date_format'];
		$this->week_start = $result['week_start'];
		$this->subject_max = $result['subject_max'];
		$this->events_max = $result['events_max'];
		$this->anon_permission = $result['anon_permission'];
		$this->timezone = $result['timezone'];
		$this->language = $result['language'];
		$this->theme = $result['theme'];
	}

	function get_title()
	{
		if(empty($this->title))
			return __('(No title)');

		return htmlspecialchars($this->title);
	}

	function get_cid()
	{
		return $this->cid;
	}

	function get_user_perm($uid, $perm)
	{
		if(!isset($this->user_perms[$uid]))
			$this->user_perms[$uid] = $this->db->get_permissions($this->cid, $uid);

		return !empty($this->user_perms[$uid][$perm]);
	}

	function can_read(User $user)
	{
		if ($this->anon_permission >= 1)
			return true;

		if (!$user->is_user())
			return false;

		return $this->can_admin($user) || $this->get_user_perm($user->uid, 'read');
	}

	function can_write(User $user)
	{
		if ($this->anon_permission >= 2)
			return true;

		if (!$user->is_user())
			return false;

		return $this->can_admin($user) || $this->get_user_perm($user->uid, 'write');
	}

	function can_admin(User $user)
	{
		if (!$user->is_user())
			return false;

		return $user->is_admin() || $this->get_user_perm($user->uid, 'admin');
	}

	function can_modify(User $user)
	{
		if ($this->anon_permission >= 3)
			return true;

		if (!$user->is_user())
			return false;

		return $this->can_admin($user) || $this->get_user_perm($user->uid, 'modify');
	}

	function can_create_readonly(User $user)
	{
		if (!$user->is_user())
			return false;

		return $this->can_admin($user) || $this->get_user_perm($user->uid, 'readonly');
	}

	function get_visible_categories($uid) {
		return $this->db->get_visible_categories($uid, $this->cid);
	}
		
	function get_categories() {
		if(!isset($this->categories)) {
			$this->categories = $this->db->get_categories($this->cid);
		}
		return $this->categories;
	}

	function get_groups() {
		if(!isset($this->groups)) {
			$this->groups = $this->db->get_groups($this->cid);
		}
		return $this->groups;
	}

	function get_field($fid) {
		if(!isset($this->fields)) {
			$this->fields = $this->db->get_fields($this->cid);
		}
		return $this->fields[$fid];
	}
}

?>
