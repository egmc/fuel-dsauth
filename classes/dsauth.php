<?php
namespace DsAuth;

use Session;
use Config;
use DB;
use Date;

/**
 * DsAuth:Auth
 *
 * @author EG
 * @license    MIT License http://www.opensource.org/licenses/mit-license.php
 */
class DsAuth {
	
	public static function _init() {
		Config::load('dsauth', true);
	}
	
	/**
	 * check login hash
	 *
	 * @return  bool true:valid_user
	 */
	public static function check() {
		$session_user = self::get_user_info();
		
		if (!$session_user) {
			Session::delete('user');
			return false;
		}
		$db_user = DB::select_array(Config::get('dsauth.table_columns', array('*')))
		->where('id', '=', $session_user['id'])
		->from(Config::get('dsauth.table_name'))
		->execute(Config::get('dsauth.db_connection'))->current();
		
		if (!$db_user) {
			Session::delete('user');
			return false;
		}
		if ($session_user['login_hash'] !== $db_user['login_hash']) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * login with user_id
	 *
	 * @param int $user_id
	 *
	 * @return bool login result
	 */
	public static function login($user_id) {
		
		if (!$user_id) {
			return false;
		}
		
		$timestamp = Date::forge()->get_timestamp();
		$login_hash = self::generate_login_hash($user_id, $timestamp);
		
		DB::update(Config::get('dsauth.table_name'))
		->set(array('last_login' => $timestamp, 'login_hash' => $login_hash))
		->where('id', '=', $user_id)
		->execute(Config::get('dsauth.db_connection'));
		$user['last_login'] = $timestamp;
		$user['login_hash'] = $login_hash;
		
		$user = DB::select_array(Config::get('dsauth.table_columns', array('*')))
		->where_open()
		->where('id', '=', $user_id)
		->where_close()
		->from(Config::get('dsauth.table_name'))
		->execute(Config::get('dsauth.db_connection'))
		->current();
		
		if(!$user) {
			Session::delete('user');
			return false;
		}
		
		Session::set('user', $user);
		return true;
	}
	
	/**
	 * get user id
	 */
	public static function get_user_id() {
		$user = self::get_user_info();
		return $user['id'];
	}
	
	/**
	 * get user info
	 *
	 * @return array user_info_array
	 */
	public static function get_user_info() {
		return Session::get('user');
	}
	
	/**
	 * create new user
	 *
	 * @param array $user_to_resgister
	 *
	 * @return int user_id
	 */
	public static function create_user(array $user_to_resgister) {

		$timestamp = Date::forge()->get_timestamp();
		
		$user_hash = \Session::get('ninjauth.user');
		$authentication = \Session::get('ninjauth.authentication');
		
		if (Config::get('dsauth.auto_modify_userinfo')) {
			// this will be removed if facebook image url has no token
			if (strpos($user_to_resgister['image'], 'https://graph.facebook.com/me/picture') !== false) {
				$user_to_resgister['image'] = "https://graph.facebook.com/{$user_to_resgister['uid']}/picture?type=normal";
			}
		}
		
		$user = array(
				'username'        => $user_to_resgister['nickname'],
				'image'        => $user_to_resgister['image'],
				'group'           => (int) Config::get('ninjauth.default_group'),
				'last_login'      => 0,
				'created_at'      => $timestamp,
				'updated_at'      => $timestamp,
		);
		$result = \DB::insert(Config::get('dsauth.table_name'))
		->set($user)
		->execute(Config::get('dsauth.db_connection'));
		
		
		return ($result[1] > 0) ? $result[0] : false;
	}
	
	/**
	 * logout
	 */
	public static function logout() {
		Session::delete('user');
		return true;
	}
	
	/**
	 * get user record by name
	 *
	 * @param string $username
	 *
	 * @return array user
	 */
	public static function get_user_by_username($username) {
		
		$same_user = \DB::select_array(Config::get('dsauth.table_columns', array('*')))
		->where('username', '=', $username)
		->from(Config::get('dsauth.table_name'))
		->execute(Config::get('dsauth.db_connection'))
		->current();
		return $same_user;
	}
	
	/**
	 * generate login hash for check
	 *
	 * @param int $user_id
	 * @param int $timestamp
	 *
	 * @return string login hash
	 */
	protected static function generate_login_hash($user_id, $timestamp) {
		return  sha1($user_id . Config::get('dsauth.login_hash_salt') . $timestamp);
	}
	
	/**
	 * check login session
	 */
	public static function is_logged_in() {
		$user_info = self::get_user_info();
		return is_array($user_info) && isset($user_info['id']);
	}
	
}