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
class FirstTime
{
    /**
     * @return array The data for the form
     */
    public function __invoke()
    {
        $form['#theme'] = 'imoneza_first_time_form';
        $form['#attached']['css'] = array(
            drupal_get_path('module', 'imoneza') . '/assets/admin.css',
        );

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
        
        $form['#validate'] = array(function()  {
            die('validating');

//            /**
//             * Implements the validation for first time form
//             *
//             * @param $form
//             * @param $form_state
//             */
//            function imoneza_first_time_form_validate($form, &$form_state) {
//                $apiKey = $form_state['values']['manage_api_key'];
//                $apiSecret = $form_state['values']['manage_api_secret'];
//
//                form_set_error('manage_api_secret', 'this is a test error.');
//            }
        });
        $form['#submit'] = array(function() {
            die('submitting');
        });

        return $form;
    }
}