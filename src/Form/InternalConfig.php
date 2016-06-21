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
class InternalConfig
{
    public function __invoke()
    {
        $form['#theme'] = 'imoneza_internal_config_form';
        $form['#attached']['css'] = array(
            drupal_get_path('module', 'imoneza') . '/assets/admin.css',
        );

        $form['manage_api_url'] = array(
            '#type' =>  'textfield',
            '#title'  =>  t('Resource Management API URL:'),
            '#attributes' =>  array(
                'placeholder'   =>  Model\Options::DEFAULT_MANAGE_API_URL
            )
        );
        $form['access_api_url'] = array(
            '#type' =>  'textfield',
            '#title'  =>  t('Resource Access API URL:'),
        );
        $form['javascript_cdn_url'] = array(
            '#type' =>  'textfield',
            '#title'  =>  t('Javascript CDN URL:'),
        );
        $form['manage_ui_url'] = array(
            '#type' =>  'textfield',
            '#title'  =>  t('Manage UI URL:'),
        );

        $form['actions']['submit'] = array(
            '#type' =>  'submit',
            '#value'  =>  t('Save')
        );

        return $form;
    }
}