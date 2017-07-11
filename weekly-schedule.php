<?php
/*Plugin Name: Weekly Schedule
Plugin URI: http://ylefebvre.ca/wordpress-plugins/weekly-schedule
Description: A plugin used to create a page with a list of programs for your activity
Version: 4.0.0
Text Domain: weekly-schedule
Domain Path: /languages/
Author: Alexis Collin
Author URI: http://mondayking.com
Copyright 2017  Alexis Collin  (email : alecollin@gmail.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA*/

$ws_pagehooktop          = "";
$ws_pagehookmoderate     = "";
$ws_pagehooksettingssets = "";
$ws_pagehookstylesheet   = "";
$ws_pagehookreciprocal   = "";

$wsstylesheet = "";

define( 'WEEKLY_SCHEDULE_ADMIN_PAGE_NAME', 'weekly-schedule' );

global $accesslevelcheck;
$accesslevelcheck = '';

$genoptions = get_option( "WeeklyScheduleGeneral" );

if ( !isset( $genoptions['accesslevel'] ) || empty( $genoptions['accesslevel'] ) ) {
	$genoptions['accesslevel'] = 'admin';
}

switch ( $genoptions['accesslevel'] ) {
	case 'admin':
		$accesslevelcheck = 'manage_options';
		break;

	case 'editor':
		$accesslevelcheck = 'manage_categories';
		break;

	case 'author':
		$accesslevelcheck = 'publish_posts';
		break;

	case 'contributor':
		$accesslevelcheck = 'edit_posts';
		break;

	case 'subscriber':
		$accesslevelcheck = 'read';
		break;

	default:
		$accesslevelcheck = 'manage_options';
		break;
}

function ws_db_prefix() {
	global $wpdb;
	if ( method_exists( $wpdb, "get_blog_prefix" ) ) {
		return $wpdb->get_blog_prefix();
	} else {
		return $wpdb->prefix;
	}
}

function ws_install() {
	global $wpdb;

	if ( function_exists( 'is_multisite' ) && is_multisite() ) {
		if ( isset( $_GET['networkwide'] ) && ( $_GET['networkwide'] == 1 ) ) {
			$originalblog = $wpdb->blogid;

			$bloglist = $wpdb->get_col( 'SELECT blog_id FROM ' . $wpdb->blogs );
			foreach ( $bloglist as $blog ) {
				switch_to_blog( $blog );
				ws_create_table_and_settings();
			}
			switch_to_blog( $originalblog );

			return;
		}
	}
	ws_create_table_and_settings();
}

function ws_new_network_site( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {
	global $wpdb;

	if ( ! function_exists( 'is_plugin_active_for_network' ) )
		require_once( ABSPATH . '/wp-admin/includes/plugin.php' );

	if ( is_plugin_active_for_network( 'weekly-schedule/weekly-schedule.php' ) ) {
		$originalblog = $wpdb->blogid;
		switch_to_blog( $blog_id );
		ws_create_table_and_settings();
		switch_to_blog( $originalblog );
	}
}

function ws_create_table_and_settings() {
	global $wpdb;

	$wpdb->wscategories = ws_db_prefix() . 'wscategories';

	$result = $wpdb->query(
		"
			CREATE TABLE IF NOT EXISTS `$wpdb->wscategories` (
				`id` int(10) unsigned NOT NULL auto_increment,
				`name` varchar(255) CHARACTER SET utf8 NOT NULL,
				`scheduleid` int(10) default NULL,
				`backgroundcolor` varchar(7) NULL,
				PRIMARY KEY  (`id`)
				) "
	);

	$catsresult = $wpdb->query(
		"
			SELECT * from `$wpdb->wscategories`"
	);

	if ( !$catsresult ) {
		$result = $wpdb->query(
			"
			INSERT INTO `$wpdb->wscategories` (`name`, `scheduleid`, `backgroundcolor`) VALUES
			('Default', 1, NULL)"
		);
	}

	$wpdb->wsdays = ws_db_prefix() . 'wsdays';

	$result = $wpdb->query(
		"
			CREATE TABLE IF NOT EXISTS `$wpdb->wsdays` (
				`id` int(10) unsigned NOT NULL,
				`name` varchar(12) CHARACTER SET utf8 NOT NULL,
				`rows` int(10) unsigned NOT NULL,
				`scheduleid` int(10) NOT NULL default '0',
				PRIMARY KEY  (`id`, `scheduleid`)
				) "
	);

	$daysresult = $wpdb->query(
		"
			SELECT * from `$wpdb->wsdays`"
	);

	if ( !$daysresult ) {
		$result = $wpdb->query(
			"
			INSERT INTO `$wpdb->wsdays` (`id`, `name`, `rows`, `scheduleid`) VALUES
			(1, 'Sun', 1, 1),
			(2, 'Mon', 1, 1),
			(3, 'Tue', 1, 1),
			(4, 'Wed', 1, 1),
			(5, 'Thu', 1, 1),
			(6, 'Fri', 1, 1),
			(7, 'Sat', 1, 1)"
		);
	}

	$wpdb->wsitems = ws_db_prefix() . 'wsitems';

	$item_table_creation_query = "
			CREATE TABLE " . $wpdb->wsitems . " (
				id int(10) unsigned NOT NULL auto_increment,
				name varchar(255) CHARACTER SET utf8,
				description text CHARACTER SET utf8 NOT NULL,
				address varchar(255) NOT NULL,
				starttime float unsigned NOT NULL,
				duration float NOT NULL,
				row int(10) unsigned NOT NULL,
				day int(10) unsigned NOT NULL,
				category int(10) unsigned NOT NULL,
				scheduleid int(10) NOT NULL default '0',
                backgroundcolor varchar(7) NULL,
                titlecolor varchar(7) NULL,
				UNIQUE KEY  ( id, scheduleid )
			);";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $item_table_creation_query );

	$upgradeoptions = get_option( 'WS_PP' );

	if ( $upgradeoptions != false ) {
		if ( $upgradeoptions['version'] != '2.0' ) {
			delete_option( "WS_PP" );

			$wpdb->query( "ALTER TABLE `$wpdb->wscategories` ADD scheduleid int(10)" );
			$wpdb->query( "UPDATE `$wpdb->wscategories` set scheduleid = 1" );

			$wpdb->query( "ALTER TABLE `$wpdb->wsitems` ADD scheduleid int(10)" );
			$wpdb->query( "ALTER TABLE `$wpdb->wsitems` CHANGE `id` `id` INT( 10 ) UNSIGNED NOT NULL" );
			$wpdb->query( "ALTER TABLE `$wpdb->wsitems` DROP PRIMARY KEY" );
			$wpdb->query( "ALTER TABLE `$wpdb->wsitems` ADD PRIMARY KEY (id, scheduleid)" );
			$wpdb->query( "ALTER TABLE `$wpdb->wsitems` CHANGE `id` `id` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT" );
			$wpdb->query( "UPDATE `$wpdb->wsitems` set scheduleid = 1" );

			$wpdb->query( "ALTER TABLE `$wpdb->wsdays` ADD scheduleid int(10)" );
			$wpdb->query( "ALTER TABLE `$wpdb->wsdays` DROP PRIMARY KEY" );
			$wpdb->query( "ALTER TABLE `$wpdb->wsdays` ADD PRIMARY KEY (id, scheduleid)" );
			$wpdb->query( "UPDATE `$wpdb->wsdays` set scheduleid = 1" );

			$upgradeoptions['adjusttooltipposition'] = true;
			$upgradeoptions['schedulename']          = "Default";

			update_option( 'WS_PP1', $upgradeoptions );

			$genoptions['stylesheet']           = $upgradeoptions['stylesheet'];
			$genoptions['numberschedules']      = 2;
			$genoptions['debugmode']            = false;
			$genoptions['includestylescript']   = $upgradeoptions['includestylescript'];
			$genoptions['frontpagestylescript'] = false;
			$genoptions['version']              = "2.0";
			$genoptions['accesslevel']          = 'admin';

			update_option( 'WeeklyScheduleGeneral', $genoptions );
		}
	}

	$options = get_option( 'WS_PP1' );

	if ( $options == false ) {
		$options['starttime']             = 19;
		$options['endtime']               = 22;
		$options['timedivision']          = 0.5;
		$options['tooltipwidth']          = 300;
		$options['tooltiptarget']         = 'right center';
		$options['tooltippoint']          = 'left center';
		$options['tooltipcolorscheme']    = 'ui-tooltip';
		$options['displaydescription']    = "tooltip";
		$options['daylist']               = "";
		$options['timeformat']            = "24hours";
		$options['layout']                = 'horizontal';
		$options['adjusttooltipposition'] = true;
		$options['schedulename']          = "Default";
		$options['linktarget']            = "newwindow";

		update_option( 'WS_PP1', $options );
	}

	$genoptions = get_option( 'WeeklyScheduleGeneral' );

	if ( $genoptions == false ) {
		ws_reset_gen_options( 'set_and_return' );
	} elseif ( isset( $genoptions['version'] ) && $genoptions['version'] == '2.0' ) {
		$genoptions['version'] = '2.3';
		$wpdb->query( "ALTER TABLE " . ws_db_prefix() . "wsdays CHANGE name name VARCHAR( 64 )  NOT NULL" );

		update_option( 'WeeklyScheduleGeneral', $genoptions );
	} elseif ( isset( $genoptions['version'] ) && $genoptions['version'] == '2.3' ) {
		$genoptions['version'] = '2.4';
		update_option( 'WeeklyScheduleGeneral', $genoptions );

		for ( $counter = 1; $counter <= $genoptions['numberschedules']; $counter += 1 ) {
			$colors    = array(
                'navy'    => 'qtip-navy',
                'blue'    => 'qtip-blue',
                'aqua'    => 'qtip-aqua',
                'teal'    => 'qtip-teal',
                'olive'   => 'qtip-olive',
                'green'   => 'qtip-green',
                'lime'    => 'qtip-lime',
                'yellow'  => 'qtip-yellow',
                'orange'  => 'qtip-orange',
                'red'     => 'qtip-red',
                'fuchsia' => 'qtip-fuchsia',
                'purple'  => 'qtip-purple',
                'maroon'  => 'qtip-maroon',
                'white'   => 'qtip-white',
                'gray'    => 'qtip-gray',
                'silver'  => 'qtip-silver',
                'black'   => 'qtip-black',
            );
			$positions = array( 'topLeft' => 'top left', 'topMiddle' => 'top center', 'topRight' => 'top right', 'rightTop' => 'right top', 'rightMiddle' => 'right center', 'rightBottom' => 'right bottom', 'bottomLeft' => 'bottom left', 'bottomMiddle' => 'bottom center', 'bottomRight' => 'bottom right', 'leftTop' => 'left top', 'leftMiddle' => 'left center', 'leftBottom' => 'left bottom' );

			$schedulename = 'WS_PP' . $counter;
			$options      = get_option( $schedulename );

			$options['tooltipcolorscheme'] = $colors[$options['tooltipcolorscheme']];
			$options['tooltiptarget']      = $positions[$options['tooltiptarget']];
			$options['tooltippoint']       = $positions[$options['tooltippoint']];

			update_option( $schedulename, $options );
		}
	} elseif ( isset( $genoptions['version'] ) && $genoptions['version'] <= 2.6 ) {
		$genoptions['version'] = '2.7';
		update_option( 'WeeklyScheduleGeneral', $genoptions );

		$wpdb->query( "ALTER TABLE `" . ws_db_prefix() . "wscategories` ADD COLUMN `backgroundcolor` varchar(7) NULL" );

		$wpdb->query( "ALTER TABLE  `" . ws_db_prefix() . "wsitems` CHANGE `name`  `name` VARCHAR( 255 ) NULL" );
	}
}

function ws_reset_gen_options( $setoptions = 'return' ) {
	$genoptions['stylesheet']           = 'stylesheettemplate.css';
	$genoptions['numberschedules']      = 2;
	$genoptions['debugmode']            = false;
	$genoptions['includestylescript']   = '';
	$genoptions['frontpagestylescript'] = false;
	$genoptions['version']              = '2.7';
	$genoptions['accesslevel']          = 'admin';
	$genoptions['csvdelimiter']         = ',';

	$stylesheetlocation           = plugins_url( $genoptions['stylesheet'], __FILE__ );
	$genoptions['fullstylesheet'] = file_get_contents( $stylesheetlocation );

	if ( $setoptions == 'set_and_return' ) {
		update_option( 'WeeklyScheduleGeneral', $genoptions );
	}

	return $genoptions;
}

function ws_uninstall() {
	$genoptions = get_option( 'WeeklyScheduleGeneral' );

	if ( $genoptions != '' ) {
		if ( isset( $genoptions['stylesheet'] ) && isset( $genoptions['fullstylesheet'] ) && !empty( $genoptions['stylesheet'] ) && empty( $genoptions['fullstylesheet'] ) ) {
			$stylesheetlocation           = plugins_url( $genoptions['stylesheet'], __FILE__ );
			$genoptions['fullstylesheet'] = file_get_contents( $stylesheetlocation );

			update_option( 'WeeklyScheduleGeneral', $genoptions );
		}
	}
}

register_activation_hook( __FILE__, 'ws_install' );
register_activation_hook( __FILE__, 'ws_uninstall' );

if ( is_admin() && !class_exists( 'WS_Admin' ) ) {
  //include(plugin_dir_path( __FILE__ ).'includes/WS_Admin.php');
    class WS_Admin {
		function WS_Admin() {
            $pagenow = isset( $_REQUEST['page'] ) ? $_REQUEST['page'] : "";
            if (is_admin() && $pagenow=='weekly-schedule') {
                wp_enqueue_style( 'ws-admin', plugins_url("weekly-schedule") . "/ws-admin.css" );
                wp_enqueue_style( 'ws-fontawesome', "https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" );
                wp_enqueue_style('wp-color-picker');
                wp_enqueue_script('ws-colorpicker', plugins_url("weekly-schedule") . "/js/ws-colorpicker.js", array('wp-color-picker'), false, true );
                wp_enqueue_script( 'ws-modal',      plugins_url("weekly-schedule") . "/js/ws-modal.js",       array('jquery'),          false, true );
            }
            function ws_load_translation_files() {
                load_plugin_textdomain('ws', false, basename( dirname( __FILE__ ) ) . '/languages/');
            }
            add_action('plugins_loaded', 'ws_load_translation_files');
            // adds the menu item to the admin interface
            add_action( 'admin_menu', array( $this, 'add_config_page' ) );
		}

		function add_config_page() {
			global $wpdb, $ws_pagehooktop, $ws_pagehookgeneraloptions, $ws_pagehookstylesheet;
			global $accesslevelcheck;
			$ws_pagehooktop            = add_menu_page( __('Weekly Schedule','ws') .' - ' . __( 'General Options', 'ws' ), __('Weekly Schedule','ws'), $accesslevelcheck, WEEKLY_SCHEDULE_ADMIN_PAGE_NAME, array( $this, 'config_page' ), plugins_url( 'icons/calendar-icon-16.png', __FILE__ ) );
			$ws_pagehookgeneraloptions = add_submenu_page( WEEKLY_SCHEDULE_ADMIN_PAGE_NAME, __('Weekly Schedule','ws') .' - ' . __( 'General Options', 'ws' ), __( 'General Options', 'ws' ), $accesslevelcheck, WEEKLY_SCHEDULE_ADMIN_PAGE_NAME, array( $this, 'config_page' ) );
			$ws_pagehookstylesheet     = add_submenu_page( WEEKLY_SCHEDULE_ADMIN_PAGE_NAME, __('Weekly Schedule','ws') .' - ' . __( 'Stylesheet', 'ws' ), __( 'Stylesheet', 'ws' ), $accesslevelcheck, 'weekly-schedule-stylesheet', array( $this, 'stylesheet_config_page' ) );

			//add_options_page('Weekly Schedule for Wordpress', 'Weekly Schedule', 9, basename(__FILE__), array('WS_Admin','config_page'));
			add_filter( 'plugin_action_links', array( $this, 'filter_plugin_actions' ), 10, 2 );
		} // end add_WS_config_page()

		function filter_plugin_actions( $links, $file ) {
			//Static so we don't call plugin_basename on every plugin row.
			static $this_plugin;
			if ( !$this_plugin ) {
				$this_plugin = plugin_basename( __FILE__ );
			}
			if ( $file == $this_plugin ) {
				$settings_link = '<a href="admin.php?page=weekly-schedule">' . __( 'Settings' ) . '</a>';

				array_unshift( $links, $settings_link ); // before other links
			}

			return $links;
		}

		function stylesheet_config_page() {
			$genoptions = get_option( 'WeeklyScheduleGeneral' );

			if ( isset( $_POST['resetstyle'] ) ) {
				$stylesheetlocation = plugins_url( 'stylesheettemplate.css', __FILE__ );

				$genoptions['fullstylesheet'] = @file_get_contents( $stylesheetlocation );

				update_option( 'WeeklyScheduleGeneral', $genoptions );
				echo '<div id="warning" class="updated fade"><p><strong>Reset stylesheet to default.</strong></div>';
			}

			if ( isset( $_POST['submitstyle'] ) ) {
				$genoptions['fullstylesheet'] = $_POST['fullstylesheet'];

				update_option( 'WeeklyScheduleGeneral', $genoptions );
			}
            /* TEMPLATE CSS EDITOR */
			include(plugin_dir_path( __FILE__ ).'views/css-editor.php');
		}
        function get_select_option_layout ($layout) {
            $opt = "";
            $layouts = array( "horizontal" => "Horizontal", "vertical" => "Vertical" );
            foreach ( $layouts as $key => $layout ) {
                if ( $key == $layout ) {
                    $samedesc = "selected='selected'";
                } else {
                    $samedesc = "";
                }
                $opt .= "<option value='" . $key . "' " . $samedesc . ">" . $layout . "\n";
            }
            return $opt;
        }
        function get_select_option_timedivision($timediv) {
            $opt = "";
            $timedivisions = array(
                "0.25" => __('Quarter-Hourly (15 min intervals)','ws'),
                ".50"  => __('Half-Hourly (30 min intervals)','ws'),
                "1.0"  => __('Hourly (60 min intervals)','ws'),
                "2.0"  => __('Bi-Hourly (120 min intervals)','ws'),
                "3.0"  => __('Tri-Hourly (180 min intervals)','ws')
            );
            foreach ( $timedivisions as $key => $timedivision ) {
                if ( $key == $timediv ) {
                    $sametime = "selected='selected'";
                } else {
                    $sametime = "";
                }
                $opt .= "<option value='" . $key . "' " . $sametime . ">" . $timedivision . "\n";
            }
            return $opt;
        }
        function get_select_option_timeformat ($timeformat) {
            $opt = "";
            $descriptions = array(
                "24hours"      => __('24 Hours (e.g. 17h30)','ws'),
                "24hourscolon" => __('24 Hours with Colon (e.g. 17:30)','ws'),
                "12hours"      => __('12 Hours (e.g. 1:30pm)','ws')
            );
            foreach ( $descriptions as $key => $description ) {
                if ( $key == $timeformat ) {
                    $samedesc = "selected='selected'";
                } else {
                    $samedesc = "";
                }
                $opt .= "<option value='" . $key . "' " . $samedesc . ">" . $description . "\n";
            }
            return $opt;
        }
        function get_select_option_displaydescription($displaydescription) {
            $opt = "";
            $descriptions = array(
                "tooltip" => __('Show as tooltip','ws'),
                "cell"    => __('Show in cell after item name','ws'),
                "none"    => __('Do not display','ws')
            );
            foreach ( $descriptions as $key => $description ) {
                if ( $key == $displaydescription ) {
                    $samedesc = "selected='selected'";
                } else {
                    $samedesc = "";
                }
                $opt .= "<option value='" . $key . "' " . $samedesc . ">" . $description . "\n";
            }
            return $opt;
        }
        function get_select_option_time ($options, $selected=0) {
            $opt = "";
            $timedivider = ( in_array( $options['timedivision'], array( '1.0', '2.0', '3.0' ) ) ? '1.0' : $options['timedivision'] );
            $maxtime     = 24 + $timedivider;
            for ( $i = 0; $i < $maxtime; $i += $timedivider ) {
                if ( $options['timeformat'] == '24hours' || $options['timeformat'] == '24hourscolon' ) {
                    $hour = floor( $i );
                } elseif ( $options['timeformat'] == '12hours' ) {
                    if ( $i < 12 ) {
                        $timeperiod = "am";
                        if ( $i == 0 ) {
                            $hour = 12;
                        } else {
                            $hour = floor( $i );
                        }
                    } else {
                        $timeperiod = "pm";
                        if ( $i >= 12 && $i < 13 ) {
                            $hour = floor( $i );
                        } else {
                            $hour = floor( $i ) - 12;
                        }
                    }
                }

                if ( fmod( $i, 1 ) == 0.25 ) {
                    $minutes = "15";
                } elseif ( fmod( $i, 1 ) == 0.50 ) {
                    $minutes = "30";
                } elseif ( fmod( $i, 1 ) == 0.75 ) {
                    $minutes = "45";
                } else {
                    $minutes = "00";
                }


                if ( $i == $selected ) {
                    $selectedstring = "selected='selected'";
                } else {
                    $selectedstring = "";
                }

                if ( $options['timeformat'] == '24hours' ) {
                    $opt .= "<option value='" . $i . "'" . $selectedstring . ">" . $hour . "h" . $minutes . "\n";
                } else if ( $options['timeformat'] == '24hourscolon' ) {
                    $opt .= "<option value='" . $i . "'" . $selectedstring . ">" . $hour . ":" . $minutes . "\n";
                } else if ( $options['timeformat'] == '12hours' ) {
                    $opt .= "<option value='" . $i . "'" . $selectedstring . ">" . $hour . ":" . $minutes . $timeperiod . "\n";
                }
            }
            return $opt;
        }
        function get_days($schedule=0){
            return $wpdb->get_results( "SELECT * from " . ws_db_prefix() . "wsdays WHERE scheduleid = " . $schedule . " ORDER by id" );
        }
        function display_starttime($item, $options){
            $displaytime = "";
            if ( $options['timeformat'] == '24hours' || $options['timeformat'] == '24hourscolon' ) {
                $hour = floor( $item->starttime );
            } elseif ( $options['timeformat'] == '12hours' ) {
                if ( $item->starttime < 12 ) {
                    $timeperiod = "am";
                    if ( $item->starttime == 0 ) {
                        $hour = 12;
                    } else {
                        $hour = floor( $item->starttime );
                    }
                } else {
                    $timeperiod = "pm";
                    if ( $item->starttime == 12 ) {
                        $hour = $item->starttime;
                    } else {
                        $hour = floor( $item->starttime ) - 12;
                    }
                }
            }

            if ( fmod( $item->starttime, 1 ) == 0.25 ) {
                $minutes = "15";
            } elseif ( fmod( $item->starttime, 1 ) == 0.50 ) {
                $minutes = "30";
            } elseif ( fmod( $item->starttime, 1 ) == 0.75 ) {
                $minutes = "45";
            } else {
                $minutes = "00";
            }

            if ( $options['timeformat'] == '24hours' ) {
                $displaytime = $hour . "h" . $minutes . "\n";
            } elseif ( $options['timeformat'] == '24hourscolon' ) {
                $displaytime = $hour . ":" . $minutes . "\n";
            } elseif ( $options['timeformat'] == '12hours' ) {
                $displaytime = $hour . ":" . $minutes . $timeperiod . "\n";
            }
            return $displaytime;
        }
        function display_endtime($item, $options){
            $displaytime = "";
            if ( $options['timeformat'] == '24hours' || $options['timeformat'] == '24hourscolon' ) {
                $hour = floor( $item->endtime );
            } elseif ( $options['timeformat'] == '12hours' ) {
                if ( $item->endtime < 12 ) {
                    $timeperiod = "am";
                    if ( $item->endtime == 0 ) {
                        $hour = 12;
                    } else {
                        $hour = floor( $item->endtime );
                    }
                } else {
                    $timeperiod = "pm";
                    if ( $item->endtime == 12 ) {
                        $hour = $item->endtime;
                    } else {
                        $hour = floor( $item->endtime ) - 12;
                    }
                }
            }

            if ( fmod( $item->endtime, 1 ) == 0.25 ) {
                $minutes = "15";
            } elseif ( fmod( $item->endtime, 1 ) == 0.50 ) {
                $minutes = "30";
            } elseif ( fmod( $item->endtime, 1 ) == 0.75 ) {
                $minutes = "45";
            } else {
                $minutes = "00";
            }

            if ( $options['timeformat'] == '24hours' ) {
                $displaytime = $hour . "h" . $minutes . "\n";
            } elseif ( $options['timeformat'] == '24hourscolon' ) {
                $displaytime = $hour . ":" . $minutes . "\n";
            } elseif ( $options['timeformat'] == '12hours' ) {
                $displaytime = $hour . ":" . $minutes . $timeperiod . "\n";
            }
            return $displaytime;
        }
		function config_page() {
			global $dlextensions;
			global $wpdb;

			$adminpage = '';
			$mode = '';

			$genoptions = get_option( 'WeeklyScheduleGeneral' );

			if ( empty( $genoptions ) ) {
				$genoptions = ws_reset_gen_options( 'set_and_return' );
			}

			if ( isset( $_GET['schedule'] ) ) {
				$schedule = $_GET['schedule'];
			} elseif ( isset( $_POST['schedule'] ) ) {
				$schedule = $_POST['schedule'];
			} else {
				$schedule = 0;
			}

			if ( isset( $_GET['copy'] ) ) {
				$destination = $_GET['copy'];
				$source      = $_GET['source'];

				$sourcesettingsname = 'WS_PP' . $source;
				$sourceoptions      = get_option( $sourcesettingsname );

				$destinationsettingsname = 'WS_PP' . $destination;
				update_option( $destinationsettingsname, $sourceoptions );

				$schedule = $destination;
			}

			if ( isset( $_GET['reset'] ) && $_GET['reset'] == "true" ) {

				$options['starttime']             = 19;
				$options['endtime']               = 22;
				$options['timedivision']          = 0.5;
				$options['tooltipwidth']          = 300;
				$options['tooltiptarget']         = 'right center';
				$options['tooltippoint']          = 'left center';
				$options['tooltipcolorscheme']    = 'ui-tooltip';
				$options['displaydescription']    = "tooltip";
				$options['daylist']               = "";
				$options['timeformat']            = "24hours";
				$options['layout']                = 'horizontal';
				$options['adjusttooltipposition'] = true;
				$options['schedulename']          = "Default";
				$options['linktarget']            = "newwindow";

				$schedule     = $_GET['reset'];
				$schedulename = 'WS_PP' . $schedule;

				update_option( $schedulename, $options );
			}
			if ( isset( $_GET['settings'] ) ) {
				if ( $_GET['settings'] == 'categories' ) {
					$adminpage = 'categories';
				} elseif ( $_GET['settings'] == 'items' ) {
					$adminpage = 'items';
				} elseif ( $_GET['settings'] == 'general' ) {
					$adminpage = 'general';
				} elseif ( $_GET['settings'] == 'days' ) {
					$adminpage = 'days';
				}

			}
			if ( isset( $_POST['submit'] ) ) {
				global $accesslevelcheck;
				if ( !current_user_can( $accesslevelcheck ) ) {
					die( __( 'You cannot edit the Weekly Schedule for WordPress options.', 'ws' ) );
				}
				check_admin_referer( 'wspp-config' );

				if ( isset( $_GET['schedule'] ) ) {
					$schedulename = 'WS_PP' . $_GET['schedule'];
				} else {
					$schedulename = 'WS_PP1';
				}

				$options = get_option( $schedulename );

				if ( $_POST['timedivision'] != $options['timedivision'] && $_POST['timedivision'] == "3.0" ) {
					$itemsquarterhour = $wpdb->get_results( "SELECT * from " . ws_db_prefix() . "wsitems WHERE MOD(duration, 1) = 0.25 and scheduleid = " . $schedule );
					$itemshalfhour    = $wpdb->get_results( "SELECT * from " . ws_db_prefix() . "wsitems WHERE MOD(duration, 1) = 0.5 and scheduleid = " . $schedule );
					$itemshour        = $wpdb->get_results( "SELECT * from " . ws_db_prefix() . "wsitems WHERE MOD(duration, 1) = 1.0 and scheduleid = " . $schedule );
					$itemstwohour     = $wpdb->get_results( "SELECT * from " . ws_db_prefix() . "wsitems WHERE MOD(duration, 1) = 2.0 and scheduleid = " . $schedule );

					if ( $itemsquarterhour ) {
						echo '<div id="warning" class="updated fade"><p><strong>Cannot change time division to tri-hourly since some items have quarter-hourly durations</strong></div>';
						$options['timedivision'] = "0.25";
					} elseif ( $itemshalfhour ) {
						echo '<div id="warning" class="updated fade"><p><strong>Cannot change time division to tri-hourly since some items have half-hourly durations</strong></div>';
						$options['timedivision'] = "0.5";
					} elseif ( $itemshour ) {
						echo '<div id="warning" class="updated fade"><p><strong>Cannot change time division to tri-hourly since some items have hourly durations</strong></div>';
						$options['timedivision'] = "1.0";
					} elseif ( $itemstwohour ) {
						echo '<div id="warning" class="updated fade"><p><strong>Cannot change time division to tri-hourly since some items have hourly durations</strong></div>';
						$options['timedivision'] = "2.0";
					} else {
						$options['timedivision'] = $_POST['timedivision'];
					}
				} elseif ( $_POST['timedivision'] != $options['timedivision'] && $_POST['timedivision'] == "2.0" ) {
					$itemsquarterhour = $wpdb->get_results( "SELECT * from " . ws_db_prefix() . "wsitems WHERE MOD(duration, 1) = 0.25 and scheduleid = " . $schedule );
					$itemshalfhour    = $wpdb->get_results( "SELECT * from " . ws_db_prefix() . "wsitems WHERE MOD(duration, 1) = 0.5 and scheduleid = " . $schedule );
					$itemshour        = $wpdb->get_results( "SELECT * from " . ws_db_prefix() . "wsitems WHERE MOD(duration, 1) = 1.0 and scheduleid = " . $schedule );

					if ( $itemsquarterhour ) {
						echo '<div id="warning" class="updated fade"><p><strong>Cannot change time division to bi-hourly since some items have quarter-hourly durations</strong></div>';
						$options['timedivision'] = "0.25";
					} elseif ( $itemshalfhour ) {
						echo '<div id="warning" class="updated fade"><p><strong>Cannot change time division to bi-hourly since some items have half-hourly durations</strong></div>';
						$options['timedivision'] = "0.5";
					} elseif ( $itemshour ) {
						echo '<div id="warning" class="updated fade"><p><strong>Cannot change time division to bi-hourly since some items have hourly durations</strong></div>';
						$options['timedivision'] = "1.0";
					} else {
						$options['timedivision'] = $_POST['timedivision'];
					}
				} elseif ( $_POST['timedivision'] != $options['timedivision'] && $_POST['timedivision'] == "1.0" ) {
					$itemsquarterhour = $wpdb->get_results( "SELECT * from " . ws_db_prefix() . "wsitems WHERE MOD(duration, 1) = 0.25 and scheduleid = " . $schedule );
					$itemshalfhour    = $wpdb->get_results( "SELECT * from " . ws_db_prefix() . "wsitems WHERE MOD(duration, 1) = 0.5 and scheduleid = " . $schedule );

					if ( $itemsquarterhour ) {
						echo '<div id="warning" class="updated fade"><p><strong>Cannot change time division to hourly since some items have quarter-hourly durations</strong></div>';
						$options['timedivision'] = "0.25";
					} elseif ( $itemshalfhour ) {
						echo '<div id="warning" class="updated fade"><p><strong>Cannot change time division to hourly since some items have half-hourly durations</strong></div>';
						$options['timedivision'] = "0.5";
					} else {
						$options['timedivision'] = $_POST['timedivision'];
					}
				} elseif ( $_POST['timedivision'] != $options['timedivision'] && $_POST['timedivision'] == "0.5" ) {
					$itemsquarterhour = $wpdb->get_results( "SELECT * from " . ws_db_prefix() . "wsitems WHERE MOD(duration, 1) = 0.25 and scheduleid = " . $schedule );

					if ( $itemsquarterhour ) {
						echo '<div id="warning" class="updated fade"><p><strong>Cannot change time division to hourly since some items have quarter-hourly durations</strong></div>';
						$options['timedivision'] = "0.25";
					} else {
						$options['timedivision'] = $_POST['timedivision'];
					}
				} else {
					$options['timedivision'] = $_POST['timedivision'];
				}

				foreach (
					array(
						'starttime', 'endtime', 'tooltipwidth', 'tooltiptarget', 'tooltippoint', 'tooltipcolorscheme',
						'displaydescription', 'daylist', 'timeformat', 'layout', 'schedulename', 'linktarget'
					) as $option_name
				) {
					if ( isset( $_POST[$option_name] ) ) {
						$options[$option_name] = $_POST[$option_name];
					}
				}

				foreach ( array( 'adjusttooltipposition' ) as $option_name ) {
					if ( isset( $_POST[$option_name] ) ) {
						$options[$option_name] = true;
					} else {
						$options[$option_name] = false;
					}
				}


				$schedulename = 'WS_PP' . $schedule;
				update_option( $schedulename, $options );

				echo '<div id="message" class="updated fade"><p><strong>Weekly Schedule: Schedule ' . $schedule . ' Updated</strong></div>';
			}
			if ( isset( $_POST['submitgen'] ) ) {
				global $accesslevelcheck;
				if ( !current_user_can( $accesslevelcheck ) ) {
					die( __( 'You cannot edit the Weekly Schedule for WordPress options.' ) );
				}
				check_admin_referer( 'wspp-config' );

				foreach ( array( 'stylesheet', 'numberschedules', 'includestylescript', 'accesslevel', 'csvdelimiter' ) as $option_name ) {
					if ( isset( $_POST[$option_name] ) ) {
						$genoptions[$option_name] = $_POST[$option_name];
					}
				}

				foreach ( array( 'debugmode', 'frontpagestylescript' ) as $option_name ) {
					if ( isset( $_POST[$option_name] ) ) {
						$genoptions[$option_name] = true;
					} else {
						$genoptions[$option_name] = false;
					}
				}

				update_option( 'WeeklyScheduleGeneral', $genoptions );
			} elseif ( isset( $_POST['importschedule'] ) ) {
				wp_suspend_cache_addition( true );
				set_time_limit( 600 );

				global $wpdb;

				$handle = fopen( $_FILES['schedulefile']['tmp_name'], 'r' );

				$importmessage = '';
				$filerow = 0;
				$successfulimport = 0;

				if ( !isset( $genoptions['csvdelimiter'] ) ) {
					$genoptions['csvdelimiter'] = ',';
				}

				if ( $handle ) {
					while ( ( $data = fgetcsv( $handle, 5000, $genoptions['csvdelimiter'] ) ) !== false ) {
						$filerow += 1;

						if ( $filerow >= 2 ) {
							$start_time = $data[3];
							$colon_position = strpos( $start_time, ':' );

							if ( false !== $colon_position ) {
								$calc_start_time = substr( $start_time, 0, $colon_position );
								$calc_start_minute = substr( $start_time, $colon_position + 1, 2 );
								$calc_start_minute = ( round ( $calc_start_minute / 15 ) / 4 );
								if ( $calc_start_minute >= 1 )
									$calc_start_minute = 0;

								$start_time = $calc_start_time + $calc_start_minute;
							} else {
								$start_time = floatval( $start_time );
							}

						    if ( count( $data ) > 0 && count( $data ) == 10 ) {
								$newitem = array(
									'name'            => wp_kses_post( stripslashes( $data[5] ) ),
									'description'     => wp_kses_post( stripslashes( $data[6] ) ),
									'address'         => esc_url( stripslashes( $data[7] ) ),
									'starttime'       => $start_time,
									'duration'        => floatval( $data[4] ),
									'row'             => '',
									'day'             => intval( $data[2] ),
									'category'        => intval( $data[1] ),
									'scheduleid'      => intval( $data[0] ),
									'backgroundcolor' => esc_html( stripslashes( $data[8] ) ),
									'titlecolor'      => esc_html( stripslashes( $data[9] ) )
								);

							    $rowsearch = 1;
							    $row       = 1;

							    while ( $rowsearch == 1 ) {
								    $endtime = $newitem['starttime'] + $newitem['duration'];

								    $conflictquery = "SELECT * from " . ws_db_prefix() . "wsitems where day = " . $newitem['day'];
								    $conflictquery .= " and row = " . $row;
								    $conflictquery .= " and scheduleid = " . $newitem['scheduleid'];
								    $conflictquery .= " and ((" . $newitem['starttime'] . " < starttime and " . $endtime . " > starttime) or";
								    $conflictquery .= "      (" . $newitem['starttime'] . " >= starttime and " . $newitem['starttime'] . " < starttime + duration)) ";

								    $conflictingitems = $wpdb->get_results( $conflictquery );

								    if ( $conflictingitems ) {
									    $row ++;
								    } else {
									    $rowsearch = 0;
								    }
							    }

							    $dayrow = $wpdb->get_row( "SELECT * from " . ws_db_prefix() . "wsdays where id = " . $newitem['day'] . " AND scheduleid = " . $newitem['scheduleid'] );
							    if ( $dayrow->rows < $row ) {
								    $dayid     = array( 'id' => $newitem['day'], 'scheduleid' => $newitem['scheduleid'] );
								    $newdayrow = array( 'rows' => $row );

								    $wpdb->update( ws_db_prefix() . 'wsdays', $newdayrow, $dayid );
							    }

							    $newitem['row'] = $row;

							    $wpdb->insert( ws_db_prefix() . 'wsitems', $newitem );
								$successfulimport++;
							} elseif ( count( $data ) > 0 && count( $data ) != 10 ) {
								$importmessage = 1;
							}
						}
					}
				}

				if ( $successfulimport > 0 ) {
					echo '<div id="message" class="updated fade"><p><strong>Successfully imported ' . $successfulimport . ' record(s) from ' . ( $filerow - 1 ) . ' line(s) in import file</strong></div>';
				}

				if ( $importmessage == 1 ) {
					echo '<div id="message" class="updated fade"><p><strong>Some records did not have the right number of fields</strong></div>';
				}

				wp_suspend_cache_addition( false );
			}

			if ( isset( $_GET['editcat'] ) ) {
				$adminpage = 'categories';

				$mode = 'edit';

				$selectedcat = $wpdb->get_row( "select * from " . ws_db_prefix() . "wscategories where id = " . intval( $_GET['editcat'] ) );
			}
			if ( isset( $_POST['newcat'] ) || isset( $_POST['updatecat'] ) ) {
				global $accesslevelcheck;
				if ( !current_user_can( $accesslevelcheck ) ) {
					die( __( 'You cannot edit the Weekly Schedule for WordPress options.' ) );
				}
				check_admin_referer( 'wspp-config' );

				if ( isset( $_POST['name'] ) ) {
					$newcat = array(
						"name"            => $_POST['name'],
						"scheduleid"      => $_POST['schedule'],
						'backgroundcolor' => $_POST['backgroundcolor']
					);
				} else {
					$newcat = "";
				}

				if ( isset( $_POST['id'] ) ) {
					$id = array( "id" => $_POST['id'] );
				}


				if ( isset( $_POST['newcat'] ) ) {
					$wpdb->insert( ws_db_prefix() . 'wscategories', $newcat );
					echo '<div id="message" class="updated fade"><p><strong>Inserted New Category</strong></div>';
				} elseif ( isset( $_POST['updatecat'] ) ) {
					$wpdb->update( ws_db_prefix() . 'wscategories', $newcat, $id );
					echo '<div id="message" class="updated fade"><p><strong>Category Updated</strong></div>';
				}

				$mode = '';

				$adminpage = 'categories';
			}
			if ( isset( $_GET['deletecat'] ) ) {
				$adminpage = 'categories';

				$catexist = $wpdb->get_row( "SELECT * from " . ws_db_prefix() . "wscategories WHERE id = " . intval( $_GET['deletecat'] ) );

				if ( $catexist ) {
					$wpdb->query( "DELETE from " . ws_db_prefix() . "wscategories WHERE id = " . intval( $_GET['deletecat'] ) );
					echo '<div id="message" class="updated fade"><p><strong>Category Deleted</strong></div>';
				}
			}
			if ( isset( $_GET['edititem'] ) ) {
				$adminpage = 'items';

				$mode = 'edit';

				$selecteditem = $wpdb->get_row( "select * from " . ws_db_prefix() . "wsitems where id = " . intval( $_GET['edititem'] ) . " AND scheduleid = " . intval( $_GET['schedule'] ) );
			}
			if ( isset( $_POST['newitem'] ) || isset( $_POST['updateitem'] ) ) {
				// Need to re-work all of this to support multiple schedules
				global $accesslevelcheck;
				if ( !current_user_can( $accesslevelcheck ) ) {
					die( __( 'You cannot edit the Weekly Schedule for WordPress options.' ) );
				}
				check_admin_referer( 'wspp-config' );

				if ( isset( $_POST['name'] ) && isset( $_POST['starttime'] ) && isset( $_POST['duration'] ) ) {
					$newitem = array(
						'name'            => wp_kses_post( stripslashes( $_POST['name'] ) ),
						'description'     => wp_kses_post( stripslashes( $_POST['description'] ) ),
						'address'         => esc_url( $_POST['address'] ),
						'starttime'       => floatval( $_POST['starttime'] ),
						'category'        => intval( $_POST['category'] ),
						'duration'        => $_POST['duration'],
						'day'             => $_POST['day'],
						'scheduleid'      => $_POST['schedule'],
						'backgroundcolor' => $_POST['backgroundcolor'],
						'titlecolor'      => $_POST['titlecolor']
					);

					if ( isset( $_POST['updateitem'] ) ) {
						$origrow = $_POST['oldrow'];
						$origday = $_POST['oldday'];
					}

					$rowsearch = 1;
					$row       = 1;

					while ( $rowsearch == 1 ) {
						if ( $_POST['id'] != "" ) {
							$checkid = " and id <> " . $_POST['id'];
						} else {
							$checkid = "";
						}

						$endtime = $newitem['starttime'] + $newitem['duration'];

						$conflictquery = "SELECT * from " . ws_db_prefix() . "wsitems where day = " . intval( $newitem['day'] ) . $checkid;
						$conflictquery .= " and row = " . intval( $row );
						$conflictquery .= " and scheduleid = " . intval( $newitem['scheduleid'] );
						$conflictquery .= " and ((" . $newitem['starttime'] . " < starttime and " . $endtime . " > starttime) or";
						$conflictquery .= "      (" . $newitem['starttime'] . " >= starttime and " . $newitem['starttime'] . " < starttime + duration)) ";

						$conflictingitems = $wpdb->get_results( $conflictquery );

						if ( $conflictingitems ) {
							$row ++;
						} else {
							$rowsearch = 0;
						}
					}

					if ( isset( $_POST['updateitem'] ) ) {
						if ( $origrow != $row || $origday != $_POST['day'] ) {
							if ( $origrow > 1 ) {
								$itemday = $wpdb->get_row( "SELECT * from " . ws_db_prefix() . "wsdays WHERE id = " . intval( $origday ) . " AND scheduleid = " . intval( $_POST['schedule'] ) );

								$othersonrow = $wpdb->get_results( "SELECT * from " . ws_db_prefix() . "wsitems WHERE day = " . intval( $origday ) . " AND row = " . intval( $origrow ) . " AND scheduleid = " . intval( $_POST['schedule'] ) . " AND id != " . intval( $_POST['id'] ) );
								if ( !$othersonrow ) {
									if ( $origrow != $itemday->rows ) {
										for ( $i = $origrow + 1; $i <= $itemday->rows; $i ++ ) {
											$newrow    = $i - 1;
											$changerow = array( 'row' => $newrow );
											$oldrow    = array( 'row' => $i, 'day' => $origday );
											$wpdb->update( ws_db_prefix() . 'wsitems', $changerow, $oldrow );
										}
									}

									$dayid     = array( 'id' => $itemday->id, 'scheduleid' => intval( $_POST['schedule'] ) );
									$newrow    = $itemday->rows - 1;
									$newdayrow = array( 'rows' => $newrow );

									$wpdb->update( ws_db_prefix() . 'wsdays', $newdayrow, $dayid );
								}
							}
						}
					}

					$dayrow = $wpdb->get_row( "SELECT * from " . ws_db_prefix() . "wsdays where id = " . intval( $_POST['day'] ) . " AND scheduleid = " . intval( $_POST['schedule'] ) );
					if ( $dayrow->rows < $row ) {
						$dayid     = array( 'id' => intval( $_POST['day'] ), 'scheduleid' => intval( $_POST['schedule'] ) );
						$newdayrow = array( 'rows' => $row );

						$wpdb->update( ws_db_prefix() . 'wsdays', $newdayrow, $dayid );
					}

					$newitem['row'] = $row;

					if ( isset( $_POST['id'] ) ) {
						$id = array( 'id' => intval( $_POST['id'] ), 'scheduleid' => intval( $_POST['schedule'] ) );
					}

					if ( isset( $_POST['newitem'] ) ) {
						$wpdb->insert( ws_db_prefix() . 'wsitems', $newitem );
						echo '<div id="message" class="updated fade"><p><strong>'.__('Inserted New Item','ws').'</strong></div>';
					} elseif ( isset( $_POST['updateitem'] ) ) {
						$wpdb->update( ws_db_prefix() . 'wsitems', $newitem, $id );
						echo '<div id="message" class="updated fade"><p><strong>'.__('Item Updated','ws').'</strong></div>';
					}
				}

				$mode = '';

				$adminpage = 'items';
			}
			if ( isset( $_GET['deleteitem'] ) ) {
				$adminpage = 'items';

				$itemexist = $wpdb->get_row( "SELECT * from " . ws_db_prefix() . "wsitems WHERE id = " . intval( $_GET['deleteitem'] ) . " AND scheduleid = " . intval( $_GET['schedule'] ) );
				$itemday   = $wpdb->get_row( "SELECT * from " . ws_db_prefix() . "wsdays WHERE id = " . $itemexist->day . " AND scheduleid = " . intval( $_GET['schedule'] ) );

				if ( $itemexist ) {
					$wpdb->query( "DELETE from " . ws_db_prefix() . "wsitems WHERE id = " . intval( $_GET['deleteitem'] ) . " AND scheduleid = " . intval( $_GET['schedule'] ) );

					if ( $itemday->rows > 1 ) {
						$othersonrow = $wpdb->get_results( "SELECT * from " . ws_db_prefix() . "wsitems WHERE day = " . $itemexist->day . " AND scheduleid = " . intval( $_GET['schedule'] ) . " AND row = " . $itemexist->row );
						if ( !$othersonrow ) {
							if ( $itemexist->row != $itemday->rows ) {
								for ( $i = $itemexist->row + 1; $i <= $itemday->rows; $i ++ ) {
									$newrow    = $i - 1;
									$changerow = array( 'row' => $newrow );
									$oldrow    = array( 'row' => $i, 'day' => $itemday->id );
									$wpdb->update( ws_db_prefix() . 'wsitems', $changerow, $oldrow );
								}
							}

							$dayid     = array( 'id' => $itemexist->day, 'scheduleid' => intval( $_GET['schedule'] ) );
							$newrow    = $itemday->rows - 1;
							$newdayrow = array( 'rows' => $newrow );

							$wpdb->update( ws_db_prefix() . 'wsdays', $newdayrow, $dayid );
						}
					}
					echo '<div id="message" class="updated fade"><p><strong>'.__('Item Deleted','ws').'</strong></div>';
				}
			}
			if ( isset( $_POST['updatedays'] ) ) {
				$dayids = array( 1, 2, 3, 4, 5, 6, 7 );

				foreach ( $dayids as $dayid ) {
					$daynamearray = array( "name" => $_POST[$dayid] );
					$dayidarray   = array( "id" => $dayid, "scheduleid" => intval( $_POST['schedule'] ) );

					$wpdb->update( ws_db_prefix() . 'wsdays', $daynamearray, $dayidarray );
				}
			}
			if ( isset( $_POST['deleteallitems'] ) && isset( $_GET['schedule'] ) ) {
				$deletion_query = 'delete from ' . ws_db_prefix() . 'wsitems where scheduleid = ' . intval( $_GET['schedule'] );
				$wpdb->get_results( $deletion_query );

				$days_row_query = 'update ' . ws_db_prefix() . 'wsdays set rows = 1 where scheduleid = ' . intval( $_GET['schedule'] );
				$wpdb->get_results( $days_row_query);
			}

			$wspluginpath = WP_CONTENT_URL . '/plugins/' . plugin_basename( dirname( __FILE__ ) ) . '/';

			if ( $schedule == '' ) {
				$options = get_option( 'WS_PP1' );
				if ( $options == false ) {
					$oldoptions = get_option( 'WS_PP' );
					if ( $options ) {
						echo __("If you are upgrading from versions before 2.0, please deactivate and reactivate the plugin in the Wordpress Plugins admin to upgrade all tables correctly.",'ws');
					}
				}

				$schedule = 0;
			} else {
				$settingsname = 'WS_PP' . $schedule;
				$options      = get_option( $settingsname );
			}

			if ( $options == "" ) {
				$options['starttime']             = 19;
				$options['endtime']               = 22;
				$options['timedivision']          = 0.5;
				$options['tooltipwidth']          = 300;
				$options['tooltiptarget']         = 'right center';
				$options['tooltippoint']          = 'left center';
				$options['tooltipcolorscheme']    = 'ui-tooltip';
				$options['displaydescription']    = "tooltip";
				$options['daylist']               = "";
				$options['timeformat']            = "24hours";
				$options['layout']                = 'horizontal';
				$options['adjusttooltipposition'] = true;
				$options['schedulename']          = "Default";
				$options['linktarget']            = "newwindow";

				$schedulename = 'WS_PP' . $schedule;

				update_option( $schedulename, $options );

				$catsresult = $wpdb->query( "SELECT * from " . ws_db_prefix() . "wscategories where scheduleid = " . $schedule );

				if ( !$catsresult ) {
					$sqlstatement = "INSERT INTO " . ws_db_prefix() . "wscategories (`name`, `scheduleid`) VALUES
									('Default', " . $schedule . ")";
					$result       = $wpdb->query( $sqlstatement );
				}

				$wpdb->wsdays = ws_db_prefix() . 'wsdays';

				$daysresult = $wpdb->query( "SELECT * from " . ws_db_prefix() . "wsdays where scheduleid = " . $schedule );

				if ( !$daysresult ) {
					$sqlstatement = "INSERT INTO " . ws_db_prefix() . "wsdays (`id`, `name`, `rows`, `scheduleid`) VALUES
									(1, 'Sun', 1, " . $schedule . "),
									(2, 'Mon', 1, " . $schedule . "),
									(3, 'Tue', 1, " . $schedule . "),
									(4, 'Wes', 1, " . $schedule . "),
									(5, 'Thu', 1, " . $schedule . "),
									(6, 'Fri', 1, " . $schedule . "),
									(7, 'Sat', 1, " . $schedule . ")";
					$result       = $wpdb->query( $sqlstatement );
				}
			}
          /**include(plugin_dir_path( __FILE__ ).'views/admin.php');/*/
             include(plugin_dir_path( __FILE__ ).'views/new-admin.php');
             include(plugin_dir_path( __FILE__ ).'views/modal.php');//*/
		} // end config_page()

	} // end class WS_Admin
	$my_ws_admin = new WS_Admin;
}

/**
 * LIBRAIRIES FUNC
 */
function ws_library_func( $atts ) {
	$schedule = 1;
	extract(
		shortcode_atts(
			array(
				'schedule' => '',
				'cats' => ''
			), $atts
		)
	);

	if ( $schedule == '' ) {
		$options  = get_option( 'WS_PP1' );
		$schedule = 1;
	} else {
		$schedulename = 'WS_PP' . $schedule;
		$options      = get_option( $schedulename );
	}

	if ( $options == false ) {
		return "Requested schedule (Schedule " . $schedule . ") is not available from Weekly Schedule<br />";
	}

	return ws_library(
		$schedule, $options['starttime'], $options['endtime'], $options['timedivision'], $options['layout'], $options['tooltipwidth'], $options['tooltiptarget'],
		$options['tooltippoint'], $options['tooltipcolorscheme'], $options['displaydescription'], $options['daylist'], $options['timeformat'],
		$options['adjusttooltipposition'], $options['linktarget'], $cats
	);
}
/**
 * LIBRAIRIES FLAT FUNC
 */
function ws_library_flat_func( $atts ) {
	$schedule = 1;

	extract(
		shortcode_atts(
			array(
				'schedule' => '',
				'cats' => ''
			), $atts
		)
	);

	if ( $schedule == '' ) {
		$options  = get_option( 'WS_PP1' );
		$schedule = 1;
	} else {
		$schedulename = 'WS_PP' . $schedule;
		$options      = get_option( $schedulename );
	}

	if ( $options == false ) {
		return "Requested schedule (Schedule " . $schedule . ") is not available from Weekly Schedule<br />";
	}

	return ws_library_flat(
		$schedule, $options['starttime'], $options['endtime'], $options['timedivision'], $options['layout'], $options['tooltipwidth'], $options['tooltiptarget'],
		$options['tooltippoint'], $options['tooltipcolorscheme'], $options['displaydescription'], $options['daylist'], $options['timeformat'],
		$options['adjusttooltipposition'], $cats
	);
}
/**
 * LIBRAIRIES
 */
function ws_library(
	$scheduleid = 1, $starttime = 19, $endtime = 22, $timedivision = 0.5, $layout = 'horizontal', $tooltipwidth = 300, $tooltiptarget = 'right center',
	$tooltippoint = 'leftMiddle', $tooltipcolorscheme = 'ui-tooltip', $displaydescription = 'tooltip', $daylist = '', $timeformat = '24hours',
	$adjusttooltipposition = true, $linktarget = 'newwindow', $cats = ''
) {
	global $wpdb;

	$today = date( 'w', current_time( 'timestamp', 0 ) ) + 1;
	$system_hour = date( 'H', current_time( 'timestamp', 0 ) );
	$system_minute = date( 'i', current_time( 'timestamp', 0 ) ) / 60;
	$time_now = $system_hour + $system_minute;

	$numberofcols = ( $endtime - $starttime ) / $timedivision;

	$output = "<!-- Weekly Schedule Output -->\n";

	$output .= "<div class='ws-schedule' id='ws-schedule" . $scheduleid . "'>\n";

	if ( $layout == 'horizontal' || $layout == '' ) {
		$output .= "<table>\n";
	} elseif ( $layout == 'vertical' ) {
		$output .= "<div class='verticalcolumn'>\n";
		$output .= "<table class='verticalheader'>\n";
	}

	$output .= "<tr class='topheader'>";

	$output .= "<th class='rowheader'></th>";

	if ( $layout == 'vertical' ) {
		$output .= "</tr>\n";
	}

	for ( $i = $starttime; $i < $endtime; $i += $timedivision ) {

		if ( fmod( $i, 1 ) == 0.25 ) {
			$minutes = "15";
		} elseif ( fmod( $i, 1 ) == 0.50 ) {
			$minutes = "30";
		} elseif ( fmod( $i, 1 ) == 0.75 ) {
			$minutes = "45";
		} else {
			$minutes = "";
		}


		if ( $timeformat == "24hours" || $timeformat == "" ) {
			if ( $layout == 'vertical' ) {
				$output .= "<tr class='datarow'>";
			}

			$output .= "<th>" . floor( $i ) . "h" . $minutes . "</th>";

			if ( $layout == 'vertical' ) {
				$output .= "</tr>\n";
			}

		} else if ( $timeformat == "24hourscolon" ) {
			if ( $layout == 'vertical' ) {
				$output .= "<tr class='datarow'>";
			}

			$output .= "<th>" . floor( $i ) . ":" . ( empty( $minutes ) ? "00" : $minutes ) . "</th>";

			if ( $layout == 'vertical' ) {
				$output .= "</tr>\n";
			}

		} else if ( $timeformat == "12hours" ) {
			if ( $i < 12 ) {
				$timeperiod = "am";
				if ( $i == 0 ) {
					$hour = 12;
				} else {
					$hour = floor( $i );
				}
			} else {
				$timeperiod = "pm";
				if ( $i >= 12 && $i < 13 ) {
					$hour = floor( $i );
				} else {
					$hour = floor( $i ) - 12;
				}
			}

			if ( $layout == 'vertical' ) {
				$output .= "<tr class='datarow'>";
			}

			$output .= "<th>" . $hour;
			if ( $minutes != "" ) {
				$output .= ":" . $minutes;
			}
			$output .= $timeperiod . "</th>";

			if ( $layout == 'vertical' ) {
				$output .= "</tr>\n";
			}
		}
	}

	if ( $layout == 'horizontal' || $layout == '' ) {
		$output .= "</tr>\n";
	} elseif ( $layout == 'vertical' ) {
		$output .= "</table>\n";
		$output .= "</div>\n";
	}


	$sqldays = "SELECT * from " . ws_db_prefix() . "wsdays where scheduleid = %d";

	if ( !empty( $daylist ) ) {
		$sqldays .= " AND id in ( %s ) ORDER BY FIELD(id, %s)";
		$sqldaysquery = $wpdb->prepare( $sqldays, $scheduleid, $daylist, $daylist );
		$sqldaysquery = str_replace( '\'', '', $sqldaysquery );
		$daysoftheweek = $wpdb->get_results( $sqldaysquery );
	} else {
		$daysoftheweek = $wpdb->get_results( $wpdb->prepare( $sqldays, $scheduleid ) );
	}

	foreach ( $daysoftheweek as $day ) {
		for ( $daysrow = 1; $daysrow <= $day->rows; $daysrow ++ ) {
			$columns = $numberofcols;
			$time    = $starttime;
			$firstrowofday = 0;

			if ( $layout == 'vertical' ) {
				$output .= "<div class='verticalcolumn" . $day->rows . "'>\n";
				$output .= "<table class='vertical" . $day->rows . "'>\n";
				$output .= "<tr class='vertrow" . $day->rows . "'>";
			} elseif ( $layout == 'horizontal' || $layout == '' ) {
				$output .= "<tr class='row" . $day->rows . " ";
				if ( !$firstrowofday ) {
					$output .= "firstrowofday";
					$firstrowofday = 1;
				}
				$output .= "'>\n";
			}

			if ( $daysrow == 1 && ( $layout == 'horizontal' || $layout == '' ) ) {
				$output .= "<th rowspan='" . $day->rows . "' class='rowheader'>" . $day->name . "</th>\n";
			}
			if ( $daysrow == 1 && $layout == 'vertical' && $day->rows == 1 ) {
				$output .= "<th class='rowheader'>" . $day->name . "</th>\n";
			}
			if ( $daysrow == 1 && $layout == 'vertical' && $day->rows > 1 ) {
				$output .= "<th class='rowheader'>&laquo; " . $day->name . "</th>\n";
			} elseif ( $daysrow != 1 && $layout == 'vertical' ) {
				if ( $daysrow == $day->rows ) {
					$output .= "<th class='rowheader'>" . $day->name . " &raquo;</th>\n";
				} else {
					$output .= "<th class='rowheader'>&laquo; " . $day->name . " &raquo;</th>\n";
				}
			}

			if ( $layout == 'vertical' ) {
				$output .= "</tr>\n";
			}

			$sqlitems = "SELECT *, i.name as itemname, c.name as categoryname, c.id as catid, i.backgroundcolor as itemcolor, c.backgroundcolor as categorycolor, i.day as dayid from " . ws_db_prefix() .
				"wsitems i, " . ws_db_prefix() . "wscategories c WHERE day = " . $day->id .
				" AND i.scheduleid = %d AND row = " . $daysrow . " AND i.category = c.id AND i.starttime >= %f AND i.starttime < %f ";

			if ( !empty( $cats ) ) {
				$sqlitems .= ' AND category IN ( %s ) ';
			}

			$sqlitems .= " ORDER by starttime";

			if ( empty( $cats ) ) {
				$items = $wpdb->get_results( $wpdb->prepare( $sqlitems, $scheduleid, $starttime, $endtime ) );
			} else {
				$items = $wpdb->get_results( $wpdb->prepare( $sqlitems, $scheduleid, $starttime, $endtime, $cats ) );
			}

			if ( $items ) {
				foreach ( $items as $item ) {

					for ( $i = $time; $i < $item->starttime; $i += $timedivision ) {
						if ( $layout == 'vertical' ) {
							$output .= "<tr class='datarow'>\n";
						}

						$output .= "<td></td>\n";

						if ( $layout == 'vertical' ) {
							$output .= "</tr>\n";
						}

						$columns -= 1;

					}

					$colspan = $item->duration / $timedivision;

					if ( $colspan > $columns ) {
						$colspan = $columns;
						$columns -= $columns;

						if ( $layout == 'horizontal' ) {
							$continue = "id='continueright' ";
						} elseif ( $layout == 'vertical' ) {
							$continue = "id='continuedown' ";
						}
					} else {
						$columns -= $colspan;
						$continue = "";
					}

					if ( $layout == 'vertical' ) {
						$output .= "<tr class='datarow" . $colspan . "'>";
					}

					$output .= '<td class="';

					if ( $item->starttime < $time_now && $time_now < ( $item->starttime + $item->duration ) && $today == $item->dayid ) {
						$output .= 'now-playing ';
					}

					$output .= 'ws-item-' . $item->id . ' cat' . $item->catid . '" ';


					if ( !empty( $item->itemcolor ) || !empty( $item->categorycolor ) ) {

						$output .= 'style= "' . 'background-color:' . ( !empty( $item->itemcolor ) ? $item->itemcolor : $item->categorycolor ) . ';"';
					}

					if ( $displaydescription == "tooltip" && $item->description != "" ) {
						$output .= "tooltip='" . htmlspecialchars( stripslashes( $item->description ), ENT_QUOTES ) . "' ";
					}

					$output .= $continue;

					if ( $layout == 'horizontal' || $layout == '' ) {
						$output .= "colspan='" . $colspan . "'";
					}

					$output .= '>';
                    // CONTENT
					$output .= '<div class="';

					if ( $item->starttime < $time_now && $time_now < ( $item->starttime + $item->duration ) && $today == $item->dayid ) {
						$output .= 'now-playing ';
					}

					$output .= 'ws-item-title ws-item-title-' . $item->id . '"';

					if ( !empty( $item->titlecolor ) ) {
						$output .= ' style="color:' . $item->titlecolor . '"';
					}

					$output .= ">";

                    $target   = $item->address;
                    $itemname = stripslashes( $item->itemname );
					if ( $item->address != "" ) {
                        $output .= "<a target='$linktarget'href='$target'>$itemname</a>";
					} else
                        $output .= $itemname;

					$output .= "</div>";

					if ( $displaydescription == "cell" ) {
						$output .= "<br />" . stripslashes( $item->description );
					}

					$output .= "</td>";
					$time = $item->starttime + $item->duration;

					if ( $layout == 'vertical' ) {
						$output .= "</tr>\n";
					}

				}

				for ( $x = $columns; $x > 0; $x -- ) {

					if ( $layout == 'vertical' ) {
						$output .= "<tr class='datarow'>";
					}

					$output .= "<td></td>";
					$columns -= 1;

					if ( $layout == 'vertical' ) {
						$output .= "</tr>";
					}
				}
			} else {
				for ( $i = $starttime; $i < $endtime; $i += $timedivision ) {
					if ( $layout == 'vertical' ) {
						$output .= "<tr class='datarow'>";
					}

					$output .= "<td></td>";

					if ( $layout == 'vertical' ) {
						$output .= "</tr>";
					}
				}
			}

			if ( $layout == 'horizontal' || $layout == '' ) {
				$output .= "</tr>";
			}

			if ( $layout == 'vertical' ) {
				$output .= "</table>\n";
				$output .= "</div>\n";
			}
		}
	}

	if ( $layout == 'horizontal' || $layout == '' ) {
		$output .= "</table>";
	}

	$output .= "</div>\n";

	if ( $displaydescription == "tooltip" ) {
		$output .= "<script type=\"text/javascript\">\n";
		$output .= "// Create the tooltips only on document load\n";

		$output .= "jQuery(document).ready(function()\n";
		$output .= "\t{\n";
		$output .= "\t// Notice the use of the each() method to acquire access to each elements attributes\n";
		$output .= "\tjQuery('.ws-schedule td[tooltip]').each(function()\n";
		$output .= "\t\t{\n";
		$output .= "\t\tjQuery(this).qtip({\n";
		$output .= "\t\t\tcontent: jQuery(this).attr('tooltip'), // Use the tooltip attribute of the element for the content\n";
		$output .= "\t\t\tstyle: {\n";
		$output .= "\t\t\t\twidth: " . $tooltipwidth . ",\n";
		$output .= "\t\t\t\tclasses: '" . $tooltipcolorscheme . "' // Give it a crea mstyle to make it stand out\n";
		$output .= "\t\t\t},\n";
		$output .= "\t\t\tposition: {\n";
		if ( $adjusttooltipposition ) {
			$output .= "\t\t\t\tadjust: {method: 'flip flip'},\n";
		}
		$output .= "\t\t\t\tviewport: jQuery(window),\n";
		$output .= "\t\t\t\tat: '" . $tooltiptarget . "',\n";
		$output .= "\t\t\t\tmy: '" . $tooltippoint . "'\n";
		$output .= "\t\t\t}\n";
		$output .= "\t\t});\n";
		$output .= "\t});\n";
		$output .= "});\n";
		$output .= "</script>\n";

	}

	$output .= "<!-- End of Weekly Schedule Output -->\n";

	return $output;
}
/**
 * LIBRAIRIES FLAT
 */
function ws_library_flat(
	$scheduleid = 1, $starttime = 19, $endtime = 22, $timedivision = 0.5, $layout = 'horizontal', $tooltipwidth = 300, $tooltiptarget = 'right center',
	$tooltippoint = 'leftMiddle', $tooltipcolorscheme = 'ui-tooltip', $displaydescription = 'tooltip', $daylist = '', $timeformat = '24hours',
	$adjusttooltipposition = true, $cats = ''
) {
	global $wpdb;

	$today = date( 'w', current_time( 'timestamp', 0 ) ) + 1;
	$system_hour = date( 'H', current_time( 'timestamp', 0 ) );
	$system_minute = date( 'i', current_time( 'timestamp', 0 ) ) / 60;
	$time_now = $system_hour + $system_minute;

	$linktarget = 'newwindow';

	$output = "<!-- Weekly Schedule Flat Output -->\n";

	$output .= "<div class='ws-schedule' id='ws-schedule-$scheduleid'>\n";

	$sqldays = "SELECT * from " . ws_db_prefix() . "wsdays where scheduleid = %d";

	if ( !empty( $daylist ) ) {
		$sqldays .= " AND id in ( %s ) ORDER BY FIELD(id, %s )";
		$sqldaysquery = $wpdb->prepare( $sqldays, $scheduleid, $daylist, $daylist );
		$sqldaysquery = str_replace( '\'', '', $sqldaysquery );
		$daysoftheweek = $wpdb->get_results( $sqldaysquery );
	} else {
		$daysoftheweek = $wpdb->get_results( $wpdb->prepare( $sqldays, $scheduleid ) );
	}

	$output .= "<table>\n";

	foreach ( $daysoftheweek as $day ) {
		for ( $daysrow = 1; $daysrow <= $day->rows; $daysrow ++ ) {
			$output .= "<tr><td colspan='3'>" . $day->name . "</td></tr>\n";

			$sqlitems = "SELECT *, i.name as itemname, c.name as categoryname, c.id as catid, i.day as dayid from " . ws_db_prefix() .
				"wsitems i, " . ws_db_prefix() . "wscategories c WHERE day = " . $day->id .
				" AND i.scheduleid = %d AND row = " . $daysrow . " AND i.category = c.id AND i.starttime >= %f AND i.starttime < %f ";

			if ( !empty( $cats ) ) {
				$sqlitems .= 'AND category IN ( %s )';
			}

			$sqlitems .= "ORDER by starttime";

			if ( empty( $cats ) ) {
				$items = $wpdb->get_results( $wpdb->prepare( $sqlitems, $scheduleid, $starttime, $endtime ) );
			} else {
				$items = $wpdb->get_results( $wpdb->prepare( $sqlitems, $scheduleid, $starttime, $endtime, $cats ) );
			}

			if ( $items ) {
				foreach ( $items as $item ) {

					$output .= "<tr>\n";

					if ( $timeformat == '24hours' || $timeformat == '24hourscolon' ) {
						$hour = floor( $item->starttime );
					} elseif ( $timeformat == '12hours' ) {
						if ( $item->starttime < 12 ) {
							$timeperiod = "am";
							if ( $item->starttime == 0 ) {
								$hour = 12;
							} else {
								$hour = floor( $item->starttime );
							}
						} else {
							$timeperiod = "pm";
							if ( $item->starttime == 12 ) {
								$hour = $item->starttime;
							} else {
								$hour = floor( $item->starttime ) - 12;
							}
						}
					}

					if ( fmod( $item->starttime, 1 ) == 0.25 ) {
						$minutes = "15";
					} elseif ( fmod( $item->starttime, 1 ) == 0.50 ) {
						$minutes = "30";
					} elseif ( fmod( $item->starttime, 1 ) == 0.75 ) {
						$minutes = "45";
					} else {
						$minutes = "00";
					}

					if ( $timeformat == '24hours' ) {
						$output .= "<td>" . $hour . "h" . $minutes . " - ";
					} elseif ( $timeformat == '24hourscolon' ) {
						$output .= "<td>" . $hour . ":" . $minutes . " - ";
					} elseif ( $timeformat == '12hours' ) {
						$output .= "<td>" . $hour . ":" . $minutes . $timeperiod . " - ";
					}

					$endtime = $item->starttime + $item->duration;

					if ( $timeformat == '24hours' || $timeformat == '24hourscolon' ) {
						$hour = floor( $endtime );
					} elseif ( $timeformat == '12hours' ) {
						if ( $endtime < 12 ) {
							$timeperiod = "am";
							if ( $endtime == 0 ) {
								$hour = 12;
							} else {
								$hour = floor( $endtime );
							}
						} else {
							$timeperiod = "pm";
							if ( $endtime == 12 ) {
								$hour = $endtime;
							} else {
								$hour = floor( $endtime ) - 12;
							}
						}
					}

					if ( fmod( $endtime, 1 ) == 0.25 ) {
						$minutes = "15";
					} elseif ( fmod( $endtime, 1 ) == 0.50 ) {
						$minutes = "30";
					} elseif ( fmod( $endtime, 1 ) == 0.75 ) {
						$minutes = "45";
					} else {
						$minutes = "00";
					}

					if ( $timeformat == '24hours' ) {
						$output .= $hour . "h" . $minutes . "</td>";
					} elseif ( $timeformat == '24hourscolon' ) {
						$output .= $hour . ":" . $minutes . "</td>";
					} elseif ( $timeformat == '12hours' ) {
						$output .= $hour . ":" . $minutes . $timeperiod . "</td>";
					}

					$output .= "<td";

					if ( $item->starttime < $time_now && $time_now < ( $item->starttime + $item->duration ) && $today == $item->dayid ) {
						$output .= ' class="now-playing"';
					}

					$output .= ">\n";

					if ( $item->address != "" ) {
						$output .= "<a target='" . $linktarget . "'href='" . $item->address . "'>";
					}

					$output .= $item->itemname;

					if ( $item->address != "" ) {
						"</a>";
					}

					$output .= "</td>";

					$output .= "<td>" . htmlspecialchars( stripslashes( $item->description ), ENT_QUOTES ) . "</td>";

					$output .= "</tr>";
				}
			}
		}
	}

	$output .= "</table>";

	$output .= "</div id='ws-schedule'>\n";

	$output .= "<!-- End of Weekly Schedule Flat Output -->\n";

	return $output;
}

function ws_day_list_func( $atts ) {
	$schedule  = 1;
	$max_items = 5;
	$empty_msg = 'No Items Found';

	extract(
		shortcode_atts(
			array(
				'schedule'  => 1,
				'cats'      => '',
				'max_items' => 5,
				'empty_msg' => 'No Items Found'
			), $atts
		)
	);

	$today  = date( 'w', current_time( 'timestamp', 0 ) ) + 1;
	$output = '<div class="ws_widget_output">';

	//fetch results
	global $wpdb;

	$schedule_query = 'SELECT * from ' . ws_db_prefix() .
		'wsitems WHERE day = ' . $today .
		' AND scheduleid = %d ';

	if ( !empty( $cats ) ) {
		$schedule_query .= ' AND category IN ( %s ) ';
	}

	$schedule_query .= 'ORDER by starttime ASC LIMIT 0, %d';

	if ( empty( $cats ) ) {
		$schedule_items = $wpdb->get_results( $wpdb->prepare( $schedule_query, $schedule, $max_items ) );
	} else {
		$schedule_items = $wpdb->get_results( $wpdb->prepare( $schedule_query, $schedule, $cats, $max_items ) );
	}

	if ( !empty( $schedule_items ) ) {
		$output .= '<ul>';

		foreach ( $schedule_items as $schedule_item ) {
			$item_name  = stripslashes( $schedule_item->name );
			$start_hour = $schedule_item->starttime;

			if ( strpos( $start_hour, '.' ) > 0 ) {
				$start_hour = substr( $start_hour, 0, strlen( $start_hour ) - strpos( $start_hour, '.' ) );
				$start_hour .= ':30';
			} else {
				$start_hour .= ":00";
			}

			$output .= '<li>';
			if ( !empty( $schedule_item->address ) ) {
				$output .= '<a href="' . $schedule_item->address . '">';
			}
			$output .= $start_hour . ' - ' . $item_name;

			if ( !empty( $schedule_item->address ) ) {
				$output .= '</a>';
			}
			$output .= '</li>';
		}

		$output .= '</ul>';
	} else {
		$output .= $empty_msg;
	}

	$output .= '</div>';

	return $output;
}


add_shortcode( 'weekly-schedule',       'ws_library_func'      );
add_shortcode( 'flat-weekly-schedule',  'ws_library_flat_func' );
add_shortcode( 'daily-weekly-schedule', 'ws_day_list_func'     );

function ws_conditional_header( $posts ) {
	if ( empty( $posts ) ) {
		return $posts;
	}

	$load_jquery = false;
	$load_qtip   = false;
	$load_style  = false;

	$genoptions = get_option( 'WeeklyScheduleGeneral' );

	foreach ( $posts as $post ) {
		$continuesearch = true;
		$searchpos      = 0;
		$scheduleids    = array();

		while ( $continuesearch ) {
			$weeklyschedulepos = stripos( $post->post_content, 'weekly-schedule ', $searchpos );
			if ( $weeklyschedulepos == false ) {
				$weeklyschedulepos = stripos( $post->post_content, 'weekly-schedule]', $searchpos );
			}
			$continuesearch = $weeklyschedulepos;
			if ( $continuesearch ) {
				$load_style   = true;
				$shortcodeend = stripos( $post->post_content, ']', $weeklyschedulepos );
				if ( $shortcodeend ) {
					$searchpos = $shortcodeend;
				} else {
					$searchpos = $weeklyschedulepos + 1;
				}

				if ( $shortcodeend ) {
					$settingconfigpos = stripos( $post->post_content, 'settings=', $weeklyschedulepos );
					if ( $settingconfigpos && $settingconfigpos < $shortcodeend ) {
						$schedule = substr( $post->post_content, $settingconfigpos + 9, $shortcodeend - $settingconfigpos - 9 );

						$scheduleids[] = $schedule;
					} else if ( count( $scheduleids ) == 0 ) {
						$scheduleids[] = 1;
					}
				}
			}
		}
	}

	if ( $scheduleids ) {
		foreach ( $scheduleids as $scheduleid ) {
			$schedulename = 'WS_PP' . $scheduleid;
			$options      = get_option( $schedulename );

			if ( $options['displaydescription'] == "tooltip" ) {
				$load_jquery = true;
				$load_qtip   = true;
			}
		}
	}

	if ( isset( $genoptions['includestylescript'] ) && !empty( $genoptions['includestylescript'] ) ) {
		$pagelist = explode( ',', $genoptions['includestylescript'] );
		foreach ( $pagelist as $pageid ) {
			if ( is_page( $pageid ) ) {
				$load_jquery = true;
				$load_style  = true;
				$load_qtip   = true;
			}
		}
	}

	if ( $load_jquery ) {
		wp_enqueue_script( 'jquery' );
	}

	if ( $load_qtip ) {
		wp_enqueue_style( 'qtipstyle', plugins_url( 'jquery-qtip/jquery.qtip.min.css', __FILE__ ) );
		wp_enqueue_script( 'qtip', plugins_url( 'jquery-qtip/jquery.qtip.min.js', __FILE__ ) );
		wp_enqueue_script( 'imagesloaded', plugins_url( 'jquery-qtip/imagesloaded.pkg.min.js', __FILE__ ), 'qtip' );
	}

	return $posts;
}
add_filter( 'the_posts', 'ws_conditional_header' ); // the_posts gets triggered before wp_head


function ws_conditionally_add_scripts_and_styles( $posts ) {
	if ( empty( $posts ) ) {
		return $posts;
	}

	$load_style = false;

	$genoptions = get_option( 'WeeklyScheduleGeneral' );

	if ( is_admin() ) {
		$load_jquery   = false;
		$load_thickbox = false;
		$load_style    = false;
	} else {
		foreach ( $posts as $post ) {
			$linklibrarypos = stripos( $post->post_content, 'weekly-schedule', 0 );
			if ( $linklibrarypos !== false ) {
				$load_style = true;
			}
		}
	}

	global $wsstylesheet;
	if ( $load_style ) {
		$wsstylesheet = true;
	} else {
		$wsstylesheet = false;
	}

	return $posts;
}
add_filter( 'the_posts', 'ws_conditionally_add_scripts_and_styles' );

function ws_header_output() {
	global $wsstylesheet;
	$genoptions = get_option( 'WeeklyScheduleGeneral' );
	if ( $wsstylesheet ) {
		echo "<style id='WeeklyScheduleStyle' type='text/css'>\n";
        echo stripslashes( $genoptions['fullstylesheet'] );
		echo "</style>\n";
	}
}
add_action( 'wp_head', 'ws_header_output' );

function ws_register_widget() {
	register_widget( "WSTodayScheduleWidget" );
}
include("includes/WSTodayScheduleWidget.php");
/* Register widgets */
add_action( 'widgets_init', 'ws_register_widget' );

?>
