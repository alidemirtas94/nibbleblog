<?php

/*
 * Nibbleblog -
 * http://www.nibbleblog.com
 * Author Diego Najar

 * Last update: 29/10/2012

 * All Nibbleblog code is released under the GNU General Public License.
 * See COPYRIGHT.txt and LICENSE.txt.
*/

class LOGIN {

	private $session_started;

	function LOGIN()
	{
		// Set HTTPOnly
		session_set_cookie_params(0, NULL, NULL, NULL, TRUE);

		// Session start
		$this->session_started = session_start();

		// Regenerate the SESSION ID, this for prevent session hijacking "man-in-the-middle attack"
		session_regenerate_id(true);
	}

	/*
	 * Return a key, with user agent and user IP
	*/
	private function get_key()
	{
		global $_NET;
		global $_CRYPT;

		return( $_CRYPT->get_hash( $_NET->get_user_agent() . $_NET->get_user_ip() ) );
	}

	/*
	 * Set session variables
	 *
	 * Parameters
	 ** id_user
	 ** username
	*/
	public function set_login($args)
	{
		global $_NET;
		global $_DATE;

		$_SESSION = array();

		$_SESSION['session_user']['id'] = $args['id_user'];
		$_SESSION['session_user']['username'] = $args['username'];

		$_SESSION['session_login']['at'] = $_DATE->unixstamp();
		$_SESSION['session_login']['key'] = $this->get_key();

		$_SESSION['session_alert']['active'] = false;
		$_SESSION['session_alert']['msg'] = '';
	}

	/*
	 * Check the user is logued
	*/
	public function is_logued()
	{
		global $_TEXT;

		if( $this->session_started )
		{
			if( isset($_SESSION['session_user']['id']) && isset($_SESSION['session_login']['key']) )
			{
				if( $_TEXT->compare($_SESSION['session_login']['key'], $this->get_key()) )
				{
					return(true);
				}
			}
		}
		return(false);
	}

	/*
	 * Verify the username and password are correct
	 *
	 * Parameters
	 ** username
	 ** password
	*/
	public function verify_login($args)
	{
		global $_CRYPT;
		global $_TEXT;

		require( FILE_SHADOW );

		if( $_TEXT->compare($args['username'], $_USER[0]['username']) )
		{
			$hash = $_CRYPT->get_hash($args['password'], $_USER[0]['salt']);

			if( $_TEXT->compare($hash, $_USER[0]['password']) )
			{
				$this->set_login( array('id_user'=>0, 'username'=>$args['username']) );
				return(true);
			}
		}

		return(false);
	}

	/*
	 * Clean the variable session for logout the user
	*/
	public function logout()
	{
		$_SESSION = array();

		if (ini_get("session.use_cookies"))
		{
			$params = session_get_cookie_params();
			setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
		}

		session_destroy();

		$this->session_started = false;
	}


// =================================================================
// Methods for return the session parameters
// =================================================================

	public function get_user_id()
	{
		if( isset($_SESSION['session_user']['id']) )
		{
			return($_SESSION['session_user']['id']);
		}
		else
		{
			return(false);
		}
	}

	public function get_username()
	{
		if( isset($_SESSION['session_user']['username']) )
		{
			return($_SESSION['session_user']['username']);
		}
		else
		{
			return(false);
		}
	}

	public function get_time_user_logued()
	{
		if( isset($_SESSION['session_login']['at']) )
		{
			return($_SESSION['session_login']['at']);
		}
		else
		{
			return(false);
		}
	}

} // END class LOGIN

?>
