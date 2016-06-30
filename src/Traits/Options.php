<?php
/**
 * Trait for options functionality
 *
 * @author Aaron Saray
 */

namespace iMoneza\Drupal\Traits;

/**
 * Class Options
 * @package iMoneza\Drupal\Traits
 */
trait Options
{
    /**
     * @param \iMoneza\Drupal\Model\Options $options
     * @return $this
     */
    protected function saveOptions(\iMoneza\Drupal\Model\Options $options)
    {
        $options->setLastUpdatedNow();
        variable_set('imoneza-options', $options);
        return $this;
    }
}