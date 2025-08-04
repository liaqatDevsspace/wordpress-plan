<?php

/*

Plugin Name: Devsspace plugin
Description: Made for demo 
Version:1.0
Author:Liaqat Ali

 **/

// Run this when the plugin is activated
function my_plugin_activate()
{
    error_log('Devsspace Plugin was activated.');
}
register_activation_hook(__FILE__, 'my_plugin_activate');

// Run this when the plugin is deactivated
function my_plugin_deactivate()
{
    error_log('Devsspace Plugin was deactivated.');
}
register_deactivation_hook(__FILE__, 'my_plugin_deactivate');
