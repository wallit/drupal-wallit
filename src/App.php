<?php
/**
 * @file the main application for this drupal module
 *
 * @author Aaron Saray
 */

namespace iMoneza\Drupal;

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
     * @return App singleton
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }    
        
        return self::$instance;
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
                "type" => MENU_NORMAL_ITEM,
                "file" => "imoneza.admin.inc"
            ],
            "admin/settings/imoneza/config" =>  [
                "title" => "iMoneza Internal Config",
                "description" => "iMoneza Internal Config",
                "page callback" => "drupal_get_form",
                "page arguments" => array("imoneza_internal_config_form"),
                "access arguments" => array(self::PERMISSION_ADMIN_IMONEZA),
                "type" => MENU_CALLBACK,
                "file" => "imoneza.internal-config.inc"
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
                $help .= "<h3>" . t('About') . "</h3>";
                $help .= "<p>" . t("This module adds the iMoneza content control and paywall to your site.") . "</p>";
                $help .= "<h3>" . t('Features') . "</h3>";
                $help .= "<dl>";
                $help .= "<dt>" . t('Paywall and Wallet') . "</dt>";
                $help .= "<dd>" . t('Add the paywall and embedded wallet to your site.  The paywall can be configured to be server-side or embedded (client side).') . "</dd>";
                $help .= "<dt>" . t('Premium Content Indicator') . "</dt>";
                $help .= "<dd>" . t('Indicate premium content on your site by tagging it as such.  This helps users understand if a paywall action is about to occur.') . "</dd>";
                $help .= "</dl>";
                break;
        }

        return $help;
    }
}