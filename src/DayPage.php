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

/*
   This file has the functions for the day displays of the calendar
*/

namespace PhpCalendar;

class DayPage extends Page
{
	// View for a single day
	function display(Context $context)
	{
		$year = $context->year;
		$month = $context->month;
		$day = $context->day;

		$monthname = month_name($month);

		$today_epoch = mktime(0, 0, 0, $month, $day, $year);

		$have_events = false;

		$html_table = tag('table', attributes('class="phpc-main"'),
				tag('caption', "$day $monthname $year"),
				tag('thead',
					tag('tr',
						tag('th', __('Title')),
						tag('th', __('Time')),
						tag('th', __('Description'))
					   )));
		if($context->calendar->can_modify($context->user)) {
			$html_table->add(tag('tfoot',
						tag('tr',
							tag('td',
								attributes('colspan="4"'),
								create_hidden('action', 'event_delete'),
								create_hidden('day', $day),
								create_hidden('month', $month),
								create_hidden('year', $year),
								create_submit(__('Delete Selected'))))));
		}

		$html_body = tag('tbody');

		$results = $context->db->get_occurrences_by_date($context->calendar->cid, $year, $month, $day);
		while($row = $results->fetch_assoc()) {

			$event = new Occurrence($context, $row);

			if(!$event->can_read($context->user))
				continue;

			$have_events = true;

			$eid = $event->get_eid();
			$oid = $event->get_oid();

			$html_subject = tag('td');

			if($event->can_modify($context->user)) {
				$html_subject->add(create_checkbox('eid[]', $eid));
			}

			$html_subject->add(create_event_link(tag('strong', $event->get_subject()),
						'display_event', $eid));

			if($event->can_modify($context->user)) {
				$html_subject->add(create_event_link(__(' (Modify)'), 'event_form', $eid));
			}

			$html_body->add(tag('tr',
						$html_subject,
						tag('td', $event->get_time_span_string()),
						tag('td', $event->get_desc())));
		}

		$html_table->add($html_body);

		if($context->calendar->can_modify($context->user)) {
			$output = tag('form', attrs('action="' . PHPC_SCRIPT . '"', 'class="phpc-form-confirm"', 'method="post"'),
					$html_table);
		} else {
			$output = $html_table;
		}

		if(!$have_events)
			$output = tag('h2', __('No events on this day.'));

		$dialog = tag('div', attrs('id="phpc-dialog"', 'title="' . __("Confirmation required") . '"'),
				__("Permanently delete the selected events?"));

		return tag('', create_day_menu($context, $year, $month, $day), $dialog, $output);
	}
}

function create_day_menu(Context $context, $year, $month, $day)
{
	$html = tag('div', attrs('class="phpc-bar ui-widget-content"'));

	$monthname = month_name($month);

	$lasttime = mktime(0, 0, 0, $month, $day - 1, $year);
	$lastday = date('j', $lasttime);
	$lastmonth = date('n', $lasttime);
	$lastyear = date('Y', $lasttime);
	$lastmonthname = month_name($lastmonth);

	$last_args = array('year' => $lastyear, 'month' => $lastmonth,
			'day' => $lastday);

	menu_item_prepend($context, $html, "$lastmonthname $lastday", 'display_day', $last_args);

	$nexttime = mktime(0, 0, 0, $month, $day + 1, $year);
	$nextday = date('j', $nexttime);
	$nextmonth = date('n', $nexttime);
	$nextyear = date('Y', $nexttime);
	$nextmonthname = month_name($nextmonth);

	$next_args = array('year' => $nextyear, 'month' => $nextmonth,
			'day' => $nextday);

	menu_item_append($context, $html, "$nextmonthname $nextday", 'display_day', $next_args);

	return $html;
}

?>
