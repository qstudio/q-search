<?php

namespace q\search\ui;

use q\core;
// use q\core\helper as h;

// Q Theme ##
use q\search\core\helper as h;

// load it up ##
// \q\search\ui\enqueue::run();

class enqueue extends \q_search {

    public static function run()
    {

        

    }


	/*
	* Script enqueuer
	*
	* @since  2.0
	*/
	public static function wp_enqueue_scripts() {

		// h::log( 'd:>adding q search assets..' );
		// h::log( 'd:>'.h::get( "ui/asset/css/q.search.css", 'return' ) );

		\wp_register_style( 'q-search-css', h::get( "ui/asset/css/q.search.css", 'return' ), '', self::version, 'all' );
		\wp_enqueue_style( 'q-search-css' );

		// add JS ## -- after all dependencies ##
		\wp_enqueue_script( 'q-search-js', h::get( "ui/asset/javascript/q.search.js", 'return' ), array( 'jquery' ), self::version, true );

		// pass variable values defined in parent class ##
		\wp_localize_script( 'q-search-js', 'q_search', array(
				'ajaxurl'           => \admin_url( 'admin-ajax.php', \is_ssl() ? 'https' : 'http' ), 
				'debug'             => self::$debug,
				'site_name'         => \get_bloginfo("sitename")
			,   'search'            => __( 'Search', 'q-search' )
			,   'search_results_for'=> __( 'Results', 'q-search' )
			//,   'on_load_text' => __( 'Search & filter to see results', 'q-search' )
		));

	  }

}
