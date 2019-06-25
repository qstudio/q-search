<?php

namespace q\search\theme;

use q\search\core\helper as helper;
use q\search\core\core as core;
#use q\search\user\user as user;

// load it up ##
\q\search\theme\theme::run();

class theme extends \q_search {

	public static function run()
  	{

		// image sizes ##
		\add_action( 'after_setup_theme', array( get_class(), 'add_image_sizes' ) );

	  }



	/*
	* Script enqueuer
	*
	* @since  2.0
	*/
	public static function wp_enqueue_scripts() {

		\wp_register_style( 'q-search-css', helper::get( "theme/css/q-search.css", 'return' ), '', self::version, 'all' );
		\wp_enqueue_style( 'q-search-css' );

		// history ##
		#\wp_register_script('jquery-history-js', helper::get( "theme/javascript/jquery.history.js" , 'return' ) ,array('jquery'), self::version, true );
		#\wp_enqueue_script('jquery-history-js');

		// add JS ## -- after all dependencies ##
		\wp_enqueue_script( 'q-search-js', helper::get( "theme/javascript/q-search.js", 'return' ), array( 'jquery' ), self::version, true );

		// pass variable values defined in parent class ##
		\wp_localize_script( 'q-search-js', 'q_search', array(
			'ajaxurl'           => \admin_url( 'admin-ajax.php', \is_ssl() ? 'https' : 'http' ), /*, 'https' */ ## add 'https' to use secure URL ##
			'debug'             => self::$debug,
			'site_name'         => \get_bloginfo("sitename")
		,   'search'            => __( 'Search', 'q-search' )
		,   'search_results_for'=> __( 'Results', 'q-search' )
		//,   'on_load_text' => __( 'Search & filter to see results', 'q-search' )
		));

  	}


	/**
	 * Add image sizes crops
	 *
	 * @since        0.1.0
	 */
	public static function add_image_sizes()
	{

		\add_image_size( 'desktop-q-search', 800, 600, true ); // desktop course landing page ##
		\add_image_size( 'handheld-q-search', 600, 400, true ); // handheld course landing page ##

  	}	


	/**
	 * Render the search engine
	 *
	 * @since       0.1
	 * @return      HTML
	 */
	public static function render()
	{

    // helper::log( 'rendering...' );

?>
    <div>
<?php

    // let's check if there are any posts to search ##
    if ( core::has_posts() ) {

		// add inline JS to instatiate AJAX call ##
		self::scripts();

?>
		<div class="q-search-li">
			<div id="q-search-content" class="horizontal">
<?php

			// build filter navigation ##
			self::filters();

			// add AJAX section ##
			self::results();

?>
			</div>
		</div>
	</div>
<?php

    } else {

      	// helper::log( 'has_posts returned zero' );

		// nothing to search :( ##
		self::no_posts();

    }

}




	/**
	 * Message to show when no posts available to search
	 * Message shown is controllable via optional ACF storage in admin
	 *
	 * @since       0.0.5
	 * @return      string      HTML
	 */
	public static function no_posts()
	{

    // grab global $post;
    global $post;
    #pr( $post );

    // check for post_meta field containing string for message ##
    if ( $post && $post->q_search_no_results ) {

		#pr( 'Found string..' );
		$message = $post->q_search_no_results;

    } else {

		// allow message to be passed ##
		$message = __( "No Results Found", 'q-search' ) ;

    }

?>
    <p class="no-results"><?php echo $message; ?></p>
<?php

	}



  /**
   * Results Markup
   *
   * @since    2.0.0
   * @return   String
   */
  public static function result()
  {

    #global $post;

    /*
    <div class="ajax-loaded q-search-default">
        <h3>%title%</h3>
        <a href="%permalink%" title="%title%">
            <img src="%src%" />
        </a>
        <p>%content%</p>
        <a href="%permalink%" title="%title%">Read More</a>
    </div>
    */
?>
    <div class="ajax-loaded  q-search-default">
      	<h3><?php \the_title();?></h3>
		<a href="<?php \the_permalink(); ?>" title="<?php \the_title();?>">
			<?php \the_post_thumbnail(array( 150, 150 )); ?>
		</a>
		<p><?php \the_excerpt(); ?></p>
		<a href="<?php \the_permalink(); ?>" title="<?php \the_title();?>"><?php _e( "Read More", 'q-search' ); ?></a>
    </div>
<?php

  }



  /**
   * Create HTML area to hold AJAX loaded content
   *
   * @since    2.0.0
   * @return   String
   */
  public static function results( $load = false )
  {

?>
    <div id="ajax-content">
      	<div id="q-search-results" class="<?php echo \apply_filters( 'q/search/results/class', 'posts' ); ?>">
<?php

			// run load query ##
			core::query([ 'load' => true ]);

?>
      	</div>
    </div>
<?php

  }




	/**
	 * build list of terms to filter by
	 *
	 * @since       1.7.0
	 * @return      string      HTML for filter nav
	 * @TODO missing keyword, tag, country filters. search by filtering doesn't work
	 */
	protected static function filters()
	{

		// check for passed values or merge defaults ##
		$post_type = array( core::properties( 'post_type' ) );
		$taxonomies = explode( ",", core::properties( 'taxonomies' ) );
		#helper::log( $taxonomies );
		$table = core::properties( 'table' );
		$application = core::properties( 'application' );
		$device = core::properties( 'device' );
		$class = core::properties( 'class' );
		$filter_type = core::properties( 'filter_type' );
		$hide_titles = core::properties( 'hide_titles' );
		$filter_position = core::properties( 'filter_position' );
		$show_count = core::properties( 'show_count' );
		$show_input_text = core::properties( 'show_input_text' );

		// position the filters correctly ##
		$position = $filter_position == 'vertical' ? 'vertical' : 'horizontal' ;

?>
		<form id="q-search-form" class="ajax-filters row <?php echo $position; ?>">
<?php

		// text input ##
		echo self::filter_input();

		// check for user_meta filters ##
		echo self::user_meta();

		// select grid ##
		$grid = core::properties( 'grid_select' );

		$queried_object = \get_queried_object();
		// helper::log($taxonomies);

		if ( 
			$taxonomies 
			&& isset( $taxonomies[0] ) 
			&& $taxonomies[0] > '' 
		) {

			foreach( $taxonomies as $taxonomy ) {

			// clean up ##
			$taxonomy = trim( $taxonomy );

			// get tax ##
			if ( false === $get_taxonomy = core::get_taxonomy( $taxonomy ) ) {

				// helper::log( 'skipping: '.$taxonomy );

				continue;

			}

			if ( 
				$filter_type == 'list'
				&& $hide_titles == 0 
			){

				echo \apply_filters( 'q/search/filter/title', "<h4>{$the_tax_name}</h4>" );

	        }

    	    #pr($term);

			// select or list items ? ##
			if( $taxonomy != 'mos_interest' ) {

				echo "<div class='".$grid."'>"; 
				echo "<div class='selector'>";
				// echo $taxonomy !== 'category' ? "<label>".$get_taxonomy["label"]."</label>" : '';
				echo "<select name='".$taxonomy."' class=\"form-control q-search-select filter-$taxonomy\">";
				
				// check for preselect option ##
				echo "<option selected value=\"\" class=\"default\">".$get_taxonomy["name"]."</option>";

				#wp_die(pr($get_taxonomy["terms"]));
				
				foreach( $get_taxonomy["terms"] as $term ) {

					echo "<option value=\"{$term->term_id}\" data-tax=\"$taxonomy={$term->term_id}\" data-slug=\"{$term->slug}\" >";

					echo "{$term->name}";

					if( $show_count == 1 ) {
						
						echo " ({$term->count})";
			
					}

            		echo "</option>";

				}

				echo "</select>";
				echo "</div></div>";

			} else {

				echo "<div><div class='tags form-group'>";
				echo '<div><label>Choose Interests</label></div>';

				foreach( $get_taxonomy["terms"] as $term ) {

					echo '<div class="tag">';
            		echo '<input name="'.$taxonomy.'[]" id="interest-'.$term->slug.'" value="'.$term->term_id.'" type="checkbox" ';
          
					if ( $term->term_id == $queried_object->term_id ) {

						echo " checked";

            		}
          
					echo '/><label for="interest-'.$term->slug.'">'.$term->name.'</label>';

            		// echo "\" data-tax=\"$taxonomy={$term->term_id}\" data-slug=\"{$term->slug}\"><a href=\"#\" class=\"ajax-filter-label\"><span class=\"checkbox\"></span>{$term->name}</a></label>";
            //                if( $show_count == 1 ) {
            //                  echo " ({$term->count})";
            //                }

            		echo "</div>";

		  		}

				echo '</div></div>';

			}

		} // loop ##

	} // taxs set ##

?>
	<div id="q-search" class="col-12">
        <div class="buttons">
          	<div class="input">
            	<input type="reset" id="reset" class="qs-reset qs-button qs-reset" value="Clear choices">
          	</div>
        </div>
	</div>
</form>
<?php

  	}




	public static function filter_input()
	{

		// is this shown ? ##
		$show_input_text = core::properties( 'show_input_text' );

		if ( ! $show_input_text ) {

			return false;

		}

		// filter grid ##
		$grid = core::properties( 'grid_input' );

		$markup = 
		'<div class="input text input-searcher '.$grid.'">
			<input type="text" value="" name="searcher" id="searcher" placeholder="Keyword" class="searcher filter-selected" />	
		</div>';

		// filter ##
		return \apply_filters( 'q/search/filter/input', $markup );
	
	}



	public static function user_meta()
	{

		// is this shown ? ##
		$user_meta = \apply_filters( 'q/search/user_meta', false );

		if ( 
			! $user_meta 
			|| ! is_array( $user_meta ) // should be passed as an array ##
		) {

			return false;

		}

		// filter grid ##
		$grid = core::properties( 'input' == $user_meta['input'] ? 'grid_input' : 'grid_select' );

		// we need to get all the options values to loop over and show ##
		$options = $user_meta['options'];

		if (
			! $options
			|| ! is_array( $options )
		) {

			helper::log( 'No valid options passed to display' );

			return false;

		}

		$markup = 
			"<div class='{$grid}'> 
				<div class='selector'>
					<select name='user_meta' class='form-control q-search-select filter-user-meta'>
						<option selected value='' class='default'>Filter by ".$user_meta["label"]."</option>
						".self::select_options( [
							'markup' 		=> '<option value="%key%" data-tax="%field%=%key%">%value%</option>',
							'options'		=> $options,
							'args'			=> $user_meta,
							'filter'		=> 'user_meta' // for filter ##
						] )."
					</select>
				</div>
			</div>";

		// filter ##
		return \apply_filters( 'q/search/filter/user_meta', $markup );
	
	}



	public static function filter_select()
	{
	
		// filter grid ##
		$grid = core::properties( 'grid_select' );

		$markup = 
		"<div class='{$grid}'> 
		   <div class='selector'>
			   <select name='user_meta' class='form-control q-search-select filter-user-meta'>
				   <option selected value='' class='default'>Filter by ".$user_meta["label"]."</option>
				   ".self::select_options( [
					   'markup' 	=> '<option value="%key%" data-tax="%field%=%key%" >',
					   'options'	=> $options,
					   'args'		=> $user_meta,
					   'filter'		=> 'user_meta' // for filter ##
				   ] )."
			   </select>
		   </div
	   	</div>";

		// filter ##
		return \apply_filters( 'q/search/filter/select', $markup );

  	}



	public static function select_options( Array $args = null )
	{

		// sanity ##
		if (
			is_null( $args )
			|| ! is_array( $args )
			|| ! isset( $args['markup'] )
			|| ! isset( $args['options'] )
			|| ! is_array( $args['options'] )
			|| ! isset( $args['args'] )
			|| ! isset( $args['args']['field'] )
		) {

			helper::log( 'Malformed data passed to method' );

			return false;

		}

		// start empty ##
		$string = '';

		// loop over each option, add markup ##
		foreach( $args['options'] as $key => $value ) {

			$string .= str_replace( [ '%key%', '%field%', '%value%' ], [ $key, $args['args']['field'], $value ], $args['markup'] );

		}

		// return filtered string ##
		return \apply_filters( 'q/search/select_options/'.$args['filter'], $string );

	}  



	/**
	 * Buid pagination
	 *
	 * @since       1.4.0
	 * @return      String      HTML for pagination
	 */
	public static function pagination( $total_posts, $posts_per_page, $posted )
	{

		// helper::log( 'rebuilding pagination on device:'. $posted["device"] );

		// handheld ##
		switch ( $posted["device"] ) {

			case ( 'handheld' ) :

				self::pagination_handheld( $total_posts, $posts_per_page, $posted );

			break ;

			case ( 'desktop' ) :
			default :

				self::pagination_desktop( $total_posts, $posts_per_page, $posted );

			break ;

		}

	}





	/**
	 * handheld pagination
	 *
	 * @since       1.4.0
	 * @return      String      HTML for pagination
	 */
	public static function pagination_handheld( $total_posts, $posts_per_page )
	{

    // helper::log( 'Loading Handheld Pagination..' );

?>
    <nav class="q-search-pagination">
      	<div class="pagination-inner">
<?php

		if( $_POST && isset($_POST['paged']) && $_POST['paged'] > 1 ) {

          	$page_number = $_POST['paged'];

?>
			<a href='#' class='page-numbers pagelink-1 pagelink' rel="1"><span>&laquo; First</span></a>
			<a class="paginationNav page-numbers prev" rel="prev" href="#"><span>&lsaquo; <?php _e( "Previous", 'q-search' ); ?></span></a>
<?php

        } else {

          	$page_number = 1;

?>
			<a href='#' class='disabled page-numbers' rel=""><span>&laquo; First</span></a>
			<a class="disabled prev" rel="" href="#">&lsaquo; <?php _e( "Previous", 'q-search' ); ?></a>
<?php

        }

        // work out total number of pages ##
        $total_pages = floor( $total_posts / $posts_per_page );
        // helper::log( 'Total Pages: '.$total_pages );

        // check if we need to print pagination ##
        if (
			// ( $posts_per_page * $page_number ) < $total_posts
			// && $posts_per_page < $total_posts
			$page_number >= $total_pages
        ) {

?>
			<a class="disabled page-numbers" rel="" href="#"><span><?php _e( "Next", 'q-search' ); ?> &rsaquo;</span></a>
			<a href='#' class='disabled page-numbers' rel=""><span>Last &raquo;</span></a>
<?php

        } else {

?>
			<a class="paginationNav page-numbers next" rel="next" href="#"><span><?php _e( "Next", 'q-search' ); ?> &rsaquo;</span></a>
			<a href='#' class='page-numbers pagelink-<?php echo $total_pages; ?> pagelink' rel="<?php echo $total_pages; ?>"><span>Last &raquo;</span></a>
<?php

        }

?>
        	<div class="clear"></div>
      	</div>
	</nav>
<?php

  	}



	/**
	 * desktop pagination
	 *
	 * @since       1.4.0
	 * @return      String      HTML for pagination
	 */
	public static function pagination_desktop( $total_posts, $posts_per_page )
	{

    // helper::log( 'Loading Desktop Pagination..' );

?>
    <nav class="q-search-pagination">
      	<div class="pagination-inner">
<?php

        if( $_POST && isset($_POST['paged']) && $_POST['paged'] > 1 ) {

			$page_number = $_POST['paged'];

?>
			<a class="paginationNav page-numbers prev" rel="prev" href="#"><span>&lsaquo;</span></a>
<?php

        } else {

          	$page_number = 1;

        }

?>
        <span class="qs-pages page-numbers-wrapper">
<?php

	#helper::log( $posts_per_page );

	// get paging info ##
	$pagination = core::get_pagination( $total_posts, $posts_per_page, $page_number );
	#helper::log( $pagination );

	// limit number of items shown on screen ##
	$max = 7;

	// work out how many filler links to allow in between next and back arrows ##
	if( $pagination['page_number'] < $max ) {

		// current page is lower than max pages allowed to be shown ##
		$sp = 1;

	} elseif ( $pagination['page_number'] >= ( $pagination['pages'] - floor( $max / 2 ) ) ) {

		// the current page is greater or equal to half the max number allowed to be shown ##
		// current = 1, total pages = 6 - 2 = 4
		// current = 5, total pages = 6 - 2 = 4 - $sp = 4
		$sp = $pagination['pages'] - $max + 1;

	} elseif( $pagination['page_number'] >= $max ) {

		// current page is equal or greater than max ##
		// 1 >= 3 = false
		// 5 >= 3 = true - $sp = 3
		$sp = $pagination['page_number'] - floor( $max / 2 );

	}

	#helper::log( '$sp: '.$sp );

	// If the current page >= $max then show link to 1st page
	if ( $pagination['page_number'] >= $max ) {

?>
  		<a href='#' class='page-numbers pagelink-1 pagelink' rel="1">1</a><a href='#' class="page-numbers dots">&#8230;</a>
<?php

	}

	// Loop though max number of pages shown and show links either side equal to $max / 2 -->
	for( $i = $sp; $i <= ($sp + $max -1); $i++ ) {

		// skip ##
		if( $i > $pagination['pages']) {

			continue;

		}

		// current ##
		if ( $pagination['page_number'] == $i ) {

?>
   	 	<a href="#" class="page-numbers pagelink-<?php echo $i; ?> pagelink current" rel="<?php echo $i; ?>"><?php echo $i; ?></a>
<?php

    	// normal ##
	  	} else {

?>
    	<a href='#' class="page-numbers pagelink-<?php echo $i; ?> pagelink" rel="<?php echo $i; ?>"><?php echo $i; ?></a>
<?php

  		}

	}

	// If the current page is less than the last page minus $max pages divided by 2 ##
	if ( $pagination['page_number'] < ( $pagination['pages'] - floor( $max / 2 ) ) ) {

?>
  		<span class="page-numbers dots">&#8230;</span>
		<a href='#' class="page-numbers pagelink-<?php echo $pagination['pages']; ?> pagelink" rel="<?php echo $pagination['pages']; ?>"><?php echo $pagination['pages']; ?></a>
<?php

	}

?>
        </span>
<?php

		// check if we need to print pagination ##
		if ( ( $posts_per_page * $page_number ) < $total_posts && $posts_per_page < $total_posts ) {

?>
		<a class="paginationNav page-numbers next" rel="next" href="#"><span>Next &rsaquo;</span></a>
<?php

        } // pagination check ##

?>
        	<div class="clear"></div>
      	</div>
    </nav>
<?php

	}



  /**
   * Add inline JS to search page
   *
   * @since       1.7.0
   * @param       array   $post_type
   * @param       string  $class
   * @param       string  $order
   * @param       string  $order_by
   */
  public static function scripts()
  {

    // grab the queried object ##
    $queried_object = \get_queried_object();

	// helper::log( $queried_object );

    // get the page's current taxonomy to filter
    if( isset( $queried_object->term_id ) ) {

      	$queried_object_string = $queried_object->taxonomy."##".$queried_object->term_id;

    } else {

      	$queried_object_string = "qs_null";

    }

    // create nonce ##
    $nonce = \esc_js( \wp_create_nonce( 'q-search-nonce' ) );

?>
    <script type="text/javascript">

        // configure QS_Filters ##
        var QS_CONFIG = {
            ajaxurl:            '<?php echo \home_url( 'wp-admin/admin-ajax.php' ) ?>',
            table:              '<?php echo core::properties( 'table') ; ?>',
            callback:           '<?php echo core::properties( 'callback') ; ?>',
            application:        '<?php echo core::properties( 'application') ; ?>',
            device:             '<?php echo core::properties( 'device') ; ?>',
            post_type:          '<?php echo core::properties( 'post_type' ); ?>',
            posts_per_page:     '<?php echo (int)core::properties( 'posts_per_page' ); ?>',
            taxonomies:         '<?php echo str_replace( " ", "", core::properties( 'taxonomies' ) ); ?>',
            order:              '<?php echo core::properties( 'order' ); ?>',
            order_by:           '<?php echo core::properties( 'order_by' ); ?>',
            filter_type:        '<?php echo core::properties( 'filter_type' ); ?>',
			filter_position:    '<?php echo core::properties( 'filter_position') ; ?>',
			category_name:      '<?php echo core::properties( 'category_name') ; ?>',
        	author_name:       	'<?php echo core::properties( 'author_name') ; ?>',
        	tag:    			'<?php echo core::properties( 'tag') ; ?>',
            queried_object:     '<?php echo $queried_object_string; ?>',
            page_number:        1,
            nonce:              '<?php echo $nonce; ?>'
        };

    </script>
<?php

  	}



	/**
	 * Count total returned posts
	 *
	 * @since   0.4
	 * @param   integer     $count
	 * @return  string      HTML
	 */
	public static function count_results( $count = 0 )
	{	

		// helper::log( core::properties( 'results', 'array' ) );

		printf (
			'<h5 class="mb-5 push-40 q-search-count-results" data-count="%d">%d %s</h5>'
			,   intval( $count )
			,   intval( $count )
			,   intval( $count ) > 1 ? core::properties( 'results', 'array' )[1] : core::properties( 'results', 'array' )[0]
		);

  	}



	/**
	 * Buid No Results
	 *
	 * @since       1.4.0
	 * @return      String      HTML for sad face :(
	 */
	public static function no_results( $string = null )
	{

		// allow message to be passed ##
		$message = ! is_null( $string ) ? $string : core::properties( 'no_results' ) ;

?>
    <div class="no-results text-center">
		<img class="push-20" src="<?php echo helper::get( "theme/css/images/search-no-results.svg", 'return' ); ?>" />
		<h5 class='push-20'><?php echo $message; ?></h5>
		<div>Sorry, that filter combination returned no results.</div>
		<div>Please try different criteria.</div>
    </div>
<?php

    	exit; // stop running now ##

  	}


}