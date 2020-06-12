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
		\add_filter( 'q/config/get/all', [ get_class(), 'get' ], 10, 1 );

    }



	/**
	 * Get configuration from /q.config.php
	 *
	 * @used by filter q/config/get/all
	 *
	 * @return		Array $array -- must return, but can be empty ##
	 */
	public static function get( Array $config = null ): Array {

		// starts with an empty array ##
		$array = [];

		// load config from JSON ##
		if (
			$array = include( self::get_plugin_path('q.config.php') )
		){

			// check if we have a 'config' key.. and take that ##
			if ( is_array( $array ) ) {

				// merge filtered data into default data ##
				$config = core\method::parse_args( $array, $config );

			}

		}

		// h::log( $config );

		// kick back ##
		return $config;

	}


}
