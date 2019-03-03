<?php

namespace q\search\admin;

// use q\search\core\helper as helper;
#use q\search\core\core as theme;
// use q\search\theme\theme as theme;

// load it up ##
\q\search\admin\ajax::run();

class ajax extends \q_search {

    public static function run()
    {

        // ajax search calls ##
        \add_action( 'wp_ajax_q_search', array( 'q\\search\\core\\core', 'query' ) );
        \add_action( 'wp_ajax_nopriv_q_search', array( 'q\\search\\core\\core', 'query' ) );

    }

}