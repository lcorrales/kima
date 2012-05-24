<?php
/**
 * Namespace Kima
 */
namespace Kima;

/**
 * Namespaces to use
 */
use \Kima\Error;
use \Kima\Controller;

/**
 * Action
 *
 * @package Kima
 */
class Action
{

    /**
     * constructor
     * @param string $controller
     * @param string $action
     */
    public function __construct()
    {
        // gets the controller and action
        $module = Application::get_instance()->get_module();
        $controller = Application::get_instance()->get_controller();
        $action = Application::get_instance()->get_action();

        // validate controller and action
        if (empty($controller)) {
            Error::set(__METHOD__, ' Controller was not set');
        }

        if (empty($action)) {
            Error::set(__METHOD__, ' Action was not set');
        }

        // inits the controller action
        $this->_run_action($module, $controller, $action);
    }

    /**
     * runs an action
     * @param string $module
     * @param string $controller
     * @param string $action
     */
    private function _run_action($module, $controller, $action)
    {
        // get the config
        $config = Application::get_instance()->get_config();

        // set the needed values
        $action = strtolower($action) . '_action';
        $controller = ucwords(strtolower($controller));

        // get the controller path
        $controller_folder = $module
            ? $config->module['folder'] . '/' . $module . '/controller'
            : $config->controller['folder'];

        $controller_path = $controller_folder . '/' . $controller . '.php';

        // get the controller
        if (is_readable($controller_path)) {
            require_once $controller_path;
        } else {
            header('HTTP/1.0 404 Not Found');
            $_GET['status_code'] = 404;

            $controller = 'Error';
            $controller_path = $config->controller['folder'] . '/Error.php';
            require_once $controller_path;

            $action = 'index_action';
        }

        // validate-create controller object
        class_exists($controller)
            ? $controller_obj = new $controller
            : Error::set(__METHOD__, ' Class ' . $controller . ' not found on ' . $controller_path);

        // validate controller is instance of Kima\Controller
        if (!$controller_obj instanceof Controller) {
            Error::set(__METHOD__, ' Object ' . $controller . ' is not an instance of Kima\Controller');
        }

        // validate-call action
        method_exists($controller, $action)
            ? $controller_obj->$action()
            : Error::set(__METHOD__, ' Method ' . $action . ' not found on ' . $controller . ' controller');
    }

}