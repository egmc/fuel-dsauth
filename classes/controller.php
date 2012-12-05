<?php
namespace DsAuth;

class Controller extends \NinjAuth\Controller {
	
	public function action_callback($provider)
	{
		try
		{
			// Whatever happens, we're sending somebody somewhere
			$status = \NinjAuth\Strategy::forge($provider)->login_or_register();
	
			// Stuff should go with each type of response
			switch ($status)
			{
				case 'linked':
					$message = 'You have linked '.$provider.' to your account.';
					$url = static::$linked_redirect;
					break;
	
				case 'logged_in':
					$message = 'You have logged in.';
					$url = static::$login_redirect;
					break;
	
				case 'registered':
					$message = 'You have logged in with your new account.';
					$this->after_registered();
					$url = static::$registered_redirect;
					break;
	
				case 'register':
					$message = 'Please fill in any missing details and add a password.';
					$url = static::$register_redirect;
					break;
	
				default:
					exit('Strategy::login_or_register() has come up with a result that we dont know how to handle.');
			}
	
			\Response::redirect($url);
		}
	
		catch (CancelException $e)
		{
			$url = Strategy::forge($provider)->authenticate();
			exit('It looks like you canceled your authorisation. <a href="'.$url.'">Click here</a> to try again.');
		}
	
		catch (ResponseException $e)
		{
			exit($e->getMessage());
		}
	
		catch (AuthException $e)
		{
			exit($e->getMessage());
		}
	}
	
	public function action_register()
	{
		$user_hash = \Session::get('ninjauth.user');
		$authentication = \Session::get('ninjauth.authentication');
	
		// Working with what?
		$strategy =  \NinjAuth\Strategy::forge($authentication['provider']);
	
		$username = \Input::post('username');
	
		if ($username) {
			if (\Config::get('dsauth.allow_duplicated_username' ) == true ||  !\DsAuth::get_user_by_username($username)) {
				$user_hash ['nickname'] = $username;
				$user_id = $strategy->adapter->create_user(
						$user_hash
				);
				
				if ($user_id)
				{
					\NinjAuth\Model_Authentication::forge(array(
							'user_id' => $user_id,
							'provider' => $authentication['provider'],
							'uid' => $authentication['uid'],
							'access_token' => $authentication['access_token'],
							'secret' => $authentication['secret'],
							'refresh_token' => $authentication['refresh_token'],
							'expires' => $authentication['expires'],
							'created_at' => time(),
					))->save();
				
					\DsAuth::login($user_id);
					$this->after_registered();
				
					\Response::redirect(static::$registered_redirect);
				}
			}
		} else {
			$username = $user_hash['nickname'];
		}
	
		return \View::forge('register', array(
				'user' => compact('username')
		));
	}
	
	protected function after_registered()
	{
		\Session::delete('ninjauth');
		
	}
	
}