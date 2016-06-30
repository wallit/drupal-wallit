<?php
/**
 * @file form abstract
 *
 * @author Aaron Saray
 */

namespace iMoneza\Drupal\Form;

use iMoneza\Drupal\Model;
use iMoneza\Drupal\Traits\Options;

/**
 * Class FormAbstract
 * @package iMoneza\Drupal\Form
 */
abstract class FormAbstract
{
    use Options;
    
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