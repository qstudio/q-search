<?php

// namespace ##
namespace q\search\core;

/**
 * helper Class
 * @package   q_search\core
 */
class helper extends \q_search {


    /**
    * check if a file exists with environmental fallback
    * first check the active theme ( pulling info from "device-theme-switcher" ), then the plugin
    *
    * @param    $include        string      Include file with path ( from library/  ) to include. i.e. - templates/loop-nothing.php
    * @param    $return         string      return method ( echo, return, require )
    * @param    $type           string      type of return string ( url, path )
    * @param    $path           string      path prefix
    * 
    * @since 0.1
    */
    public static function get( $include = null, $return = 'echo', $type = 'url', $path = "library/" )
    {

        // nothing passed ##
        if ( is_null( $include ) ) { 

            return false;            

        }

        // nada ##
        $template = false; 
        
        #if ( ! defined( 'TEMPLATEPATH' ) ) {

        #    helper::log( 'MISSING for: '.$include.' - AJAX = '.( \wp_doing_ajax() ? 'true' : 'false' ) );

        #}

        // perhaps this is a child theme ##
        if ( 
            defined( 'Q_CHILD_THEME' )
            && Q_CHILD_THEME
            #&& \is_child_theme() 
            && file_exists( get_stylesheet_directory().'/'.$path.$include )
        ) {

            $template = get_stylesheet_directory_uri().'/'.$path.$include; // template URL ##
            
            if ( 'path' === $type ) { 

                $template = get_stylesheet_directory().'/'.$path.$include;  // template path ##

            }

            #if ( self::$debug ) self::log( 'child theme: '.$template );

        }

        // load active theme over plugin ##
        elseif ( 
            file_exists( get_template_directory().'/'.$path.$include ) 
        ) { 

            $template = get_template_directory_uri().'/'.$path.$include; // template URL ##
            
            if ( 'path' === $type ) { 

                $template = get_template_directory().'/'.$path.$include;  // template path ##

            }

            #if ( self::$debug ) self::log( 'parent theme: '.$template );

        // load from Plugin ##
        } elseif ( 
            file_exists( self::get_plugin_path( $path.$include ) )
        ) {

            $template = self::get_plugin_url( $path.$include ); // plugin URL ##

            if ( 'path' === $type ) {
                
                $template = self::get_plugin_path( $path.$include ); // plugin path ##
                
            } 

            #if ( self::$debug ) self::log( 'plugin: '.$template );

        }

        if ( $template ) { // continue ##

            // apply filters ##
            $template = apply_filters( 'q_locate_template', $template );

            // echo or return string ##
            if ( 'return' === $return ) {

                #if ( self::$debug ) helper::log( 'returned' );

                return $template;

            } elseif ( 'require' === $return ) {

                #if ( self::$debug ) helper::log( 'required' );

                return require_once( $template );

            } else {

                #if ( self::$debug ) helper::log( 'echoed..' );

                echo $template;

            }

        }

        // nothing cooking ##
        return false;

    }



    /**
     * Write to WP Error Log
     *
     * @since       1.5.0
     * @return      void
     */
    public static function log( $log )
    {

        if ( true === WP_DEBUG ) {

            $trace = debug_backtrace();
            $caller = $trace[1];

            $suffix = sprintf(
                __( ' - %s%s() %s:%d', 'Q' )
                ,   isset($caller['class']) ? $caller['class'].'::' : ''
                ,   $caller['function']
                ,   isset( $caller['file'] ) ? $caller['file'] : 'n'
                ,   isset( $caller['line'] ) ? $caller['line'] : 'x'
            );

            if ( is_array( $log ) || is_object( $log ) ) {
                error_log( print_r( $log, true ).$suffix );
            } else {
                error_log( $log.$suffix );
            }

        }

    }


    /**
     * Pretty print_r / var_dump
     *
     * @since       0.1
     * @param       Mixed       $var        PHP variable name to dump
     * @param       string      $title      Optional title for the dump
     * @return      String      HTML output
     */
    public static function pr( $var, $title = null )
    {

        if ( $title ) $title = '<h2>'.$title.'</h2>';
        print '<pre class="var_dump">'; echo $title; var_dump($var); print '</pre>';

    }


    /**
     * Pretty print_r / var_dump with wp_die
     *
     * @since       0.1
     * @param       Mixed       $var        PHP variable name to dump
     * @param       string      $title      Optional title for the dump
     * @return      String      HTML output
     */
    public static function pr_die( $var, $title = null )
    {

        \wp_die( self::pr( $var, $title ) );

    }



    
    /**
    * Get current device type from "Device Theme Switcher"
    *
    * @since       0.1
    * @return      string      Device slug
    */
    public static function get_device()
    {

        // property already loaded ##
        if (
            isset( self::$device ) 
            && self::$device 
        ) { 
            return self::$device; 
        }

        // check plugin is active ##
        if ( 
            function_exists( 'is_plugin_active' ) 
            && ! \is_plugin_active( "device-theme-switcher/dts_controller.php" ) 
        ) {

            return self::$device = 'desktop'; // defaults to desktop ##

        }

        // Access the device theme switcher object anywhere in themes or plugins
        // http://wordpress.org/plugins/device-theme-switcher/installation/
        global $dts;

        // device check ##
        if ( is_null ( $dts ) ) {

            $handle = 'desktop';

        } else {

            // theme overwrite approved ##
            if ( ! empty($dts->{$dts->theme_override . "_theme"})) {

                #pr('option 1');
                $handle = $dts->{$dts->theme_override . "_theme"}["stylesheet"];

            // device selected theme loading ##
            } elseif ( ! empty($dts->{$dts->device . "_theme"})) {

                #pr('option 2');
                $handle = $dts->{$dts->device . "_theme"}["stylesheet"];

            // fallback to active theme ##
            } else {

                #pr('option 3');
                $handle = $dts->active_theme["stylesheet"];

            }

        }

        #pr($dts);

        // trim client prefix "ccigh-" from device handle ##
        $handle = ( $handle && false !== strpos( $handle, 'desktop' ) ) ? 'desktop' : 'handheld' ;

        #self::log( 'handle: '.$handle );

        // set and return the property value ##
        return self::$device = $handle;

    }



    /**
     * Return data image element to use for holding images
     * 
     * @todo        Review
     */
    public static function holder( $string = null ) 
    {

        if ( is_null( $string ) ) {

            return 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';

        }

    }


}