<?php
/**
 * @package     Arastta eCommerce
 * @copyright   2015-2017 Arastta Association. All rights reserved.
 * @copyright   See CREDITS.txt for credits and other copyright notices.
 * @license     GNU GPL version 3; see LICENSE.txt
 * @link        https://arastta.org
 */

class Session {
    public $data = array();

    public function __construct() {
        if (!session_id()) {
            ini_set('session.use_only_cookies', '1');
            ini_set('session.use_trans_sid', '0');
            ini_set('session.cookie_httponly', '1');

            session_set_cookie_params(0, '/');
            session_start();
        }

        $this->data =& $_SESSION;
    }

    public function getId() {
        return session_id();
    }

    public function destroy() {
        return session_destroy();
    }
}
