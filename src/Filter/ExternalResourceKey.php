<?php
/**
 * Generate an external resource key
 *
 * @author Aaron Saray
 */

namespace iMoneza\Drupal\Filter;

/**
 * Class ExternalResourceKey
 * @package iMoneza\Wordpress\Filter
 */
class ExternalResourceKey
{
    /**
     * Get the external resource key
     * @param \stdClass $node
     * @return string
     */
    public function __invoke(\stdClass $node)
    {
        return sprintf('dp7-%s', $node->nid);
    }
}