<?php
/**
 * @file the main application for this drupal module
 *
 * @author Aaron Saray
 */

namespace iMoneza\Drupal;
use iMoneza\Drupal\Form;
use iMoneza\Drupal\Model\Options;
use Pimple\Container;
use iMoneza\Exception;


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

        // DI: Options
        $di['options'] = function() {
            /** this is done because its __PHP_INCOMPLETE_CLASS when its unserialized the first time (cuz our class is not valid yet)  */
            return unserialize(serialize(variable_get('imoneza-options', new Model\Options())));
        };

        // DI: Filters
        $di['filter.external-resource-key'] = function () {
            return new Filter\ExternalResourceKey();
        };
        
        // DI: Services
        $di['service.imoneza'] = function () use ($di) {
            return new Service\iMoneza($di['filter.external-resource-key']);
        };
        
        // DI: Forms
        $di['imoneza_first_time_form'] = function() use ($di) {
            return new Form\FirstTime($di['options'], $di['service.imoneza']);
        };
        $di['imoneza_access_form'] = function() use ($di) {
            return new Form\Access($di['options'], $di['service.imoneza']);
        };
        $di['imoneza_internal_config_form'] = function() use ($di) {
            return new Form\InternalConfig($di['options']);
        };
    }
    
    /**
     * Generate the menu for the app
     * 
     * @return array
     */
    public function menu()
    {
        /** @var \iMoneza\Drupal\Model\Options $options */
        $options = $this->di['options'];

        $menu = [];
        if ($options->isInitialized()) {
            $menu["admin/settings/imoneza"] = [
                "title" => "iMoneza",
                "description" => "iMoneza Settings",
                "page callback" => "drupal_get_form",
                "page arguments" => array("imoneza_access_form"),
                "access arguments" => array(self::PERMISSION_ADMIN_IMONEZA),
                "type" => MENU_NORMAL_ITEM
            ];
        }
        else {
            $menu["admin/settings/imoneza"] = [
                "title" => "iMoneza",
                "description" => "iMoneza Settings",
                "page callback" => "drupal_get_form",
                "page arguments" => array("imoneza_first_time_form"),
                "access arguments" => array(self::PERMISSION_ADMIN_IMONEZA),
                "type" => MENU_NORMAL_ITEM
            ];
        }
        
        $menu["admin/settings/imoneza/config"] =  [
            "title" => "iMoneza Internal Config",
            "description" => "iMoneza Internal Config",
            "page callback" => "drupal_get_form",
            "page arguments" => array("imoneza_internal_config_form"),
            "access arguments" => array(self::PERMISSION_ADMIN_IMONEZA),
            "type" => MENU_CALLBACK
        ];
        
        return $menu;
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
                'template'  =>  'admin/options/first-time',
                'path'  =>  $this->templateFilesPath
            ],
            'imoneza_access_form' => [
                'render element' => 'form',
                'template'  =>  'admin/options/access',
                'path'  =>  $this->templateFilesPath
            ],
            'imoneza_internal_config_form' => [
                'render element' => 'form',
                'template'  =>  'admin/options/internal-config',
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
                $help .= theme_render_template($this->templateFilesPath . '/admin/help.tpl.php', []);
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

    /**
     * Set variables that our forms might need
     * 
     * @param $variables array
     */
    public function preprocessForm(&$variables)
    {
        /** @var \iMoneza\Drupal\Model\Options $options */
        $options = $this->di['options'];
        $variables['manageUiUrl'] = $options->getManageUiUrl();
        $variables['propertyTitle'] = $options->getPropertyTitle();
        $variables['isDynamicallyCreateResources'] = $options->isDynamicallyCreateResources();
    }
    
    /**
     * Creates a nice URL for an asset in a template
     * 
     * @param $url string location of the asset
     * @return string
     */
    public static function asset($url)
    {
        return file_create_url(drupal_get_path('module', 'imoneza') . '/assets/' . $url);
    }

    /**
     * Used to determine if the admin notice is needed
     */
    public function adminNoticeConfigNeeded()
    {
        $path = current_path();
        if (stripos($path, 'settings/imoneza') === false && !$this->di['options']->isInitialized()) {
            $message = sprintf('%s <a href="#">%s</a>',
                t('iMoneza is not yet configured.'),
                t('Configure iMoneza to begin protecting your content.')
            );
            drupal_set_message($message, 'warning', false);
        }
    }

    /**
     * Add the client side javascript to the non-admin pages
     */
    public function addClientSideAccessControl()
    {
        /** @var \iMoneza\Drupal\Model\Options $options */
        $options = $this->di['options'];
        
        if ($options->isAccessControlClient() && $options->getAccessApiKey()) {
            $resourceKey = null;
            if ($node = menu_get_object()) {
                $resourceKey = $this->di['filter.external-resource-key']($node);
            }
            
            drupal_add_js($options->getJavascriptCdnUrl(Options::GET_DEFAULT));
            drupal_add_js(sprintf('iMoneza.paywall.init("%s",{resourceKey:"%s"});', $options->getAccessApiKey(), $resourceKey), 'inline');
        }
    }

    /**
     * Add server side control
     */
    public function addServerSideAccessControl()
    {
        /** @var \iMoneza\Drupal\Model\Options $options */
        $options = $this->di['options'];

        if ($options->isAccessControlServer() && $options->getAccessApiKey()) {
            if ($node = menu_get_object()) {
                $iMonezaTUT = isset($_GET['iMonezaTUT']) ? $_GET['iMonezaTUT'] : null;

                /** @var \iMoneza\Drupal\Service\iMoneza $service */
                $service = $this->di['service.imoneza'];
                $service
                    ->setManagementApiKey($options->getManageApiKey())
                    ->setManagementApiSecret($options->getManageApiSecret())
                    ->setAccessApiKey($options->getAccessApiKey())
                    ->setAccessApiSecret($options->getAccessApiSecret())
                    ->setManageApiUrl($options->getManageApiUrl(Options::GET_DEFAULT))
                    ->setAccessApiUrl($options->getAccessApiUrl(Options::GET_DEFAULT));

                try {
                    if ($redirectURL = $service->getResourceAccessRedirectURL($node, $iMonezaTUT)) {
                        $currentURL = sprintf('%s://%s%s', $_SERVER['SERVER_PROTOCOL'], $_SERVER['HTTP_HOST'], $_SERVER['REQUEST_URI']);
                        drupal_goto($redirectURL . '&originalURL=' . $currentURL);
                    }
                }
                catch (Exception\iMoneza $e) {
                    // do nothing - as we don't want to error out on content
                }
            }
        }
    }
}