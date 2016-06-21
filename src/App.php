<?php
/**
 * @file the main application for this drupal module
 *
 * @author Aaron Saray
 */

namespace iMoneza\Drupal;
use iMoneza\Drupal\Form;
use Pimple\Container;

/**
 * Class App
 * @package iMoneza\Drupal
 */
class App
{
    /**
     * @var string the permission for admin
     */
    const PERMISSION_ADMIN_IMONEZA = "administer imoneza settings";
    
    /**
     * @var self singleton variable
     */
    protected static $instance;

    /**
     * @var Container
     */
    protected $di;

    /**
     * @var string the location of the template files
     */
    protected $templateFilesPath;

    /**
     * @return App singleton (needed for drupal specifically)
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }    
        
        return self::$instance;
    }

    /**
     * App constructor. Protected because this is a singleton pattern for Drupal
     */
    protected function __construct()
    {
        $this->templateFilesPath = drupal_get_path('module', 'imoneza') . '/templates';
        
        $this->di = $di = new Container();

        /**
         * Forms
         */
        $di['imoneza_first_time_form'] = function() {
            return new Form\FirstTime();
        };
        $di['imoneza_internal_config_form'] = function() {
            return new Form\InternalConfig();
        };
    }
    
    /**
     * Generate the menu for the app
     * 
     * @return array
     */
    public function menu()
    {
        return [
            "admin/settings/imoneza"    =>  [
                "title" => "iMoneza",
                "description" => "iMoneza Settings",
                "page callback" => "drupal_get_form",
                "page arguments" => array("imoneza_first_time_form"),
                "access arguments" => array(self::PERMISSION_ADMIN_IMONEZA),
                "type" => MENU_NORMAL_ITEM
            ],
            "admin/settings/imoneza/config" =>  [
                "title" => "iMoneza Internal Config",
                "description" => "iMoneza Internal Config",
                "page callback" => "drupal_get_form",
                "page arguments" => array("imoneza_internal_config_form"),
                "access arguments" => array(self::PERMISSION_ADMIN_IMONEZA),
                "type" => MENU_CALLBACK
            ]
        ];
    }

    /**
     * This creates a section in the permissions admin area to allow permissions for this module.
     * 
     * @return array
     */
    public function permissions() {
        return [
            self::PERMISSION_ADMIN_IMONEZA => [
                "title" => t("Administer iMoneza Settings")
            ]
        ];
    }

    /**
     * Specifies themable items
     *
     * @return array
     */
    public function theme() {
        return [
            'imoneza_first_time_form' => [
                'render element' => 'form',
            ],
            'imoneza_internal_config_form' => [
                'render element' => 'form',
                'template'  =>  'internal-config',
                'path'  =>  $this->templateFilesPath
            ]
        ];
    }
    
    /**
     * Displays help string for this module
     *
     * @param $path string
     * @param $arg mixed
     * @return null|string
     */
    public function help($path, $arg) {
        $help = '';

        switch ($path) {
            case "admin/help#imoneza":
                $help .= theme_render_template($this->templateFilesPath . '/help.tpl.php', []);
                break;
        }

        return $help;
    }

    /**
     * Spawn a form using our OO method (this is used in a callback in the .module file)
     *
     * @param $form
     * @param $formState
     * @param $formId
     * @return array
     */
    public function form($form, &$formState, $formId)
    {
        $formState['build_info']['base_form_id'] = $formId; // this is an array because its a callback by default but drupal requires a string - so making it back to the original string (lets others alter us too)
        return $this->di[$formId]();
    }
}