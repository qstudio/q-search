<?php

namespace q\theme\ui;

// Q ##
use q\core;

// Q Theme
use q\theme\core\helper as h;

// load it up ##
\q\theme\ui\library::run();

class library extends \q_theme {

    // public static $plugin_version;
    public static $options;

    public static function run()
    {

        // load templates ##
        self::load_properties();

        if ( ! \is_admin() ) {

        }

        // add extra options in libraries select API ##
        // \add_filter( 'acf/load_field/name=q_option_library', [ get_class(), 'filter_acf_library' ], 10, 1 );
        
    }



    
    /**
     * Add new libraries to Q Settings via API
     * 
     * @since 2.3.0
     */
    public static function filter_acf_library( $field )
    {

        // h::log( $field['choices'] );
        // h::log( $field['default_value'] );

        // pop on a new choice ##
        // $field['choices']['bootstrapgrid'] = 'Bootstrap Grid CSS';

        // make it selected ##
        // $field['default_value'][] = 'bootstrapgrid';

        // h::log( $field['choices'] );
        // h::log( $field['default_value'] );

        // return $field;

    }


    /**
    * Load Properties
    *
    * @since        2.0.0
    */
    private static function load_properties()
    {

        if ( ! class_exists( '\q\core\option' ) ) {

            h::log( 'e:>Q classes are required, install required plugin.' );

        } else {

            // grab ALL options ##
            self::$options = core\option::get();
            #h::log( self::$options );

        }

    }


}
