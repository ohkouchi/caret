<?php
/*
Plugin Name: CaretEzGoogleMaps
Plugin URI: http://www.ca-ret.co.jp/WordPress/
Description: GoogleMapsを記事内に簡単挿入
Author: Caret Inc.
Version: 1.0.0
Author URI: http://www.ca-ret.co.jp/
License: GPL2
*/

/*	@2013 Caret Inc.
	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

class CaretEzGoogleMaps
{
	function CaretEzGoogleMaps()
	{
		add_action('plugins_loaded', array(&$this, 'Initalization'));
	}

	function sink_hooks()
	{
		add_filter('mce_plugins', array(&$this, 'mce_plugins'));
	}

	function Initalization()
	{
		add_action('init', array(&$this, 'addbuttons'));
	}

	function addbuttons()
	{
		if (!current_user_can('edit_posts') && !current_user_can('edit_pages')) return;

		if (get_user_option('rich_editing') == 'true') {
			add_filter("mce_external_plugins", array(&$this, 'mce_external_plugins'));
			add_filter('mce_buttons', array(&$this, 'mce_buttons'));
		}
	}

	function mce_buttons($buttons)
	{
		array_push($buttons, "separator", "GoogleMaps");
		return $buttons;
	}

	function mce_external_plugins($plugin_array)
	{
		$plugin_array['CaretEzGoogleMaps'] = get_bloginfo('wpurl') .'/wp-content/plugins/CaretEzGoogleMaps/CaretEzGoogleMaps.js';
		return $plugin_array;
	}

	function mce_html_buttons()
	{
		echo '<script type="text/javascript">';
		echo "QTags.addButton('ed_caretezgooglemaps', 'GoogleMaps', '{GoogleMaps}', '{/GoogleMaps}');";
		echo '</script>';
	}

	var $load_js;

	function replace($string)
	{
		$pattern = '/(\{GoogleMaps\})(.*?)(\{\/GoogleMaps\})/i';

		preg_match_all($pattern, $string, $matches);

		foreach ($matches[0] as $val) {
			$addr = preg_replace($pattern, "$2", $val);
			$addr = str_replace(array("\r", "\n"), "", $addr);

 			if (!$addr) continue;

			$uniq_id = uniqid();

			$tags = '<div class="CaretEzGoogleMaps" id="maps_'.$uniq_id.'"></div>';
			$string = str_replace($val, $tags, $string);
			$this->load_js .= "googlemap_init('maps_{$uniq_id}', '{$addr}');\n";
		}

		return $string;
	}

	function api_load()
	{
		wp_enqueue_style( 'caretezgooglemaps', get_bloginfo('wpurl').'/wp-content/plugins/CaretEzGoogleMaps/style.css');
		wp_enqueue_script('googlemapsapiv3','http://maps.google.com/maps/api/js?v=3&amp;sensor=false', array(), null);
		wp_enqueue_script('caretezgooglemaps', get_bloginfo('wpurl').'/wp-content/plugins/CaretEzGoogleMaps/CaretEzGoogleMaps.ui.js', array(), null);

		echo <<<EOF
<script type="text/javascript">
window.onload=function(){
{$this->load_js}
}
</script>
EOF;
	}
}

$obj = & new CaretEzGoogleMaps();
add_filter('the_content', array(&$obj, 'replace'), 10);
add_action('init',  array(&$obj, 'CaretEzGoogleMaps'));
add_action('admin_print_footer_scripts', array(&$obj, 'mce_html_buttons'));
add_action('wp_footer', array(&$obj, 'api_load'));

?>