<?php

namespace q\search\core;

// Q ##
use q\admin;
use q\core;

// Q Theme ##
use q\search\core\helper as h;

// load it up ##
\q\search\core\config::run();

class config extends \q_search {

    public static function run()
    {

		// filter Q Config -- ALL FIELDS [ $array "data" ]##
		// Priority -- Q = 1, Q Plugin = 10, Q Parent = 100, Q Child = 1000
		\add_filter( 'q/config/get/all', [ get_class(), 'load' ], 10, 1 );

    }



	/**
	 * Get configuration from /q.config.php
	 *
	 * @used by filter q/config/get/all
	 *
	 * @return		Array $array -- must return, but can be empty ##
	 */
	public static function load( $args = null ) {

		// sanity ##
		if ( 
			is_null( $args )
			|| ! is_array( $args )
			|| ! isset( $args['context'] ) 
			|| ! isset( $args['process'] )
		){

			// h::log( $args );
			h::log( 'e:>Missing context and process q_search' );

			return false;

		}

		// return config file ##
		return core\config::load( self::get_plugin_path( 'q.config.php' ), 'q-search' );

	}


}
