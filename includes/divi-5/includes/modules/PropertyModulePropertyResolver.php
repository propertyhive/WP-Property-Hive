<?php
namespace PropertyHive\Divi5Sim\Modules;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Shared property resolution helpers for all Property Hive Divi 5 modules.
 *
 * Divi Theme Builder/frontend contexts can make get_the_ID() point at a layout,
 * section, or other non-property post. This resolver tries several candidates and
 * returns the first one that can be instantiated as a PH_Property.
 */
trait PropertyModulePropertyResolver {
    protected static function get_property_candidates() {
        global $property, $post, $wp_query;

        $candidates = array();

        if ( is_object( $property ) && ! empty( $property->id ) ) {
            $candidates[] = array(
                'source'   => 'global_property',
                'id'       => absint( $property->id ),
                'property' => $property,
            );
        }

        $queried_object = function_exists( 'get_queried_object' ) ? get_queried_object() : null;

        $ids = array(
            'queried_object_id' => function_exists( 'get_queried_object_id' ) ? absint( get_queried_object_id() ) : 0,
            'queried_object'    => ( is_object( $queried_object ) && ! empty( $queried_object->ID ) ) ? absint( $queried_object->ID ) : 0,
            'wp_query_post'     => ( is_object( $wp_query ) && is_object( $wp_query->post ?? null ) && ! empty( $wp_query->post->ID ) ) ? absint( $wp_query->post->ID ) : 0,
            'global_post'       => ( is_object( $post ) && ! empty( $post->ID ) ) ? absint( $post->ID ) : 0,
            'get_the_ID'        => function_exists( 'get_the_ID' ) ? absint( get_the_ID() ) : 0,
        );

        foreach ( $ids as $source => $id ) {
            if ( $id ) {
                $candidates[] = array(
                    'source' => $source,
                    'id'     => $id,
                );
            }
        }

        /**
         * Allows projects to add/override candidate IDs when rendering Property Hive
         * Divi modules in unusual template contexts.
         */
        return apply_filters( 'propertyhive_divi5_property_candidates', $candidates );
    }

    protected static function get_property() {
        global $property;

        if ( ! class_exists( '\\PH_Property' ) ) {
            return null;
        }

        foreach ( static::get_property_candidates() as $candidate ) {
            if ( ! empty( $candidate['property'] ) && is_object( $candidate['property'] ) && ! empty( $candidate['property']->id ) ) {
                $property = $candidate['property'];
                return $candidate['property'];
            }

            if ( empty( $candidate['id'] ) ) {
                continue;
            }

            $candidate_property = new \PH_Property( absint( $candidate['id'] ) );

            if ( ! empty( $candidate_property->id ) ) {
                $property = $candidate_property;
                return $candidate_property;
            }
        }

        return null;
    }
}
