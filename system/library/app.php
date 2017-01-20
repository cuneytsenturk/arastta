<?php
/**
 * @package     Arastta eCommerce
 * @copyright   2015-2017 Arastta Association. All rights reserved.
 * @copyright   See CREDITS.txt for credits and other copyright notices.
 * @license     GNU GPL version 3; see LICENSE.txt
 * @link        https://arastta.org
 */

use Joomla\Profiler\Profiler;

class App extends Object
{

    protected $registry;

    public function __construct()
    {
        $this->registry = new Registry();

        // Config
        if (file_exists(DIR_ROOT . 'config.php')) {
            require_once(DIR_ROOT . 'config.php');
        }

        $this->registry->set('profiler', new Profiler('Trigger'));
    }

    public function __get($key)
    {
        return $this->registry->get($key);
    }

    public function __set($key, $value)
    {
        $this->registry->set($key, $value);
    }

    public function initialise()
    {
        $this->trigger->fire('post.app.initialise');
    }

    public function ecommerce()
    {
        $this->trigger->fire('post.app.ecommerce');
    }

    public function route()
    {
        $this->trigger->fire('post.app.route');
    }

    public function dispatch()
    {
        $this->trigger->fire('post.app.dispatch');
    }

    public function render()
    {
        // Render
        $this->response->output();

        $this->trigger->fire('post.app.render');

        if ($this->config->get('config_debug_system') and $this->request->isGet() and !$this->request->isAjax()) {
            echo '<div id="profiler">'.$this->profiler.'</div>';
        }
    }

    public function errorHandler($errno, $errstr, $errfile, $errline)
    {
        // error suppressed with @
        if (error_reporting() === 0) {
            return false;
        }

        switch ($errno) {
            case E_NOTICE:
            case E_USER_NOTICE:
                $error = 'Notice';
                break;
            case E_WARNING:
            case E_USER_WARNING:
                $error = 'Warning';
                break;
            case E_ERROR:
            case E_USER_ERROR:
                $error = 'Fatal Error';
                break;
            default:
                $error = 'Unknown';
                break;
        }

        if ($this->config->get('config_error_display')) {
            echo '<b>' . $error . '</b>: ' . $errstr . ' in <b>' . $errfile . '</b> on line <b>' . $errline . '</b>';
        }

        if ($this->config->get('config_error_log')) {
            $this->log->write('PHP ' . $error . ':  ' . $errstr . ' in ' . $errfile . ' on line ' . $errline);
        }

        return true;
    }

    public function checkSslRedirection()
    {
        // Don't touch Ajax requests
        if ($this->request->isAjax()) {
            return;
        }

        $redirect = false;
        $ssl_required = false;

        $config_secure = $this->config->get('config_secure');

        if ($config_secure == 3) { // everywhere
            $ssl_required = true;
        } elseif (Client::isCatalog()) {
            $secure_routes = array('account/account', 'checkout/checkout');

            $this->trigger->fire('pre.app.sslredirection', array(&$secure_routes));

            if ($config_secure == 2) { // catalog
                $ssl_required = true;
            } elseif (($config_secure == 1) && (isset($this->request->get['route']) && in_array($this->request->get['route'], $secure_routes))) { // checkout
                $ssl_required = true;
            }
        }

        // Config set as SSL but URI comes as http
        if ($ssl_required && !$this->uri->isSsl()) {
            $this->uri->setScheme('https');

            $redirect = true;
        }

        // Config set as non-SSL but URI comes as https
        if (!$ssl_required && $this->uri->isSsl()) {
            $this->uri->setScheme('http');

            $redirect = true;
        }

        if ($redirect == false) {
            return;
        }

        $this->response->redirect($this->uri->toString(), 301);
    }
}
