<?php
/**
 * Common functions for Phone Rental System
 */

/**
 * Returns a human-readable string representing the time elapsed since a given datetime.
 *
 * @param string|null $datetime
 * @param bool $full
 * @return string
 */
function time_elapsed_string($datetime, $full = false) {
    if ($datetime == null) return "Never";

    try {
        $now = new DateTime();
        $ago = new DateTime($datetime);
    } catch (Exception $e) {
        return "Invalid date";
    }

    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    );
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}

/**
 * Checks if a device is online based on its last_seen timestamp.
 *
 * @param string|null $last_seen
 * @param int $minutes Threshold in minutes (default 5)
 * @return bool
 */
function is_online($last_seen, $minutes = 5) {
    if ($last_seen == null) return false;

    try {
        $last_seen_time = new DateTime($last_seen);
        $now = new DateTime();
        $interval = $now->getTimestamp() - $last_seen_time->getTimestamp();

        return $interval <= ($minutes * 60);
    } catch (Exception $e) {
        return false;
    }
}
