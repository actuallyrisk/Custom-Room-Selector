<?php

// Function to check if it's the fourth week of the year
function custom_room_selection_fourth_week_message() {
    $current_week = date('W');
    
    // If it's a multiple of 4 (every 4th week), display the message in red
    if ($current_week % 3 === 0) {
        return '<span style="color: red;">In this week, all refrigerators must be cleaned, and the freezer compartments must be defrosted</span>';
    } elseif (($current_week + 1) % 3 === 0) {
        // If it's one week before a multiple of 4, display the message in orange
        return '<span style="color: orange;">Next week, all refrigerators should be prepared for cleaning and defrosting</span>';
    } else {
        // Otherwise, don't display anything
        return '';
    }
}

// Add the function as a shortcode
add_shortcode('custom_room_selection_fourth_week', 'custom_room_selection_fourth_week_message');
