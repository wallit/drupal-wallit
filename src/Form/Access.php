<?php
/**
 * Access form
 * 
 * @note this cannot be ajax based because it doesn't get regenerated otherwise.
 *
 * @author Aaron Saray
 */

namespace iMoneza\Drupal\Form;
use iMoneza\Drupal\Model;
use iMoneza\Drupal\Service;

/**
 * Class Access
 * @package iMoneza\Drupal\Form
 */
class Access extends FormAbstract
{
    /**
     * @var Service\iMoneza
     */
    protected $iMonezaService;

    /**
     * Used to store between validate and submit
     * 
     * @var \iMoneza\Data\Property|false
     */
    protected $propertyOptions;

    /**
     * FirstTime constructor.
     * @param Model\Options $options
     * @param Service\iMoneza $iMonezaService
     */
    public function __construct(Model\Options $options, Service\iMoneza $iMonezaService)
    {
        parent::__construct($options);
        $this->iMonezaService = $iMonezaService;
    }
    
    /**
     * @return array The data for the form
     */
    public function __invoke()
    {
        $form = [
            '#validate' =>  [[$this, 'validate']],
            '#submit'   =>  [[$this, 'submit']],
            '#theme'    =>  'imoneza_access_form',
            '#attached' =>  [
                'css'   =>  [drupal_get_path('module', 'imoneza') . '/assets/css/admin.css']
            ],
            '#attributes'   =>  [
                'class' =>  ['imoneza-form']
            ]
        ];

        $form['manage_api'] = [
            '#type' =>  'fieldset'
        ];
        $form['manage_api']['manage_api_key'] = array(
            '#type' =>  'textfield',
            '#title'  =>  t('Resource Management API Key:'),
            '#required' => true,
            '#default_value'    =>  $this->options->getManageApiKey()
        );
        $form['manage_api']['manage_api_secret'] = array(
            '#type' =>  'textfield',
            '#title'  =>  t('Resource Management Secret:'),
            '#required' =>  true,
            '#default_value'    =>  $this->options->getManageApiSecret()
        );

        $form['access_api'] = [
            '#type' =>  'fieldset'
        ];
        $form['access_api']['access_api_key'] = array(
            '#type' =>  'textfield',
            '#title'  =>  t('Resource Access API Key:'),
            '#required' => true,
            '#default_value'    =>  $this->options->getAccessApiKey()
        );
        $form['access_api']['access_api_secret'] = array(
            '#type' =>  'textfield',
            '#title'  =>  t('Resource Access Secret:'),
            '#required' =>  true,
            '#default_value'    =>  $this->options->getAccessApiSecret()
        );
        if (!($accessControl = $this->options->getAccessControl())) {
            $accessControl = Model\Options::ACCESS_CONTROL_CLIENT;
        }
        $form['access_control_method'] = [
            '#type' =>  'radios',
            '#title'    =>  'Select an Access Control:',
            '#default_value'    =>  $accessControl,
            '#options'  =>  [
                Model\Options::ACCESS_CONTROL_CLIENT    =>  'Client Side',
                Model\Options::ACCESS_CONTROL_SERVER    =>  'Server Side'
            ]
        ];
        
        $form['submit'] = array(
            '#type' =>  'submit',
            '#value'  =>  t('Save Settings')
        );
        
        $form['refresh'] = array(
            '#type' =>  'submit',
            '#value'    =>  t('Refresh Settings from iMoneza.com')
        );
        
        return $form;
    }

    /**
     * Implements form validation
     *
     * @param $form
     * @param $form_state
     * @return bool|void
     */
    public function validate($form, &$form_state)
    {
        if ($form_state["triggering_element"]['#id'] == 'edit-refresh') {
            // this means that it was the refresh button - so we need to populate property options with our standard info
            $this->iMonezaService
                ->setManagementApiKey($this->options->getManageApiKey())
                ->setManagementApiSecret($this->options->getManageApiSecret())
                ->setManageApiUrl($this->options->getManageApiUrl(Model\Options::GET_DEFAULT));  // wonder if this is a bad idea not to populate those values
            $this->propertyOptions = $this->iMonezaService->getProperty();
        }
        else {
            if (
                ($manageApiKey = $form_state['values']['manage_api_key'])
                &&
                ($manageApiSecret = $form_state['values']['manage_api_secret'])
                &&
                ($accessApiKey = $form_state['values']['access_api_key'])
                &&
                ($accessApiSecret = $form_state['values']['access_api_secret'])
            ) {

                $this->iMonezaService
                    ->setManagementApiKey($manageApiKey)
                    ->setManagementApiSecret($manageApiSecret)
                    ->setAccessApiKey($accessApiKey)
                    ->setAccessApiSecret($accessApiSecret)
                    ->setManageApiUrl($this->options->getManageApiUrl(Model\Options::GET_DEFAULT))
                    ->setAccessApiUrl($this->options->getAccessApiUrl(Model\Options::GET_DEFAULT));

                if (!($this->propertyOptions = $this->iMonezaService->getProperty())) {
                    form_set_error('manage_api_key', $this->iMonezaService->getLastError());
                }

                if (!$this->iMonezaService->validateResourceAccessApiCredentials()) {
                    form_set_error('access_api_key', $this->iMonezaService->getLastError());
                }
            }
        }
    }

    /**
     * Handles the form submission
     *
     * @param $form
     * @param $form_state
     */
    public function submit($form, &$form_state) {
        $successMessage = t('Your settings have been updated from iMoneza.com');
        
        if ($form_state["triggering_element"]['#id'] != 'edit-refresh') {
            // this means that it was the save button, not the refresh settings button
            $this->options
                ->setManageApiKey($form['manage_api']['manage_api_key']['#value'])
                ->setManageApiSecret($form['manage_api']['manage_api_secret']['#value'])
                ->setAccessApiKey($form['access_api']['access_api_key']['#value'])
                ->setAccessApiSecret($form['access_api']['access_api_secret']['#value'])
                ->setAccessControl($form['access_control_method']['#value']);
            
            $successMessage = t("Your settings have been saved!");
        }
        
        // update the rest from the property options regardless
        $this->options
            ->setPropertyTitle($this->propertyOptions->getTitle())
            ->setDynamicallyCreateResources($this->propertyOptions->isDynamicallyCreateResources())
            ->setPricingGroupsBubbleDefaultToTop($this->propertyOptions->getPricingGroups());
        
        $this->saveOptions();

        drupal_set_message($successMessage);
    }
}