<?php
namespace NinjAuth;

use \DsAuth\DsAuth;

/**
 * DsAuth Adapter For NinjAuth
 *
 * @author EG
 *
 */
class Adapter_Dsauth extends Adapter {

	public function is_logged_in() {
		return DsAuth::is_logged_in();
	}
	public function get_user_id() {
		return DsAuth::get_user_id();
	}
	public function force_login($user_id) {
		return DsAuth::login($user_id);
	}
	public function create_user(array $user) {
		return DsAuth::create_user($user);
	}
	
	public function can_auto_login(array $user) {
		
		DsAuth::init();
		if (\Config::get('dsauth.always_confirm_username')) {
			return false;
		}
		
		if(\Config::get('dsauth.allow_duplicated_username') == false && \DsAuth::get_user_by_username($user['nickname'])) {
			return false;
		}
		
		return true;
	}
}