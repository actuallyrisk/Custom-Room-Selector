<?php
// Enqueue JavaScript and CSS files
function custom_room_selection_enqueue_scripts()
{
    wp_enqueue_script('custom-room-selection-script', plugin_dir_url(__FILE__) . '../js/custom-room-selection.js', array('jquery'), '1.0', true);
    wp_enqueue_style('custom-room-selection-style', plugin_dir_url(__FILE__) . '../css/custom-room-selection.css');
}
add_action('wp_enqueue_scripts', 'custom_room_selection_enqueue_scripts');
