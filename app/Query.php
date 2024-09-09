<?php

namespace JetSmartFiltersProximity;

use Carbon_Fields\Container;
use Carbon_Fields\Field;


class Query
{

    public static function register()
    {
        $instance = new self;

        add_filter( 'jet-smart-filters/query/vars', [$instance, 'my_register_filter_query_vars'] );
        add_filter( 'jet-smart-filters/query/add-var', [$instance, 'my_process_filter_query_vars'], 10, 4 );
        add_filter( 'jet-smart-filters/query/meta-query-row', [$instance, 'my_clear_meta_query'] );
        add_filter( 'jet-smart-filters/query/final-query', [$instance, 'final_query'] );
    }


    function my_register_filter_query_vars($vars)
    {
        array_unshift($vars, '_proximity');
        return $vars;
    }


    function my_process_filter_query_vars($value, $key, $var, $query)
    {
        return $value;
    }


    function my_clear_meta_query($row)
    {
        //		$row['compare'] = 'NOT LIKE';
        //if( in_array($row[ 'key' ], ['_proximity']) ) {
        //    $row = [];
        //}
        return $row;
    }


    function final_query($query)
    {
        foreach($query['meta_query'] as $queryAttr) {

            if($queryAttr['key'] === '_proximity') {

                $parts = $queryAttr[ 'value' ];
                if(is_string($parts)) $parts = explode(',', $parts);


                $newMetaQuery = [];
                foreach($query['meta_query'] as $otherQuery) {
                    if($otherQuery['key'] != '_proximity') $newMetaQuery[] = $otherQuery;
                }
                $query['meta_query'] = $newMetaQuery;

                if( count($parts) !== 5 ) {
                    return $query;
                }


                $table = esc_sql($parts[ 0 ]);
                $lat = esc_sql($parts[ 2 ]);
                $lng = esc_sql($parts[ 3 ]);
                $dis = esc_sql($parts[ 4 ]);

                global $wpdb;
                $subquery = "SELECT
					p.ID,
					latitude.meta_value as locLat,
					longitude.meta_value as locLong,
					( 6371 * acos(
					cos( radians( $lat ) )
					* cos( radians( latitude.meta_value ) )
					* cos( radians( longitude.meta_value ) - radians( $lng ) )
					+ sin( radians( $lat ) )
					* sin( radians( latitude.meta_value ) )
					) )
					AS distance
				FROM $wpdb->posts p
				INNER JOIN $wpdb->postmeta latitude ON p.ID = latitude.post_id
				INNER JOIN $wpdb->postmeta longitude ON p.ID = longitude.post_id
				WHERE 1 = 1 
				AND p.post_type = '$table'
				AND p.post_status = 'publish'
				AND latitude.meta_key = '_lat'
				AND longitude.meta_key = '_lng'
				HAVING distance < $dis
				ORDER BY distance DESC";

                $exists = $wpdb->get_results($subquery);

                $ids = [];
                foreach ( $exists as $item ) {
                    $ids[] = $item->ID;
                }

                if(count($ids) === 0) $ids = [-1];

                $query[ 'post__in' ] = $ids;
            }
        }

        return $query;
    }

}
