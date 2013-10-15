<?php
/*
Plugin Name: Caret Two Stage Authentication
Plugin URI: http://www.ca-ret.co.jp/WordPress/
Description: 管理画面の認証にベーシック認証を追加し、簡易二段階認証を実現します。
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

caret2saStart();

function caret2saStart()
{
	global $wpdb;

	define('CARET2SA_TABLE', $wpdb->prefix.'caret2sa');

	$obj = & new caret2saAuthentication($wpdb);

	if (function_exists( 'register_activation_hook' )) {
		register_activation_hook(__FILE__, array($obj, 'install'));
	}
	if (function_exists('register_uninstall_hook')) {
		register_uninstall_hook(__FILE__, 'uninstall');
	}
	add_action('admin_init', array($obj, 'authCheck'), 100);
	add_action('admin_notices', array($obj, 'noticeMessage'));
	add_action('admin_menu', array($obj, 'addMenu'));
	add_filter('plugin_action_links', array($obj, 'addLink'), 10, 2);
}

function uninstall()
{
	global $wpdb;

	$sql = "DROP TABLE IF EXISTS ".CARET2SA_TABLE;
	$wpdb->query($sql);
}

class caret2saAuthentication
{
	var $wpdb;
	var $wptbl;
	var $auth;
	var $list;
	var $table;
	var $error = false;

	function __construct($wpdb)
	{
		mb_language("Ja");
		mb_internal_encoding("UTF-8");
		date_default_timezone_set('Asia/Tokyo');

		$GLOBALS["caret2sa"] = $_REQUEST;

		$this->wpdb = $wpdb;
		$this->wptbl = CARET2SA_TABLE;
		$this->table = $this->isTable();
		$this->list = $this->getList();
		$this->auth = $this->isAuth();
	}

	function install()
	{
		if (!$this->table) {
			$sql = <<<SQL
CREATE TABLE {$this->wptbl} (
	user VARCHAR(32) UNIQUE NOT NULL,
	passwd VARCHAR(256) NOT NULL
) default character set 'utf8' ENGINE=InnoDB;
SQL;

			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql);
		}
	}

	function addMenu()
	{
		add_submenu_page('options-general.php', 'ベーシック認証設定', 'ベーシック認証設定', 8, 'CaretTwoStageAuthentication', array($this, 'action'));
	}

	function addLink($links, $file)
	{
		if ($file === plugin_basename(__FILE__)) {
			$settings_link = '<a href="options-general.php?page=CaretTwoStageAuthentication">設定</a>';
	        array_unshift($links, $settings_link);
	    }

	    return $links;
	}

	function isTable()
	{
		if ($this->wpdb->get_var("show tables like '{$this->wptbl}'") !== $this->wptbl) {
			return false;
		}

		return true;
	}

	function getList()
	{
		if (!$this->table) return array();

		$sql = "SELECT user, AES_DECRYPT(UNHEX(passwd), 'caret2sa') as passwd FROM {$this->wptbl}";

		return $this->wpdb->get_results($sql, ARRAY_A);
	}

	function isAuth()
	{
		if (!is_admin()) return true;
		if (!$this->list) return true;

		if (!isset($_SERVER["PHP_AUTH_USER"]) || !isset($_SERVER["PHP_AUTH_PW"]) || $_SERVER["PHP_AUTH_USER"] === "" || $_SERVER["PHP_AUTH_PW"] === "") {
			return false;
		}

		foreach ($this->list as $val) {
			if ($val['user'] === $_SERVER["PHP_AUTH_USER"] && $val['passwd'] === $_SERVER["PHP_AUTH_PW"]) {
				return true;
			}
		}

		return false;
	}

	function authPopup()
	{
		header("WWW-Authenticate: Basic realm=\"Please Enter Your Password\"");
		header("HTTP/1.0 401 Unauthorized");
		echo "Authorization Required";
		exit;
	}

	function authCheck()
	{
 		if (strpos($_SERVER['REQUEST_URI'], '/wp-admin') !== false && strpos($_SERVER['PHP_SELF'], 'admin-ajax.php') === false && !$this->auth) {
			$this->authPopup();
		}
	}

	function noticeMessage()
	{
		if (strpos($_SERVER['PHP_SELF'], 'options-general.php') !== false && $_GET['page'] === "CaretTwoStageAuthentication") return;

		if (!$this->list) {
			echo '<div id="message" class="error"><p><strong>ベーシック認証の設定を<a href="options-general.php?page=CaretTwoStageAuthentication">こちら</a>から行ってください。</strong></p></div>';
		}
	}

	function update()
	{
		if ($this->error) return;

		$sql = "DELETE FROM {$this->wptbl}";
		$this->wpdb->query($sql);

		for ($i = 0; $i < count($GLOBALS["caret2sa"]['user']); $i++) {
			if (!$GLOBALS["caret2sa"]['user'][$i] && !$GLOBALS["caret2sa"]['passwd'][$i]) continue;

			$values  = "'" . $this->wpdb->escape($GLOBALS["caret2sa"]['user'][$i]) . "', ";
			$values .= "HEX(AES_ENCRYPT('" . $this->wpdb->escape($GLOBALS["caret2sa"]['passwd'][$i]) . "', 'caret2sa'))";

			$sql = "INSERT INTO {$this->wptbl}(user, passwd) VALUES({$values})";

			$this->wpdb->query($sql);
		}
	}

	function validate()
	{
		$user_exists = array();

		for ($i = 0; $i < count($GLOBALS["caret2sa"]['user']); $i++) {
			if (!$GLOBALS["caret2sa"]['user'][$i] && !$GLOBALS["caret2sa"]['passwd'][$i]) continue;

			if ($GLOBALS["caret2sa"]['user'][$i] === "") {
				$GLOBALS["caret2sa"]['error']['user'][$i] = "入力してください";
				$this->error = true;
			} elseif (strlen($GLOBALS["caret2sa"]['user'][$i]) > 32) {
				$GLOBALS["caret2sa"]['error']['user'][$i] = "32文字以内で入力してください";
				$this->error = true;
			} elseif (preg_match("/[^\w\-\.]/", $GLOBALS["caret2sa"]['user'][$i])) {
				$GLOBALS["caret2sa"]['error']['user'][$i] = "半角英数字及び記号「- _ .」のみを使用してください";
				$this->error = true;
			} elseif (!empty($user_exists[$GLOBALS["caret2sa"]['user'][$i]])) {
				$GLOBALS["caret2sa"]['error']['user'][$i] = "IDが重複しています";
				$this->error = true;
			} else {
				$user_exists[$GLOBALS["caret2sa"]['user'][$i]] = 1;
			}

			if ($GLOBALS["caret2sa"]['passwd'][$i] === "") {
				$GLOBALS["caret2sa"]['error']['passwd'][$i] = "入力してください";
				$this->error = true;
			} elseif (strlen($GLOBALS["caret2sa"]['passwd'][$i]) > 32) {
				$GLOBALS["caret2sa"]['error']['passwd'][$i] = "32文字以内で入力してください";
				$this->error = true;
			} elseif (preg_match("/[^\w\-\!\"\#\$\%\'\(\)\+\<\=\>\/\;\?\[\]\{\|\}\~\.]/", $GLOBALS["caret2sa"]['passwd'][$i])) {
				$GLOBALS["caret2sa"]['error']['passwd'][$i] = "半角英数字及び記号「 - _ ! \" # $ % ' ( ) + < = > / ; ? [ ] { | } ~ .」のみを使用してください";
				$this->error = true;
			}
		}
	}

	function action()
	{
		switch($GLOBALS["caret2sa"]['action']) {
			case "update":
				$this->validate();
				$this->update();
				break;
			default:
				for ($i = 0; $i < count($this->list); $i++) {
					$GLOBALS["caret2sa"]['user'][$i] = $this->list[$i]['user'];
					$GLOBALS["caret2sa"]['passwd'][$i] = $this->list[$i]['passwd'];
				}

				break;
		}

		require_once(dirname(__FILE__)."/setup.php");
	}
}

?>