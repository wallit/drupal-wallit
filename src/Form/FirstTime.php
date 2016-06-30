<?php
/**
 * First time form
 * 
 * @note this cannot be ajax based because it doesn't get regenerated otherwise.
 *
 * @author Aaron Saray
 */

namespace iMoneza\Drupal\Form;
use iMoneza\Drupal\Model;
use iMoneza\Drupal\Service;

/**
 * Class FirstTime
 * @package iMoneza\Drupal\Form
 */
class FirstTime extends FormAbstract
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
            '#theme'    =>  'imoneza_first_time_form',
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
        
        $form['manage_api']['key'] = array(
            '#type' =>  'textfield',
            '#title'  =>  t('Resource Management API Key:'),
            '#required' => true
        );
        $form['manage_api']['secret'] = array(
            '#type' =>  'textfield',
            '#title'  =>  t('Resource Management Secret:'),
            '#required' =>  true
        );

        $form['submit'] = array(
            '#type' =>  'submit',
            '#value'  =>  t('Verify Access')
        );
        
        return $form;
    }

    /**
     * Implements form validation
     *
     * @param $form
     * @param $form_state
     */
    public function validate($form, &$form_state)
    {
        if (($key = $form_state['values']['key']) && ($secret = $form_state['values']['secret'])) {

            $this->iMonezaService
                ->setManagementApiKey($key)
                ->setManagementApiSecret($secret)
                ->setManageApiUrl($this->options->getManageApiUrl(Model\Options::GET_DEFAULT))
                ->setAccessApiUrl($this->options->getAccessApiUrl(Model\Options::GET_DEFAULT));

            if (!($this->propertyOptions = $this->iMonezaService->getProperty())) {
                form_set_error('key', $this->iMonezaService->getLastError());
            }
        }
    }

    /**
     * Handles the form submission
     *
     * @param $form
     * @param $form_state
     * 
     * @todo schedule cron to include dynamically created items
     */
    public function submit($form, &$form_state) {
        $this->options
            ->setManageApiKey($form['manage_api']['key']['#value'])
            ->setManageApiSecret($form['manage_api']['secret']['#value'])
            ->setPropertyTitle($this->propertyOptions->getTitle())
            ->setDynamicallyCreateResources($this->propertyOptions->isDynamicallyCreateResources())
            ->setAccessControl(Model\Options::ACCESS_CONTROL_CLIENT)
            ->setPricingGroupsBubbleDefaultToTop($this->propertyOptions->getPricingGroups());
        
        $this->saveOptions($this->options);

        drupal_set_message(t("Way to go!  Now, let's finish this up."));
    }
}