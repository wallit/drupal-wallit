<?php
/**
 * @file form abstract
 *
 * @author Aaron Saray
 */

namespace iMoneza\Drupal\Form;

use iMoneza\Drupal\Model;

/**
 * Class FormAbstract
 * @package iMoneza\Drupal\Form
 */
abstract class FormAbstract
{
    /**
     * @var Model\Options
     */
    protected $options;

    /**
     * InternalConfig constructor.
     * @param Model\Options $options
     */
    public function __construct(Model\Options $options)
    {
        $this->options = $options;
    }

    /**
     * Save the options
     */
    protected function saveOptions()
    {
        variable_set('imoneza-options', $this->options);
    }

    /**
     * Validate the form
     * @param $form
     * @param $form_state
     * @return void
     */
    abstract public function validate($form, &$form_state);

    /**
     * Submit the form details
     * @param $form
     * @param $form_state
     * @return void
     */
    abstract public function submit($form, &$form_state);
}