<?php
/**
 * Internal Configuration
 *
 * @author Aaron Saray
 */

namespace iMoneza\Drupal\Form;

use iMoneza\Drupal\Model;

/**
 * Class InternalConfig
 * @package iMoneza\Drupal\Form
 */
class InternalConfig extends FormAbstract
{
    /**
     * @return array the data for the form
     */
    public function __invoke()
    {
        $form = [
            '#validate' =>  [[$this, 'validate']],
            '#submit'   =>  [[$this, 'submit']],
            '#theme'    =>  'imoneza_internal_config_form',
            '#attached' =>  [
                'css'   =>  [drupal_get_path('module', 'imoneza') . '/assets/css/admin.css']
            ],
            '#attributes'   =>  [
                'class' =>  ['imoneza-form']
            ]
        ];
        
        $form['urls'] = [
            '#type' =>  'fieldset'
        ];
        
        $form['urls']['manage_api_url'] = array(
            '#type' =>  'textfield',
            '#title'  =>  t('Resource Management API URL:'),
            '#default_value'    =>  $this->options->getManageApiUrl(),
            '#attributes' =>  array(
                'placeholder'   =>  Model\Options::DEFAULT_MANAGE_API_URL
            )
        );
        $form['urls']['access_api_url'] = array(
            '#type' =>  'textfield',
            '#title'  =>  t('Resource Access API URL:'),
            '#default_value'    =>  $this->options->getAccessApiUrl(),
            '#attributes' =>  array(
                'placeholder'   =>  Model\Options::DEFAULT_ACCESS_API_URL
            )
        );
        $form['urls']['javascript_cdn_url'] = array(
            '#type' =>  'textfield',
            '#title'  =>  t('Javascript CDN URL:'),
            '#default_value'    =>  $this->options->getJavascriptCdnUrl(),
            '#attributes' =>  array(
                'placeholder'   =>  Model\Options::DEFAULT_JAVASCRIPT_CDN_URL
            )
        );
        $form['urls']['manage_ui_url'] = array(
            '#type' =>  'textfield',
            '#title'  =>  t('Manage UI URL:'),
            '#default_value'    =>  $this->options->getManageUiUrl(),
            '#attributes' =>  array(
                'placeholder'   =>  Model\Options::DEFAULT_MANAGE_UI_URL
            )
        );
        
        $form['submit'] = array(
            '#type' => 'submit',
            '#value' => t('Save'),

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
        if ($this->notEmptyInvalidUrl($form_state['values']['manage_api_url'])) {
            form_set_error('manage_api_url', t('Please enter a valid URL.'));
        }
        if ($this->notEmptyInvalidUrl($form_state['values']['access_api_url'])) {
            form_set_error('access_api_url', t('Please enter a valid URL.'));
        }
        if ($this->notEmptyInvalidUrl($form_state['values']['javascript_cdn_url'])) {
            form_set_error('javascript_cdn_url', t('Please enter a valid URL.'));
        }
        if ($this->notEmptyInvalidUrl($form_state['values']['manage_ui_url'])) {
            form_set_error('manage_ui_url', t('Please enter a valid URL.'));
        }
    }

    /**
     * If the url is not empty - and its not valid
     * 
     * @param $url
     * @return bool
     */
    protected function notEmptyInvalidUrl($url)
    {
        return (!empty($url) && filter_var($url, FILTER_VALIDATE_URL) === false);
    }

    /**
     * Handles the form submission
     * 
     * @param $form
     * @param $form_state
     */
    public function submit($form, &$form_state) {
        $this->options
            ->setManageApiUrl($form['manage_api_url']['#value'])
            ->setAccessApiUrl($form['access_api_url']['#value'])
            ->setJavascriptCdnUrl($form['javascript_cdn_url']['#value'])
            ->setManageUiUrl($form['manage_ui_url']['#value']);
        
        $this->saveOptions();
        
        drupal_set_message(t('You have successfully updated the settings.'));
    }
}