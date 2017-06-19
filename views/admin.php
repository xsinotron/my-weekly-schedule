<div class="wrap">
    <h2><?=__('Weekly Schedule Configuration','ws');?></h2>

    <form name='wsadmingenform' action="<?php echo add_query_arg( 'page', 'weekly-schedule', admin_url( 'options-general.php' ) ); ?>" method="post" id="ws-conf" enctype="multipart/form-data">
        <input type="hidden" name="MAX_FILE_SIZE" value="1000000" />
        <?php
        if ( function_exists( 'wp_nonce_field' ) ) {
            wp_nonce_field( 'wspp-config' );
        }
        ?>
        <fieldset style='border:1px solid #CCC;padding:10px'>
            <legend class="tooltip" title='These apply to all schedules' style='padding: 0 5px 0 5px;'>
                <strong><?=__('General Settings','ws');?>
                    <span style="border:0;padding-left: 15px;" class="submit"><input class="button button-primary" type="submit" name="submitgen" value="<?=__('Update General Settings &raquo;','ws');?>" /></span></strong>
            </legend>
            <table>
                <tr>
                    <td style='padding: 8px; vertical-align: top'>
                        <table>
                            <tr>
                                <td><?=__('Import Schedule Items','ws');?> (<a href="<?php echo plugins_url( 'importtemplate.csv', __FILE__ ); ?>"><?=__('Template','ws');?></a>)</td>
                                <td><input size="80" name="schedulefile" type="file" /></td>
                                <td><input class="button" type="submit" name="importschedule" value="<?=__('Import Items','ws');?>" /></td>
                            </tr>
                            <tr>
                                <td><?=__('Import File Delimiter','ws');?></td>
                                <td>
                                    <input type="text" id="csvdelimiter" name="csvdelimiter" size="1" value="<?php if ( !isset( $genoptions['csvdelimiter'] ) ) $genoptions['csvdelimiter'] = ','; echo $genoptions['csvdelimiter']; ?>" /></td>
                            </tr>
                            <tr>
                                <td style='width:200px'><?=__('Stylesheet File Name','ws');?></td>
                                <td>
                                    <input type="text" id="stylesheet" name="stylesheet" size="40" value="<?php echo $genoptions['stylesheet']; ?>" />
                                </td>
                            </tr>
                            <?php if (current_user_can( 'manage_options' )) { ?>
                            <tr>
                                <td style='width:200px'><?=__('Access level required', 'ws');?></td>
                                <td>
                                    <?php } ?>
                                    <select <?php if ( !current_user_can( 'manage_options' ) ) {
                                        echo 'style="display: none"';
                                    } ?> id="accesslevel" name="accesslevel">
                                        <?php $levels = array( 'admin' => 'Administrator', 'editor' => 'Editor', 'author' => 'Author', 'contributor' => 'Contributor', 'subscriber' => 'Subscriber' );
                                        if ( !isset( $genoptions['accesslevel'] ) || empty( $genoptions['accesslevel'] ) ) {
                                            $genoptions['accesslevel'] = 'admin';
                                        }

                                        foreach ( $levels as $key => $level ) {
                                            echo '<option value="' . $key . '" ' . selected( $genoptions['accesslevel'], $key, false ) . '>' . $level . '</option>';
                                        } ?>
                                    </select>
                                    <?php if (current_user_can( 'manage_options' )) { ?>
                                </td>
                            </tr>
                        <?php } ?>
                            <tr>
                                <td><?=__('Number of Schedules','ws');?></td>
                                <td>
                                    <input type="text" id="numberschedules" name="numberschedules" size="5" value="<?php if ( $genoptions['numberschedules'] == '' ) {
                                        echo '2';
                                    }
                                    echo $genoptions['numberschedules']; ?>" /></td>
                            </tr>
                            <tr>
                                <td style="padding-left: 10px;padding-right:10px"><?=__('Debug Mode','ws');?></td>
                                <td>
                                    <input type="checkbox" id="debugmode" name="debugmode" <?php if ( $genoptions['debugmode'] ) {
                                        echo ' checked="checked" ';
                                    } ?>/></td>
                            </tr>
                            <tr>
                                <td colspan="2"><?=__('Additional pages to style (Comma-Separated List of Page IDs)','ws');?></td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <input type='text' name='includestylescript' style='width: 200px' value='<?php echo $genoptions['includestylescript']; ?>' />
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </fieldset>
    </form>

    <div style='padding-top: 15px;clear:both'>
        <fieldset style='border:1px solid #CCC;padding:10px'>
            <legend style='padding: 0 5px 0 5px;'><strong><?=__('Schedule Selection and Usage Instructions','ws');?></strong></legend>
            <table class='widefat striped'>
                <thead>
                    <tr>
                        <th style='width:80px'  class="tooltip"><?=__('ID','ws');?></th>
                        <th style='width:130px' class="tooltip"><?=__('Schedule Name','ws');?></th>
                        <th                     class="tooltip"><?=__('Code to insert on a Wordpress page to see Weekly Schedule','ws');?></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php 
                $numberofschedules = $genoptions['numberschedules'];
                for( $counter = 1; $counter <= $numberofschedules; $counter ++ ) {
                    $tempoptionname = "WS_PP" . $counter;
                    $tempoptions    = get_option( $tempoptionname );
                    $schedulename   = $tempoptions['schedulename'];
                    $selected       = $schedule == $counter;
                    echo(
                    "<tr class='$selected' >
                        <td style=''>$counter</td>
                        <td style=''>$schedulename</td>
                        <td style=''>[weekly-schedule schedule=\"$counter\"]</td>
                        <td style='text-align:right'>
                            <a class='button' href='?page=weekly-schedule&settings=$adminpage&schedule=$counter' title='".__('modify','ws')."'><span class='fa fa-pencil'/></a>
                            <a class='button' href='?page=weekly-schedule&settings=$adminpage&schedule=$counter' title='".__('copy','ws')."'><span class='fa fa-files-o'/></a>
                            <a class='button' href='?page=weekly-schedule&settings=$adminpage&schedule=$counter' title='".__('delete','ws')."'><span class='fa fa-trash-o'/></a>
                        </td>
                    </tr>"
                    );
                }
                ?>
                </tbody>
            </table>
            <FORM name="scheduleselection">
                <?=__('Select Current Schedule:','ws');?>
                <SELECT name="schedulelist" style='width: 300px'>
                    <?php if ( $genoptions['numberschedules'] == '' ) {
                        $numberofschedules = 2;
                    } else {
                        $numberofschedules = $genoptions['numberschedules'];
                    }
                    for ( $counter = 1; $counter <= $numberofschedules; $counter ++ ): ?>
                        <?php $tempoptionname = "WS_PP" . $counter;
                        $tempoptions          = get_option( $tempoptionname ); ?>
                        <option value="<?php echo $counter ?>" <?php if ( $schedule == $counter ) {
                            echo 'SELECTED';
                        } ?>><?=__('Schedule:','ws');?> <?php echo $counter ?><?php if ( $tempoptions != "" ) {
                                echo " (" . $tempoptions['schedulename'] . ")";
                            } ?></option>
                    <?php endfor; ?>
                </SELECT>
                <!--INPUT type="button" class="button button-primary" name="go" value="<?=__('Go!','ws');?>" onClick="window.location= '?page=weekly-schedule&amp;settings=<?php echo $adminpage; ?>&amp;schedule=' + document.scheduleselection.schedulelist.options[document.scheduleselection.schedulelist.selectedIndex].value"-->
                <?=__('Copy from:','ws');?>
                <SELECT name="copysource" style='width: 300px'>
                    <?php if ( $genoptions['numberschedules'] == '' ) {
                        $numberofschedules = 2;
                    } else {
                        $numberofschedules = $genoptions['numberschedules'];
                    }
                    for ( $counter = 1; $counter <= $numberofschedules; $counter ++ ): ?>
                        <?php $tempoptionname = "WS_PP" . $counter;
                        $tempoptions          = get_option( $tempoptionname );
                        if ( $counter != $schedule ):?>
                            <option value="<?php echo $counter ?>" <?php if ( $schedule == $counter ) {
                                echo 'SELECTED';
                            } ?>><?=__('Schedule:','ws');?> <?php echo $counter ?><?php if ( $tempoptions != "" ) {
                                    echo " (" . $tempoptions['schedulename'] . ")";
                                } ?></option>
                        <?php endif;
                    endfor; ?>
                </SELECT>
                <INPUT type="button" class="button button-primary" name="copy" value="<?=__('Copy!','ws');?>" onClick="window.location= '?page=weekly-schedule&amp;copy=<?php echo $schedule; ?>&source=' + document.scheduleselection.copysource.options[document.scheduleselection.copysource.selectedIndex].value">
                <br />
            </FORM>
        </fieldset>
    </div>
    <br />


    <fieldset style='border:1px solid #CCC;padding:10px'>
    <legend style='padding: 0 5px 0 5px;'>
        <strong><?=__('Settings for Schedule','ws');?> <?php echo $schedule; ?> - <?php echo $options['schedulename']; ?></strong>
    </legend>
    <?php if (( $adminpage == "" ) || ( $adminpage == "general" )): ?>
    <h3 class="nav-tab-wrapper">
        <a class="nav-tab nav-tab-active" href="?page=weekly-schedule&amp;settings=general&amp;schedule=<?php echo $schedule; ?>"><?=__('General Settings','ws'); ?></a>
        <a class="nav-tab" href="?page=weekly-schedule&amp;settings=categories&amp;schedule=<?php echo $schedule; ?>"><?=__('Manage Schedule Categories','ws');?></a>
        <a class="nav-tab" href="?page=weekly-schedule&amp;settings=items&amp;schedule=<?php echo $schedule; ?>"><?=__('Manage Schedule Items','ws');             ?></a>
        <a class="nav-tab" href="?page=weekly-schedule&amp;settings=days&amp;schedule=<?php echo $schedule; ?>"><?=__('Manage Days Labels','ws');                ?></a>
    </h3>
    <form name="wsadminform" action="<?php echo add_query_arg( 'page', 'weekly-schedule', admin_url( 'options-general.php' ) ); ?>" method="post" id="ws-config">
    <?php
    if ( function_exists( 'wp_nonce_field' ) ) {
        wp_nonce_field( 'wspp-config' );
    }
    ?>
    <!-- NAME -->
    <?=__('Schedule Name','ws');?> :
    <input type="text" id="schedulename" name="schedulename" size="80" value="<?php echo $options['schedulename']; ?>" /><br /><br />
    
    
    <!-- TIME RELATED SETTINGS -->
    <strong><?=__('Time-related Settings','ws');?></strong><br />
    <input type="hidden" name="schedule" value="<?php echo $schedule; ?>" />
    <table>
        <tr>
            <td><?=__('Schedule Layout','ws');?></td>
            <td><select style="width: 200px" name='layout'>
                    <?php $layouts = array( "horizontal" => "Horizontal", "vertical" => "Vertical" );
                    foreach ( $layouts as $key => $layout ) {
                        if ( $key == $options['layout'] ) {
                            $samedesc = "selected='selected'";
                        } else {
                            $samedesc = "";
                        }

                        echo "<option value='" . $key . "' " . $samedesc . ">" . $layout . "\n";
                    }
                    ?>
                </select></td>
            <td><?=__('Time Display Format','ws');?></td>
            <td><select style="width: 200px" name='timeformat'>
                    <?php $descriptions = array( 
                        "24hours"      => __('24 Hours (e.g. 17h30)','ws'),
                        "24hourscolon" => __('24 Hours with Colon (e.g. 17:30)','ws'),
                        "12hours"      => __('12 Hours (e.g. 1:30pm)','ws')
                    );
                    foreach ( $descriptions as $key => $description ) {
                        if ( $key == $options['timeformat'] ) {
                            $samedesc = "selected='selected'";
                        } else {
                            $samedesc = "";
                        }

                        echo "<option value='" . $key . "' " . $samedesc . ">" . $description . "\n";
                    }
                    ?>
                </select></td>
        </tr>
        <tr>
            <td><?=__('Start Time','ws');?></td>
            <td><select style='width: 200px' name="starttime">
                    <?php $timedivider = ( in_array( $options['timedivision'], array( '1.0', '2.0', '3.0' ) ) ? '1.0' : $options['timedivision'] );
                    $maxtime           = 24 + $timedivider;
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


                        if ( $i == $options['starttime'] ) {
                            $selectedstring = "selected='selected'";
                        } else {
                            $selectedstring = "";
                        }

                        if ( $options['timeformat'] == '24hours' ) {
                            echo "<option value='" . $i . "'" . $selectedstring . ">" . $hour . "h" . $minutes . "\n";
                        } else if ( $options['timeformat'] == '24hourscolon' ) {
                            echo "<option value='" . $i . "'" . $selectedstring . ">" . $hour . ":" . $minutes . "\n";
                        } else if ( $options['timeformat'] == '12hours' ) {
                            echo "<option value='" . $i . "'" . $selectedstring . ">" . $hour . ":" . $minutes . $timeperiod . "\n";
                        }
                    }
                    ?>
                </select></td>
            <td><?=__('End Time','ws');?></td>
            <td><select style='width: 200px' name="endtime">
                    <?php for ( $i = 0; $i < $maxtime; $i += $timedivider ) {
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


                        if ( $i == $options['endtime'] ) {
                            $selectedstring = "selected='selected'";
                        } else {
                            $selectedstring = "";
                        }

                        if ( $options['timeformat'] == '24hours' ) {
                            echo "<option value='" . $i . "'" . $selectedstring . ">" . $hour . "h" . $minutes . "\n";
                        } elseif ( $options['timeformat'] == '24hourscolon' ) {
                            echo "<option value='" . $i . "'" . $selectedstring . ">" . $hour . ":" . $minutes . "\n";
                        } elseif ( $options['timeformat'] == '12hours' ) {
                            echo "<option value='" . $i . "'" . $selectedstring . ">" . $hour . ":" . $minutes . $timeperiod . "\n";
                        }
                    }
                    ?>
                </select></td>
        </tr>
        <tr>
            <td><?=__('Cell Time Division','ws');?></td>
            <td><select style='width: 250px' name='timedivision'>
                    <?php $timedivisions = array(
                        "0.25" => __('Quarter-Hourly (15 min intervals)','ws'),
                        ".50"  => __('Half-Hourly (30 min intervals)','ws'),
                        "1.0"  => __('Hourly (60 min intervals)','ws'),
                        "2.0"  => __('Bi-Hourly (120 min intervals)','ws'),
                        "3.0"  => __('Tri-Hourly (180 min intervals)','ws')
                    );
                    foreach ( $timedivisions as $key => $timedivision ) {
                        if ( $key == $options['timedivision'] ) {
                            $sametime = "selected='selected'";
                        } else {
                            $sametime = "";
                        }

                        echo "<option value='" . $key . "' " . $sametime . ">" . $timedivision . "\n";
                    }
                    ?>
                </select></td>
            <td><?=__('Show Description','ws');?></td>
            <td><select style="width: 200px" name='displaydescription'>
                    <?php $descriptions = array(
                        "tooltip" => __('Show as tooltip','ws'),
                        "cell"    => __('Show in cell after item name','ws'),
                        "none"    => __('Do not display','ws')
                    );
                    foreach ( $descriptions as $key => $description ) {
                        if ( $key == $options['displaydescription'] ) {
                            $samedesc = "selected='selected'";
                        } else {
                            $samedesc = "";
                        }

                        echo "<option value='" . $key . "' " . $samedesc . ">" . $description . "\n";
                    }
                    ?>
                </select></td>
        </tr>
        <tr>
            <td colspan='2'><?=__('Day List (comma-separated Day IDs to specify days to be displayed and their order)','ws');?>
            </td>
            <td colspan='2'>
                <input type='text' name='daylist' style='width: 200px' value='<?php echo $options['daylist']; ?>' />
            </td>
        </tr>
        <tr>
            <td><?=__('Target Window Name','ws');?>
            </td>
            <td>
                <input type='text' name='linktarget' style='width: 250px' value='<?php echo $options['linktarget']; ?>' />
            </td>
        </tr>
    </table>
    <br />
    <br />
    
    <!--TOOLTIP-->
    <strong><?=__('Tooltip Configuration','ws');?></strong>
    <table>
        <tr>
            <td><?=__('Tooltip Color Scheme','ws');?></td>
            <td><select name='tooltipcolorscheme' style='width: 100px'>
                    <?php $colors = array( 
                        'qtip-navy'    => __('navy','ws'),
                        'qtip-blue'    => __('blue','ws'),
                        'qtip-aqua'    => __('aqua','ws'),
                        'qtip-teal'    => __('teal','ws'),
                        'qtip-olive'   => __('olive','ws'),
                        'qtip-green'   => __('green','ws'),
                        'qtip-lime'    => __('lime','ws'),
                        'qtip-yellow'  => __('yellow','ws'),
                        'qtip-orange'  => __('orange','ws'),
                        'qtip-red'     => __('red','ws'),
                        'qtip-fuchsia' => __('fuchsia','ws'),
                        'qtip-purple'  => __('purple','ws'),
                        'qtip-maroon'  => __('maroon','ws'),
                        'qtip-white'   => __('white','ws'),
                        'qtip-gray'    => __('gray','ws'),
                        'qtip-silver'  => __('silver','ws'),
                        'qtip-black'   => __('black','ws')
                    );
                    foreach ( $colors as $key => $color ) {
                        if ( $key == $options['tooltipcolorscheme'] ) {
                            $samecolor = "selected='selected'";
                        } else {
                            $samecolor = "";
                        }

                        echo "<option value='" . $key . "' " . $samecolor . ">" . $color . "\n";
                    }
                    ?>
                </select></td>
            <td><?=__('Tooltip Width','ws');?></td>
            <td>
                <input type='text' name='tooltipwidth' style='width: 100px' value='<?php echo $options['tooltipwidth']; ?>' />
            </td>
        </tr>
        <tr>
            <td><?=__('Tooltip Anchor Point on Data Cell','ws');?></td>
            <td><select name='tooltiptarget' style='width: 200px'>
                    <?php $positions = array(
                        'top left'     => __('Top-Left Corner','ws'),         'top center'    => __('Middle of Top Side','ws'),
                        'top right'    => __('Top-Right Corner','ws'),        'right top'     => __('Right Side of Top-Right Corner','ws'),
                        'right center' => __('Middle of Right Side','ws'),    'right bottom'  => __('Right Side of Bottom-Right Corner','ws'),
                        'bottom left'  => __('Under Bottom-Left Side','ws'),  'bottom center' => __('Under Middle of Bottom Side','ws'),
                        'bottom right' => __('Under Bottom-Right Side','ws'), 'left top'      => __('Left Side of Top-Left Corner','ws'),
                        'left center'  => __('Middle of Left Side','ws'),     'left bottom'   => __('Left Side of Bottom-Left Corner','ws'),
                    );

                    foreach ( $positions as $index => $position ) {
                        if ( $index == $options['tooltiptarget'] ) {
                            $sameposition = "selected='selected'";
                        } else {
                            $sameposition = "";
                        }

                        echo "<option value='" . $index . "' " . $sameposition . ">" . $position . "\n";
                    }

                    ?>
                </select></td>
            <td><?=__('Tooltip Attachment Point','ws');?></td>
            <td><select name='tooltippoint' style='width: 200px'>
                    <?php $positions = array(
                        'top left'     => __('Top-Left Corner','ws'),         'top center'    => __('Middle of Top Side','ws'),
                        'top right'    => __('Top-Right Corner','ws'),        'right top'     => __('Right Side of Top-Right Corner','ws'),
                        'right center' => __('Middle of Right Side','ws'),    'right bottom'  => __('Right Side of Bottom-Right Corner','ws'),
                        'bottom left'  => __('Under Bottom-Left Side','ws'),  'bottom center' => __('Under Middle of Bottom Side','ws'),
                        'bottom right' => __('Under Bottom-Right Side','ws'), 'left top'      => __('Left Side of Top-Left Corner','ws'),
                        'left center'  => __('Middle of Left Side','ws'),     'left bottom'   => __('Left Side of Bottom-Left Corner','ws'),
                    );
                    foreach ( $positions as $index => $position ) {
                        if ( $index == $options['tooltippoint'] ) {
                            $sameposition = "selected='selected'";
                        } else {
                            $sameposition = "";
                        }

                        echo "<option value='" . $index . "' " . $sameposition . ">" . $position . "\n";
                    }

                    ?>
                </select></td>
        </tr>
        <tr>
            <td><?=__('Auto-Adjust Position to be visible','ws');?></td>
            <td>
                <input type="checkbox" id="adjusttooltipposition" name="adjusttooltipposition" <?php if ( $options['adjusttooltipposition'] == true ) {
                    echo ' checked="checked" ';
                } ?>/></td>
            <td></td>
            <td></td>
        </tr>
    </table>
    <p style="border:0;" class="submit">
        <input class="button button-primary" type="submit" name="submit" value="<?=__('Update Settings &raquo;', 'ws');?>" />
    </p>
    </form>
    </fieldset>
    <?php /* --------------------------------------- Categories --------------------------------- */ ?>
    <?php elseif ( $adminpage == "categories" ): ?>
        <a href="?page=weekly-schedule&amp;settings=general&amp;schedule=<?php    echo $schedule; ?>"><?=__('General Settings','ws');                  ?></a> |
        <a href="?page=weekly-schedule&amp;settings=categories&amp;schedule=<?php echo $schedule; ?>"><strong><?=__('Manage Schedule Categories','ws');?></strong></a> |
        <a href="?page=weekly-schedule&amp;settings=items&amp;schedule=<?php      echo $schedule; ?>"><?=__('Manage Schedule Items','ws');             ?></a> |
        <a href="?page=weekly-schedule&amp;settings=days&amp;schedule=<?php       echo $schedule; ?>"><?=__('Manage Days Labels','ws');                ?></a>
        <div style='float:left;margin-right: 15px'>
            <form name="wscatform" action="" method="post" id="ws-config">
                <?php
                if ( function_exists( 'wp_nonce_field' ) ) {
                    wp_nonce_field( 'wspp-config' );
                }
                ?>
                <?php if ( $mode == "edit" ): ?>
                    <strong><?=__('Editing Category','ws');?> #<?php echo $selectedcat->id; ?></strong><br />
                <?php endif; ?>
                <?=__('Category Name:','ws');?> <input style="width:300px" type="text" name="name" <?php if ( $mode == "edit" ) {
                    echo "value='" . $selectedcat->name . "'";
                } ?>/>
                <br><?=__('Background Cell Color (optional)','ws');?>
                <input style="width:100px" class="color-field" type="text" name="backgroundcolor" <?php if ( $mode == "edit" ) {
                    echo "value='" . $selectedcat->backgroundcolor . "'";
                } ?>/>
                <input type="hidden" name="id" value="<?php if ( $mode == "edit" ) {
                    echo $selectedcat->id;
                } ?>" />
                <input type="hidden" name="schedule" value="<?php echo $schedule; ?>" />
                <?php if ( $mode == "edit" ): ?>
                    <p style="border:0;" class="submit">
                        <input class="button button-primary" type="submit" name="updatecat" value="<?=__('Update &raquo;','ws');?>" /></p>
                <?php else: ?>
                    <p style="border:0;" class="submit">
                        <input class="button button-primary" type="submit" name="newcat" value="<?=__('Insert New Category &raquo;','ws');?>" /></p>
                <?php endif; ?>
            </form>
        </div>
        <div>
            <?php $cats = $wpdb->get_results( "SELECT count( i.id ) AS nbitems, c.name, c.id, c.backgroundcolor, c.scheduleid FROM " . ws_db_prefix() . "wscategories c LEFT JOIN " . ws_db_prefix() . "wsitems i ON i.category = c.id WHERE c.scheduleid = " . $schedule . " GROUP BY c.id" );
            if ( $cats ): ?>
                <table class='widefat' style='clear:none;width:400px;background: #DFDFDF url(/wp-admin/images/gray-grad.png) repeat-x scroll left top;'>
                    <thead>
                    <tr>
                        <th scope='col' style='width: 50px' id='id' class='manage-column column-id'><?=__('ID','ws');?></th>
                        <th scope='col' id='name' class='manage-column column-name' style=''><?=__('Name','ws');?></th>
                        <th scope='col' style='width: 50px;text-align: right' id='color' class='manage-column column-color' style=''><?=__('Color','ws');?></th>
                        <th scope='col' style='width: 50px;text-align: right' id='items' class='manage-column column-items' style=''><?=__('Items','ws');?></th>
                        <th style='width: 30px'></th>
                    </tr>
                    </thead>

                    <tbody id='the-list' class='list:link-cat'>

                    <?php foreach ( $cats as $cat ): ?>
                        <tr>
                            <td class='name column-name' style='background: #FFF'><?php echo $cat->id; ?></td>
                            <td style='background: #FFF'>
                                <a href='?page=weekly-schedule&amp;editcat=<?php echo $cat->id; ?>&schedule=<?php echo $schedule; ?>'><strong><?php echo $cat->name; ?></strong></a>
                            </td>
                            <td style='background: <?php echo $cat->backgroundcolor != null ? $cat->backgroundcolor : '#FFF'; ?>;text-align:right'></td>
                            <td style='background: #FFF;text-align:right'><?php echo $cat->nbitems; ?></td>
                            <?php if ( $cat->nbitems == 0 ): ?>
                                <td style='background:#FFF'>
                                    <a href='?page=weekly-schedule&amp;deletecat=<?php echo $cat->id; ?>&schedule=<?php echo $schedule; ?>'
                                        <?php echo "onclick=\"if ( confirm('" . esc_js( sprintf( __( "You are about to delete this category '%s'\n  'Cancel' to stop, 'OK' to delete." ), $cat->name ) ) . "') ) { return true;}return false;\"" ?>><img src='<?php echo plugins_url( '/icons/delete.png', __FILE__ ); ?>' /></a>
                                </td>
                            <?php else: ?>
                                <td style='background: #FFF'></td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>

                    </tbody>
                </table>

            <?php endif; ?>

            <p><?=__("Categories can only be deleted when they don\'t have any associated items.",'ws');?></p>
        </div>
        <?php /* --------------------------------------- Items --------------------------------- */ ?>
    <?php
    elseif ( $adminpage == "items" ): ?>
        <a href="?page=weekly-schedule&amp;settings=general&amp;schedule=<?php    echo $schedule; ?>"><?=__('General Settings','ws');                  ?></a> |
        <a href="?page=weekly-schedule&amp;settings=categories&amp;schedule=<?php echo $schedule; ?>"><?=__('Manage Schedule Categories','ws');?></a> |
        <a href="?page=weekly-schedule&amp;settings=items&amp;schedule=<?php      echo $schedule; ?>"><strong><?=__('Manage Schedule Items','ws');             ?></strong></a> |
        <a href="?page=weekly-schedule&amp;settings=days&amp;schedule=<?php       echo $schedule; ?>"><?=__('Manage Days Labels','ws');                ?></a>
        <div style='float:left;margin-right: 15px;width: 500px;'>
            <form name="wsitemsform" action="" method="post" id="ws-config">
                <?php
                if ( function_exists( 'wp_nonce_field' ) ) {
                    wp_nonce_field( 'wspp-config' );
                }
                ?>

                <input type="hidden" name="id" value="<?php if ( $mode == 'edit' && isset( $selecteditem ) ) {
                    echo $selecteditem->id;
                } ?>" />
                <input type="hidden" name="oldrow" value="<?php if ( $mode == "edit" && isset( $selecteditem ) ) {
                    echo $selecteditem->row;
                } ?>" />
                <input type="hidden" name="oldday" value="<?php if ( $mode == "edit"  && isset( $selecteditem ) ) {
                    echo $selecteditem->day;
                } ?>" />
                <input type="hidden" name="schedule" value="<?php echo $schedule; ?>" />
                <?php if ( $mode == "edit"  && isset( $selecteditem ) ): ?>
                    <strong>Editing Item #<?php echo $selecteditem->id; ?></strong>
                <?php endif; ?>

                <table>
                    <?php
                    if ( function_exists( 'wp_nonce_field' ) ) {
                        wp_nonce_field( 'wspp-config' );
                    }
                    ?>
                    <tr>
                        <td style='width: 180px'><?=__('Item Title','ws');?></td>
                        <td><input style="width:360px" type="text" name="name" <?php if ( $mode == "edit" && isset( $selecteditem ) ) {
                                echo "value='" . stripslashes( $selecteditem->name ) . "'";
                            } ?>/></td>
                    </tr>
                    <tr>
                        <td><?=__('Category','ws');?></td>
                        <td><select style='width: 360px' name="category">
                                <?php $cats = $wpdb->get_results( "SELECT * from " . ws_db_prefix() . "wscategories where scheduleid = " . $schedule . " ORDER by name" );

                                foreach ( $cats as $cat ) {
                                    if ( isset( $selecteditem ) && $cat->id == $selecteditem->category ) {
                                        $selectedstring = "selected='selected'";
                                    } else {
                                        $selectedstring = "";
                                    }

                                    echo "<option value='" . $cat->id . "' " . $selectedstring . ">" . $cat->name . "\n";
                                }
                                ?></select></td>
                    </tr>
                    <tr>
                        <td><?=__('Description','ws');?></td>
                        <td>
                            <textarea id="description" rows="5" cols="45" name="description"><?php if ( $mode == "edit" && isset( $selecteditem ) ) {
                                    echo stripslashes( $selecteditem->description );
                                } ?></textarea></td>
                    </tr>
                    <tr>
                        <td><?=__('Web Address','ws');?></td>
                        <td><input style="width:360px" type="text" name="address" <?php if ( $mode == "edit" && isset( $selecteditem ) ) {
                                echo "value='" . $selecteditem->address . "'";
                            } ?>/></td>
                    </tr>
                    <tr>
                        <td><?=__('Day','ws');?></td>
                        <td><select style='width: 360px' name="day">
                                <?php $days = $wpdb->get_results( "SELECT * from " . ws_db_prefix() . "wsdays where scheduleid = " . $schedule . " ORDER by id" );

                                foreach ( $days as $day ) {

                                    if ( isset( $selecteditem ) && $day->id == $selecteditem->day ) {
                                        $selectedstring = "selected='selected'";
                                    } else {
                                        $selectedstring = "";
                                    }

                                    echo "<option value='" . $day->id . "' " . $selectedstring . ">" . $day->name . "\n";
                                }
                                ?></select></td>
                    </tr>
                    <tr>
                        <td><?=__('Start Time','ws');?></td>
                        <td><select style='width: 360px' name="starttime">
                                <?php for ( $i = $options['starttime']; $i < $options['endtime']; $i += $options['timedivision'] ) {
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

                                    if ( isset( $selecteditem ) && $i == $selecteditem->starttime ) {
                                        $selectedstring = "selected='selected'";
                                    } else {
                                        $selectedstring = "";
                                    }

                                    if ( $options['timeformat'] == '24hours' ) {
                                        echo "<option value='" . $i . "'" . $selectedstring . ">" . $hour . "h" . $minutes . "\n";
                                    } elseif ( $options['timeformat'] == '24hourscolon' ) {
                                        echo "<option value='" . $i . "'" . $selectedstring . ">" . $hour . ":" . $minutes . "\n";
                                    } elseif ( $options['timeformat'] == '12hours' ) {
                                        echo "<option value='" . $i . "'" . $selectedstring . ">" . $hour . ":" . $minutes . $timeperiod . "\n";
                                    }
                                }
                                ?></select></td>
                    </tr>
                    <tr>
                        <td><?=__('Duration','ws');?></td>
                        <td><select style='width: 360px' name="duration">
                                <?php for ( $i = $options['timedivision']; $i <= ( $options['endtime'] - $options['starttime'] ); $i += $options['timedivision'] ) {
                                    if ( fmod( $i, 1 ) == 0.25 ) {
                                        $minutes = "15";
                                    } elseif ( fmod( $i, 1 ) == 0.50 ) {
                                        $minutes = "30";
                                    } elseif ( fmod( $i, 1 ) == 0.75 ) {
                                        $minutes = "45";
                                    } else {
                                        $minutes = "00";
                                    }

                                    if ( isset( $selecteditem ) && $i == $selecteditem->duration ) {
                                        $selectedstring = "selected='selected'";
                                    } else {
                                        $selectedstring = "";
                                    }

                                    echo "<option value='" . $i . "' " . $selectedstring . ">" . floor( $i ) . "h" . $minutes . "\n";
                                }
                                ?></select></td>
                    </tr>
                    <tr>
                        <td><?=__('Background Cell Color (optional)','ws');?></td>
                        <td>
                            <input style="width:100px" class="color-field" type="text" name="backgroundcolor" <?php if ( $mode == "edit" && isset( $selecteditem ) ) {
                                echo "value='" . $selecteditem->backgroundcolor . "'";
                            } ?>/></td>
                    </tr>
                    <tr>
                        <td><?=__('Title Color (optional)','ws');?></td>
                        <td>
                            <input style="width:100px" class="color-field" type="text" name="titlecolor" <?php if ( $mode == "edit" && isset( $selecteditem )) {
                                echo "value='" . $selecteditem->titlecolor . "'";
                            } ?>/></td>
                    </tr>
                </table>
                <?php if ( $mode == "edit" ): ?>
                    <p style="border:0;" class="submit">
                        <input class="button button-primary" type="submit" name="updateitem" value="<?=__('Update &raquo;','ws');?>" /></p>
                <?php else: ?>
                    <p style="border:0;" class="submit">
                        <input class="button button-primary" type="submit" name="newitem" value="<?=__('Insert New Item &raquo;','ws');?>" /></p>
                <?php endif; ?>
            </form>
        </div>
        <div>
            <?php
            $itemquery = "SELECT d.name as dayname, i.id, i.name, i.backgroundcolor, i.day, i.starttime FROM " . ws_db_prefix() . "wsitems as i, " . ws_db_prefix() . "wsdays as d WHERE i.day = d.id
                        and i.scheduleid = " . $schedule . " and d.scheduleid = " . $_GET['schedule'] . " ORDER by d.id, starttime, name";
            $items = $wpdb->get_results( $itemquery );

            if ( $items ): ?>
                <form name="wsitemdeletionform" action="?page=weekly-schedule&settings=items&schedule=<?php echo $schedule; ?>" method="post" id="ws-config">
                    <?php
                    if ( function_exists( 'wp_nonce_field' ) ) {
                        wp_nonce_field( 'wspp-config' );
                    }
                    ?>

                    <input class="button button-primary" type="submit" name="deleteallitems" value="<?=__('Delete all items in Schedule','ws');?> <?php echo $schedule; ?>" onclick="return confirm('<?=__('Are you sure you want to delete all items in Schedule','ws');?> <?php echo $schedule; ?>?')" />
                </form>
                <br />
                <table class='widefat' style='clear:none;width:500px;background: #DFDFDF url(/wp-admin/images/gray-grad.png) repeat-x scroll left top;'>
                    <thead>
                    <tr>
                        <th scope='col' style='width: 50px' id='id' class='manage-column column-id'><?=__('ID','ws');?></th>
                        <th scope='col' id='name' class='manage-column column-name' style=''><?=__('Name','ws');?></th>
                        <th scope='col' id='color' class='manage-column column-color' style=''><?=__('Color','ws');?></th>
                        <th scope='col' id='day' class='manage-column column-day' style='text-align: right'><?=__('Day','ws');?></th>
                        <th scope='col' style='width: 50px;text-align: right' id='starttime' class='manage-column column-items' style=''><?=__('Start Time','ws');?></th>
                        <th style='width: 30px'></th>
                    </tr>
                    </thead>

                    <tbody id='the-list' class='list:link-cat'>

                    <?php foreach ( $items as $item ): ?>
                        <tr>
                            <td class='name column-name' style='background: #FFF'>
                                <a href='?page=weekly-schedule&amp;edititem=<?php echo $item->id; ?>&amp;schedule=<?php echo $schedule; ?>'><strong><?php echo $item->id; ?></strong></a>
                            </td>
                            <td style='background: #FFF'>
                                <a href='?page=weekly-schedule&amp;edititem=<?php echo $item->id; ?>&amp;schedule=<?php echo $schedule; ?>'><strong><?php echo stripslashes( $item->name ); ?></strong></a>
                            </td>

                            <td style='background: <?php echo $item->backgroundcolor ? $item->backgroundcolor : '#FFF'; ?>'></td>
                            <td style='background: #FFF;text-align:right'><?php echo $item->dayname; ?></td>
                            <td style='background: #FFF;text-align:right'>
                                <?php

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
                                    echo $hour . "h" . $minutes . "\n";
                                } elseif ( $options['timeformat'] == '24hourscolon' ) {
                                    echo $hour . ":" . $minutes . "\n";
                                } elseif ( $options['timeformat'] == '12hours' ) {
                                    echo $hour . ":" . $minutes . $timeperiod . "\n";
                                }
                                ?></td>
                            <td style='background:#FFF'>
                                <a href='?page=weekly-schedule&amp;deleteitem=<?php echo $item->id; ?>&amp;schedule=<?php echo $schedule; ?>'
                                    <?php echo "onclick=\"if ( confirm('" . esc_js( sprintf( __( "You are about to delete the item '%s'\n  'Cancel' to stop, 'OK' to delete." ), $item->name ) ) . "') ) { return true;}return false;\""; ?>><img src='<?php echo plugins_url( '/icons/delete.png', __FILE__ ); ?>' /></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    </tbody>
                </table>
            <?php else: ?>
                <p>No items to display</p>
            <?php endif; ?>
        </div>
    <?php
    elseif ( $adminpage == "days" ): ?>
        <div>
            <a href="?page=weekly-schedule&amp;settings=general&amp;schedule=<?php    echo $schedule; ?>"><?=__('General Settings','ws');                  ?></a> |
            <a href="?page=weekly-schedule&amp;settings=categories&amp;schedule=<?php echo $schedule; ?>"><?=__('Manage Schedule Categories','ws');?></a> |
            <a href="?page=weekly-schedule&amp;settings=items&amp;schedule=<?php      echo $schedule; ?>"><?=__('Manage Schedule Items','ws');             ?></a> |
            <a href="?page=weekly-schedule&amp;settings=days&amp;schedule=<?php       echo $schedule; ?>"><strong><?=__('Manage Days Labels','ws');                ?></strong></a>
            <div>
                <form name="wsdaysform" action="" method="post" id="ws-config">
                    <?php
                    if ( function_exists( 'wp_nonce_field' ) ) {
                        wp_nonce_field( 'wspp-config' );
                    }

                    $days = $wpdb->get_results( "SELECT * from " . ws_db_prefix() . "wsdays WHERE scheduleid = " . $schedule . " ORDER by id" );

                    if ( $days ):
                        ?>
                        <input type="hidden" name="schedule" value="<?php echo $schedule; ?>" />
                        <table>
                            <tr>
                                <th style='text-align:left'><strong><?=__('ID','ws');?></strong></th>
                                <th style='text-align:left'><strong><?=__('Name','ws');?></strong></th>
                            </tr>
                            <?php foreach ( $days as $day ): ?>
                                <tr>
                                    <td style='width:30px;'><?php echo $day->id; ?></td>
                                    <td>
                                        <input style="width:300px" type="text" name="<?php echo $day->id; ?>" value='<?php echo $day->name; ?>' />
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </table>

                        <p style="border:0;" class="submit">
                            <input class="button button-primary" type="submit" name="updatedays" value="<?=__('Update &raquo;','ws');?>" /></p>

                    <?php endif; ?>

                </form>
            </div>
        </div>
    <?php
    endif; ?>
</div>
<div class="wrap">
    <fieldset style='border:1px solid #CCC;padding:10px'>
    <div class="footer">
        <a target='wsinstructions' href='http://wordpress.org/extend/plugins/weekly-schedule/installation/'><?=__('Installation Instructions','ws');?></a> |
        <a href='http://wordpress.org/extend/plugins/weekly-schedule/faq/' target='llfaq'><?=__('FAQ','ws');?></a> |
        <a href='http://yannickcorner.nayanna.biz/contact-me'><?=__('Contact the Author','ws');?></a>
        <a href="http://yannickcorner.nayanna.biz/wordpress-plugins/weekly-schedule/" target="weeklyschedule" style="float: right;"><img src="<?php echo plugins_url( '../icons/btn_donate_LG.gif', __FILE__ ); ?>" /></a>
    </div>
    </fieldset>
</div>