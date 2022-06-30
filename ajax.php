<?php

/**

Returns the markup for a single week row of the calendar.

When the calendar first loads, it makes 5 calls to this ajax, thus filling up the calendar.

Then each time the user scrolls down, another call is made here to add one more week row to the calendar

**/


$initializing = false;
$last_retrieved = $_REQUEST['last_retrieved'];

if ($last_retrieved){
	if (
		preg_match("#[^\d]#", $last_retrieved)
		|| $last_retrieved < time()
		|| $last_retrieved > time() + (365 * 3600 * 24)
	){
		print json_encode(array(
			"success" => false,
			"error_msg" => "Invalid params"
		));
		exit;
	}

	$weeks = array(
		strtotime(date("Y-m-d", $last_retrieved) . " + 7 days")
	);
}
else {
	$initializing = true;
	$this_week = date("Y-m-d", strtotime("Monday this week"));
	$weeks = array();
	for ($w = 0; $w < 6; $w++){
		if ($w === 0){
			$d = strtotime($this_week);
		}
		else {
			$d = strtotime($this_week . " + " . (7 * $w) . " days");
		}
		$weeks[] = $d;
	}
}

$today_ts = strtotime(date("Y-m-d"));

// this markup will be a row containing one week of the calendar;
// the week sent back is determined by the param last_retrieved sent from the javascript.
// the js always asks for the next week that it hasnt gotten yet.
$markup = "";

foreach ($weeks as $w_i => $week_ts){

	$markup .= '<div class="week_row">';

	for ($day = 0; $day < 7; $day++){

		$day_ts = strtotime(date("Y-m-d", $week_ts) . " + " . $day . " days", $week_ts);
		$dom = date("j", $day_ts);
		$mon = "";
		if (
			$dom == 1
			|| ($initializing && $w_i == 0 && $day == 0)
		){
			$mon = date("M", $day_ts);
		}

		$markup .= '<div';
		$markup .= ' class="day_cell' . ($day === 0 ? " first_day_cell" : "") . '"';
		$markup .= ' style="left: ' . (14.2857 * $day) . '%"';
		$markup .= '>';
			if ($mon){
				$markup .= '<span class="month_label">' . $mon . '</span>';
			}
			$markup .= '<span class="day_label">' . $dom . '</span>';

		$markup .= "</div>";
	}

	for ($day = 0; $day < 7; $day++){

		$day_ts = strtotime(date("Y-m-d", $week_ts) . " + " . $day . " days", $week_ts);
		$day_str = date("Y-m-d", $day_ts);

		if ($day_ts <= $today_ts){
			continue;
		}


		// first half of day:
		
		$available = true;
		// or if this half of the day is blocked, $available = false;

		// green or red block in first half of day
		$left = ((100 / 7) * $day);
		if ($available){
			// green block / available
			// links to the booking website with this boat and date pre-selected
			$markup .= '<a href="/?bid=XXXX&date=' . urlencode($day_str) . '"';
			$markup .= ' target="motorboatinbooking"';
			$markup .= ' class="booking_block available_block"';
			$markup .= ' style="left: ' . $left . '%"';
			$markup .= ' data-date="' . $day_str . '-am"';
			$markup .= '>';

			$markup .= "</a>";
		}
		else {
			// red block / not available
			$markup .= '<div class="booking_block unavailable_block" style="left: ' . $left . '%">';
			$markup .= "</div>";
		}
		

		// second half of day:
		
		$available = true;
		// or if this half of the day is blocked, $available = false;

		// green or red block in second half of day
		$left = ((100 / 7) * $day) + (100 / 14);
		if ($available){
			// green block / available
			// links to the booking website with this boat and date pre-selected
			$markup .= '<a href="/?bid=XXXX&date=' . urlencode($day_str) . '"';
			$markup .= ' target="motorboatinbooking"';
			$markup .= ' class="booking_block available_block"';
			$markup .= ' style="left: ' . $left . '%"';
			$markup .= ' data-date="' . $day_str . '-pm"';
			$markup .= '>';
			$markup .= "</a>";
		}
		else {
			// red block / not available
			$markup .= '<div class="booking_block unavailable_block" style="left: ' . $left . '%">';
			$markup .= "</div>";
		}

	}

	$markup .= "</div>"; // end week_row

}

header("Content-type: application/json");

print json_encode(array(
	
	"success" => true,
	
	// tells the javascript how many weeks have been retrieved, so it knows on the next call to fetch the next week
	"last_retrieved" => $weeks[count($weeks) - 1],

	// the actual week's markup row to add the calendar
	"markup" => $markup
));

