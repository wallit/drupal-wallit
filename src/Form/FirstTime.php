<?php
/**
 *
 *
 * @author Aaron Saray
 */

namespace iMoneza\Drupal\Form;


class FirstTime
{
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
        });
        $form['#submit'] = array(function() {
            die('submitting');
        });

        return $form;
    }
}