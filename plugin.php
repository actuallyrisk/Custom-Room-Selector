<?php
/*
Plugin Name: Custom Room Selection
Description: Custom plugin for room selection in WordPress
Version: 1.1
Author: Tomislav Zecevic
*/

// Include necessary files
require_once plugin_dir_path(__FILE__) . 'includes/enqueue-scripts.php';
require_once plugin_dir_path(__FILE__) . 'includes/helpers.php';

// Load ignored users from JSON file
function custom_room_selection_load_ignored_users()
{
    $ignored_users_file = plugin_dir_path(__FILE__) . 'ignored-users.json';
    $ignored_users = file_exists($ignored_users_file) ? json_decode(file_get_contents($ignored_users_file), true) : array();
    return $ignored_users;
}

// Load start index from JSON file
function custom_room_selection_load_start_index()
{
    $start_index_file = plugin_dir_path(__FILE__) . 'start-index.json';
    $start_index = file_exists($start_index_file) ? json_decode(file_get_contents($start_index_file)) : 0;
    return $start_index;
}

// Retrieve selected room and next rooms
function custom_room_selection_get_rooms()
{
    // Get all subscriber users
    $users = get_users(array('role' => 'subscriber'));

    // Sort users by name and room number
    usort($users, function ($userA, $userB) {
        $nameA = $userA->display_name;
        $nameB = $userB->display_name;

        // Extract room numbers
        $roomNumberA = intval(preg_replace('/[^0-9]+/', '', $nameA));
        $roomNumberB = intval(preg_replace('/[^0-9]+/', '', $nameB));

        // Compare room numbers
        if ($roomNumberA !== $roomNumberB) {
            return $roomNumberA - $roomNumberB;
        }

        // If room numbers are the same, compare names
        return strcasecmp($nameA, $nameB);
    });

    // Filter out ignored users
    $ignored_users = custom_room_selection_load_ignored_users();
    $users = array_filter($users, function ($user) use ($ignored_users) {
        return !in_array($user->display_name, $ignored_users);
    });

    // Generate array of available rooms
    $available_rooms = array_map(function ($user) {
        return $user->display_name;
    }, $users);

    // Load start index
    $start_index = custom_room_selection_load_start_index();

    // Rearrange the room entries in the array to have the starting room at the first position
    $available_rooms = array_merge(array_slice($available_rooms, $start_index), array_slice($available_rooms, 0, $start_index));

    // Determine the current week
    $current_week = date('W');

    // Calculate the selected room for this week
    $selected_room = $available_rooms[$current_week % count($available_rooms)];

    // Calculate the next rooms
    $next_rooms = array();
    $next_week = $current_week + 1;
    while (count($next_rooms) < 5) {
        $next_room = $available_rooms[$next_week % count($available_rooms)];
        if (!in_array($next_room, $next_rooms)) {
            $next_rooms[] = $next_room;
        }
        $next_week++;
    }

    return array(
        'selected' => $selected_room,
        'next'     => $next_rooms,
    );
}

// Shortcode for displaying the selected room and next rooms
function custom_room_selection_shortcode()
{
    $rooms = custom_room_selection_get_rooms();
    ob_start();
    ?>
    <h2>Selected room for this week</h2>
    <p>The selected room for <b>THIS</b> week is: <b><?php echo $rooms['selected']; ?></b></p>
    <?php if (!empty($rooms['next'])) : ?>
        <h3>Next rooms</h3>
        <table class="custom-room-selection-table">
            <thead>
                <tr>
                    <th>Week</th>
                    <th>Date Range</th>
                    <th>Rooms</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $current_week = date('W') + 1; // Start with the next week
                foreach ($rooms['next'] as $room) :
                ?>
                    <tr>
                        <td><?php echo $current_week; ?></td>
                        <td><?php echo custom_room_selection_get_date_range($current_week); ?></td>
                        <td><?php echo $room; ?></td>
                    </tr>
                <?php
                    $current_week++;
                endforeach;
                ?>
            </tbody>
        </table>
    <?php endif; ?>
    <?php
    return ob_get_clean();
}
add_shortcode('custom_room_selection', 'custom_room_selection_shortcode');

// Function to get the date range for a given week
function custom_room_selection_get_date_range($week_number)
{
    $year = date('Y');
    $start_of_week = date('d.m.Y', strtotime($year . 'W' . $week_number . '1'));
    $end_of_week = date('d.m.y', strtotime($year . 'W' . $week_number . '7'));
    return $start_of_week . ' - ' . $end_of_week;
}
