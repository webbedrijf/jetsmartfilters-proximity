<?php

namespace JetSmartFiltersProximity;

use Carbon_Fields\Container;
use Carbon_Fields\Field;


class Shortcode
{

    public static function register()
    {
        $instance = new self;

        add_shortcode('jetsmartfilters_proximity', [$instance, 'registerShortcode']);
    }


    public function registerShortcode($atts, $content = null)
    {
        if(!array_key_exists('post_type', $atts)) {
            echo 'Table is required.';
        }

        $postType = $atts['post_type'];
        $queryId = isset($atts[ 'query_id' ]) ? $atts[ 'query_id' ] : null;
        $distanceSuffix = isset($atts[ 'distance_suffix' ]) ? $atts[ 'distance_suffix' ] : ' km';
        $distances = isset($atts[ 'distances' ]) ? explode(',', $atts[ 'distances' ]) : [10, 20, 30, 50, 100, 200];

        ?>
        <input id="jet-proximity-loca" type="text" size="50">

        <script>
            let lat = null;
            let lng = null;

            function initialize() {
                var input = document.getElementById('jet-proximity-loca');
                var autocomplete = new google.maps.places.Autocomplete(input);
                google.maps.event.addListener(autocomplete, 'place_changed', function () {
                    var place = autocomplete.getPlace();

                    lat = place.geometry.location.lat();
                    lng = place.geometry.location.lng();

                    proximityQuery();
                });
            }

            function proximityQuery() {
                if(lat == null) return;

                document.getElementById('jet-proximity-check').value = '<?php echo $postType; ?>,' + lat + ',' + lng + ',' + document.getElementById('jet-proximity-distance').value;
                document.getElementById('jet-proximity-check-button').click();
            }

            google.maps.event.addDomListener(window, 'load', initialize);
        </script>

        <select id="jet-proximity-distance" onchange="proximityQuery()">
            <?php
            foreach($distances as $distance) {
                ?>
                <option value="<?php echo $distance; ?>"><?php echo $distance . '' . $distanceSuffix; ?></option>
                <?php
            }
            ?>
        </select>



        <div class="proximity-hide-jet">
        <div class=" jet-smart-filters-checkboxes jet-filter " data-indexer-rule="show" data-show-counter="" data-change-counter="always">
            <div class="jet-checkboxes-list" data-query-type="meta_query" data-query-var="proximity" data-smart-filter="checkboxes" data-filter-id="6313" data-apply-type="ajax-reload" data-content-provider="jet-engine" data-additional-providers="" data-query-id="<?php echo $queryId; ?>" data-active-label="" data-layout-options="{&quot;show_label&quot;:&quot;&quot;,&quot;display_options&quot;:{&quot;show_items_label&quot;:false,&quot;show_decorator&quot;:&quot;yes&quot;,&quot;filter_image_size&quot;:&quot;full&quot;,&quot;show_counter&quot;:false}}" data-query-var-suffix="">
                <div class="jet-checkboxes-list-wrapper">
                    <div class="jet-checkboxes-list__row jet-filter-row">
                        <input type="checkbox" id="jet-proximity-check" checked class="jet-checkboxes-list__input" value="" data-label="Test">
                    </div>
                </div>
            </div>
        </div><div class="apply-filters">
            <button type="button" id="jet-proximity-check-button" class="apply-filters__button">Apply filter</button>
        </div>
        </div>

        <style>
            .proximity-hide-jet {
                display: none;
            }
        </style>

        <?php
    }

}
