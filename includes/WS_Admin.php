<?php

	
	class WS_Admin {
		function WS_Admin() {
            $pagenow = isset( $_REQUEST['page'] ) ? $_REQUEST['page'] : "";
            if (is_admin() && $pagenow=='weekly-schedule') {
                wp_enqueue_style( 'ws-admin', plugins_url("weekly-schedule") . "/ws-admin.css" );
                wp_enqueue_style( 'ws-fontawesome', "https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" );
                wp_enqueue_script( 'ws-modal', plugins_url("weekly-schedule") . "/js/ws-modal.js", array('jquery'), "1.0", true );
            }
            function ws_load_translation_files() {
                // TODO erreur de chargement
                load_plugin_textdomain('ws', false, basename( dirname( __FILE__ ) ) . '../languages/');
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
			include(plugin_dir_path( __FILE__ ).'../views/css-editor.php');
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
          /**include(plugin_dir_path( __FILE__ ).'../views/admin.php');/*/
             include(plugin_dir_path( __FILE__ ).'../views/new-admin.php');
             include(plugin_dir_path( __FILE__ ).'../views/modal.php');//*/
		} // end config_page()

	} // end class WS_Admin
?>