<?php
/**
 * First time form
 *
 * @author Aaron Saray
 */

namespace iMoneza\Drupal\Form;

/**
 * Class FirstTime
 * @package iMoneza\Drupal\Form
 */
class FirstTime extends FormAbstract
{
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
            ]
        ];
        
        $form['manage_api_key'] = array(
            '#type' =>  'textfield',
            '#title'  =>  t('Resource Management API Key:'),
            '#required' => true
        );
        $form['manage_api_secret'] = array(
            '#type' =>  'textfield',
            '#title'  =>  t('Resource Management API Secret:'),
            '#required' =>  true
        );

        $form['actions']['submit'] = array(
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
        form_set_error('manage_api_key', 'Invalid, son!');
    }

    /**
     * Handles the form submission
     *
     * @param $form
     * @param $form_state
     */
    public function submit($form, &$form_state) {
        $this->options
            ->setManageApiKey($form['manage_api_key']['#value'])
            ->setManageApiSecret($form['manage_api_secret']['#value']);

        $this->saveOptions();

        drupal_set_message(t('Way to go!'));
    }
}