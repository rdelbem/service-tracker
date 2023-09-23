<?php

namespace ServiceTracker\includes;

defined('WPINC') or die();

class STOLMCServiceTrackerPermalinkValidator
{
    public function isPermalinkStructureValid()
    {
        if (get_option('permalink_structure') !== '/%postname%/') {
            return false;
        }

        return true;
    }
}