<?php
/*
Plugin Name: Custom Room Selection
Description: Custom plugin for room selection in WordPress
Version: 1.0
Author: Tomislav Zecevic
*/

// Include necessary files
require_once plugin_dir_path( __FILE__ ) . 'includes/enqueue-scripts.php';

// Load ignored users from JSON file
function custom_room_selection_load_ignored_users() {
    $ignored_users_file = plugin_dir_path( __FILE__ ) . 'ignored-users.json';
    $ignored_users = file_exists( $ignored_users_file ) ? json_decode( file_get_contents( $ignored_users_file ), true ) : array();
    return $ignored_users;
}

// Load start index from JSON file
function custom_room_selection_load_start_index() {
    $start_index_file = plugin_dir_path( __FILE__ ) . 'start-index.json';
    $start_index = file_exists( $start_index_file ) ? json_decode( file_get_contents( $start_index_file ) ) : 0;
    return $start_index;
}

// Retrieve selected room for the current week
function custom_room_selection_get_selected_room() {
    // Get all subscriber users
    $users = get_users( array( 'role' => 'subscriber' ) );

    // Sort users by name and room number
    usort( $users, function( $userA, $userB ) {
        $nameA = $userA->display_name;
        $nameB = $userB->display_name;

        // Extract room numbers
        $roomNumberA = intval( preg_replace( '/[^0-9]+/', '', $nameA ) );
        $roomNumberB = intval( preg_replace( '/[^0-9]+/', '', $nameB ) );

        // Compare room numbers
        if ( $roomNumberA !== $roomNumberB ) {
            return $roomNumberA - $roomNumberB;
        }

        // If room numbers are the same, compare names
        return strcasecmp( $nameA, $nameB );
    } );

    // Filter out ignored users
    $ignored_users = custom_room_selection_load_ignored_users();
    $users = array_filter( $users, function( $user ) use ( $ignored_users ) {
        return ! in_array( $user->display_name, $ignored_users );
    } );

    // Generate array of available rooms
    $availableRooms = array_map( function( $user ) {
        return $user->display_name;
    }, $users );

    // Load start index
    $start_index = custom_room_selection_load_start_index();

    // Rearrange the room entries in the array to have the starting room at the first position
    $availableRooms = array_merge( array_slice( $availableRooms, $start_index ), array_slice( $availableRooms, 0, $start_index ) );

    // Determine the current week
    $currentWeek = date( 'W' );

    // Calculate the room for this week based on the current week
    $selectedRoom = $availableRooms[ $currentWeek % count( $availableRooms ) ];

    return $selectedRoom;
}

// Shortcode for displaying the selected room
function custom_room_selection_shortcode() {
    $selectedRoom = custom_room_selection_get_selected_room();
    ob_start();
    ?>
    <h2>Selected room for this week</h2>
    <p>The selected room for week <?php echo date( 'W' ); ?> is: <b><?php echo $selectedRoom; ?></b></p>
    <?php
    return ob_get_clean();
}
add_shortcode( 'custom_room_selection', 'custom_room_selection_shortcode' );
