<?php

namespace JetSmartFiltersProximity;

use Carbon_Fields\Carbon_Fields;


class Plugin
{

    public function __construct()
    {
        add_action('after_setup_theme', [$this, 'loadCarbon']);

        Shortcode::register();
        Query::register();
    }


    function loadCarbon()
    {
        Carbon_Fields::boot();
    }

}
