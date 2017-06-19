<div class="ws-module-settings-container ws-admin-page hidden">
    <div class="ws-modal-navigation">
        <h2><?=__('Settings for Schedule','ws');?> <?=$schedule;?> - <?php echo $options['schedulename']; ?></h2>
        <button class="dashicons ws-close-modal" title="<?=__('Close','ws');?>"></button>
        <button class="ws-right dashicons" title="<?=__('NEXT','ws');?>"><span class="screen-reader-text"><?=__('NEXT','ws');?></span></button>
        <button class="ws-left dashicons"  title="<?=__('PREVIOUS','ws');?>"><span class="screen-reader-text"><?=__('PREVIOUS','ws');?></span></button>
    </div>
    <div class="ws-module-settings-content-container">
        <div class="ws-module-settings-content">
            <h3 class="nav-tab-wrapper" data-selected="<?="$adminpage";?>">
                <a class="nav-tab ws-nav-tab-general"    data-nav="general"    href="?page=weekly-schedule&amp;settings=general&amp;schedule=<?=$schedule;?>"><?=__('General Settings','ws'); ?></a>
                <a class="nav-tab ws-nav-tab-categories" data-nav="categories" href="?page=weekly-schedule&amp;settings=categories&amp;schedule=<?=$schedule;?>"><?=__('Manage Schedule Categories','ws');?></a>
                <a class="nav-tab ws-nav-tab-items"      data-nav="items"      href="?page=weekly-schedule&amp;settings=items&amp;schedule=<?=$schedule;?>"><?=__('Manage Schedule Items','ws'); ?></a>
                <a class="nav-tab ws-nav-tab-days"       data-nav="days"       href="?page=weekly-schedule&amp;settings=days&amp;schedule=<?=$schedule;?>"><?=__('Manage Days Labels','ws'); ?></a>
            </h3>
            <div class="ws-tab-content ws-general">
                <h3><?=__('General Settings','ws'); ?></h3>
                <form class="form-wrap" name="wsadminform" action="<?php echo add_query_arg( 'page', 'weekly-schedule', admin_url( 'admin.php' ) ); ?>" method="post" id="ws-config">
                    <?php if ( function_exists( 'wp_nonce_field' ) ) wp_nonce_field( 'wspp-config' ); ?>
                    <!-- NAME -->
                    <div class="form-field">
                        <label><?=__('Schedule Name','ws');?></label>
                        <input type="text" id="schedulename" name="schedulename" size="80" value="<?php echo $options['schedulename']; ?>" />
                    </div>
                    <!-- TIME RELATED SETTINGS -->
                    <fieldset>
                    <legend><strong><?=__('Time-related Settings','ws');?></strong></legend>
                    <br />
                    <input type="hidden" name="schedule" value="<?php echo $_REQUEST['schedule']; ?>" />
                    <table>
                        <tr>
                            <td><?=__('Schedule Layout','ws');?></td>
                            <td>
                                <select style="width: 200px" name='layout'>
                                    <?=$this->get_select_option_layout($options['layout']);?>
                                </select>
                            </td>
                            <td><?=__('Time Display Format','ws');?></td>
                            <td>
                                <select style="width: 200px" name='timeformat'>
                                    <?=$this->get_select_option_timeformat($options['timeformat']);?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td><?=__('Start Time','ws');?></td>
                            <td>
                                <select style='width: 200px' name="starttime">
                                    <?=$this->get_select_option_time($options, $options['starttime']);?>
                                </select>
                            </td>
                            <td>
                                <?=__('End Time','ws');?>
                            </td>
                            <td>
                                <select style='width: 200px' name="endtime">
                                    <?=$this->get_select_option_time($options, $options['endtime']);?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <?=__('Cell Time Division','ws');?>
                            </td>
                            <td>
                                <select style='width: 250px' name='timedivision'>
                                    <?=$this->get_select_option_timedivision($options['timedivision']);?>
                                </select>
                            </td>
                            <td>
                                <?=__('Show Description','ws');?>
                            </td>
                            <td>
                                <select style="width: 200px" name='displaydescription'>
                                    <?=$this->get_select_option_displaydescription($options['displaydescription']);?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td colspan='2'><?=__('Day List (comma-separated Day IDs to specify days to be displayed and their order)','ws');?></td>
                            <td colspan='2'><input type='text' name='daylist' style='width: 200px' value='<?php echo $options[' daylist ']; ?>' /> </td>
                        </tr>
                        <tr>
                            <td><?=__('Target Window Name','ws');?></td>
                            <td><input type='text' name='linktarget' style='width: 250px' value='<?php echo $options[' linktarget ']; ?>' /> </td>
                        </tr>
                    </table>
                    </fieldset>
                    <!--TOOLTIP-->
                    <fieldset>
                    <legend><strong><?=__('Tooltip Configuration','ws');?></strong></legend>
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
                    </fieldset>
                    <p style="border:0;" class="submit">
                        <input class="button button-primary" type="submit" name="submit" value="<?=__('Update Settings &raquo;', 'ws');?>" />
                    </p>
                </form>
            </div>
            <div class="ws-tab-content ws-categories">
                <h3><?=__('Manage Schedule Categories','ws');?></h3>
                <?php $cats = $wpdb->get_results( "SELECT count( i.id ) AS nbitems, c.name, c.id, c.backgroundcolor, c.scheduleid FROM " . ws_db_prefix() . "wscategories c LEFT JOIN " . ws_db_prefix() . "wsitems i ON i.category = c.id WHERE c.scheduleid = " . $schedule . " GROUP BY c.id" );
                if ( $cats ): ?>
                <table class='widefat striped'>
                    <thead>
                    <tr>
                        <th scope='col' id='id'    class='manage-column column-id'><?=__('ID','ws');?></th>
                        <th scope='col' id='name'  class='manage-column column-name' style=''><?=__('Name','ws');?></th>
                        <th scope='col' id='color' class='manage-column column-color' style=''><?=__('Color','ws');?></th>
                        <th scope='col' id='items' class='manage-column column-items' style=''><?=__('Items','ws');?></th>
                        <th style='width: 30px'></th>
                    </tr>
                    </thead>

                    <tbody id='the-list' class='list:link-cat'>

                    <?php foreach ( $cats as $cat ): ?>
                        <tr>
                            <td class='name column-name'><?php echo $cat->id; ?></td>
                            <td >
                                <a href='?page=weekly-schedule&amp;editcat=<?php echo $cat->id; ?>&schedule=<?php echo $schedule; ?>'><strong><?php echo $cat->name; ?></strong></a>
                            </td>
                            <td style='background: <?php echo $cat->backgroundcolor != null ? $cat->backgroundcolor : 'transparent'; ?>;text-align:right'></td>
                            <td><?php echo $cat->nbitems; ?></td>
                            <?php if ( $cat->nbitems == 0 ): ?>
                                <td class="ws-action-table">
                                    <a href='?page=weekly-schedule&amp;deletecat=<?php echo $cat->id; ?>&schedule=<?php echo $schedule; ?>'
                                        <?php echo "onclick=\"if ( confirm('" . esc_js( sprintf( __( "You are about to delete this category '%s'\n  'Cancel' to stop, 'OK' to delete." ), $cat->name ) ) . "') ) { return true;}return false;\"" ?>><span class="fa fa-trash-o"></span></a>
                                </td>
                            <?php else: ?>
                                <td></td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <p class="description"><i><?=__("Categories can only be deleted when they don't have any associated items.",'ws');?></i></p>
                <?php endif; ?>
                
                <form name="wscatform" action="" method="post" id="ws-config">
                    <fieldset>
                    <?php if ( function_exists( 'wp_nonce_field' ) ) wp_nonce_field( 'wspp-config' ); ?>
                    <?php
                        if ( $mode == "edit" && $adminpage == 'categories') {
                            //EDIT
                            $title  = __('Editing Category','ws'). " \"" . $selectedcat->name . "\" (".$selectedcat->id.")";
                            $btnName  = "updatecat";
                            $btnValue = __('Update &raquo;','ws');
                        }
                        else  if ( $mode != "edit" || $adminpage != 'categories') {
                            // NEW
                            $title    = __('New Category','ws');
                            $btnName  = "newcat";
                            $btnValue = __('Insert New Category &raquo;','ws');
                        }
                    ?>
                    <legend><strong><?=$title;?></strong></legend>
                    <div class="form-wrap">
                        <div class="form-field ws-col-left">
                        <label for="name"><?=__('Category Name:','ws');?></label>
                        <input type="text" name="name" placeholder="<?=__("Choose a name for this category...","ws");?>" <?php if ( $mode == "edit" ) { echo "value='" . $selectedcat->name . "'"; } ?>/>
                        </div>
                        <div class="form-field ws-col-right">
                        <label for="backgroundcolor"><?=__('Background Cell Color (optional)','ws');?></label>
                        <input type="text" class="color-field" name="backgroundcolor" <?php if ( $mode == "edit" ) {echo "value='" . $selectedcat->backgroundcolor . "'";} ?>/>
                        </div>
                    </div>
                    <input type="hidden" name="id" value="<?php if ( $mode == "edit" ) {echo $selectedcat->id;} ?>" />
                    <input type="hidden" name="schedule" value="<?php echo $schedule; ?>" />
                    <p class="submit"><input class="button button-primary" type="submit" name="<?=$btnName;?>" value="<?=$btnValue;?>" /></p>
                    </fieldset>
                </form>
            </div>
            <div class="ws-tab-content ws-items">
                <h3><?=__('Manage Schedule Items','ws'); ?></h3>
                <?php
                $itemquery = "SELECT d.name as dayname, i.id, i.name, i.backgroundcolor, i.day, i.starttime FROM " . ws_db_prefix() . "wsitems as i, " . ws_db_prefix() . "wsdays as d WHERE i.day = d.id and i.scheduleid = " . $schedule . " and d.scheduleid = " . $_GET['schedule'] . " ORDER by d.id, starttime, name";
                $items = $wpdb->get_results( $itemquery );
                $title = __('New item','ws');
                if ( $mode == 'edit' && isset( $selecteditem ) ) {
                    $title  = __('Editing Item','ws'). " \"" . $selecteditem->name . "\" (".$selecteditem->id.")";
                    $id     = $selecteditem->id;
                    $oldrow = $selecteditem->row;
                    $oldday = $selecteditem->day;
                }
                if ( $items ): ?>
                    <table class='widefat striped ws-item-table'>
                        <thead>
                            <tr>
                                <th scope='col' id='id'        class='manage-column column-id'    style='width: 50px'                  ><?=__('ID','ws');?></th>
                                <th scope='col' id='name'      class='manage-column column-name'  style=''                             ><?=__('Name','ws');?></th>
                                <th scope='col' id='color'     class='manage-column column-color' style=''                             ><?=__('Color','ws');?></th>
                                <th scope='col' id='day'       class='manage-column column-day'   style=''                             ><?=__('Day','ws');?></th>
                                <th scope='col' id='starttime' class='manage-column column-items' style='width: 50px;text-align: right'><?=__('Start Time','ws');?></th>
                                <th style='width: 30px'>
                                    <form name="wsitemdeletionform" action="?page=weekly-schedule&settings=items&schedule=<?php echo $schedule; ?>" method="post" id="ws-config">
                                        <?php if ( function_exists( 'wp_nonce_field' ) ) wp_nonce_field( 'wspp-config' );?>
                                        <button type="submit" class="button button-text align-right" name="deleteallitems" title="<?=__('Delete all items in Schedule','ws');?> <?php echo $schedule; ?>" onclick="return confirm('<?=__('Are you sure you want to delete all items in Schedule','ws');?> <?php echo $schedule; ?>?')">
                                            <span class="fa fa-trash-o"></span>
                                        </button>
                                    </form>
                                </th>
                            </tr>
                        </thead>
                        <tbody id='the-list' class='list:link-cat'>
                        <?php foreach ( $items as $item ): ?>
                            <tr>
                                <td class='name column-name'>
                                    <a href='?page=weekly-schedule&amp;edititem=<?php echo $item->id; ?>&amp;schedule=<?php echo $schedule; ?>'><strong><?php echo $item->id; ?></strong></a>
                                </td>
                                <td>
                                    <a href='?page=weekly-schedule&amp;edititem=<?php echo $item->id; ?>&amp;schedule=<?php echo $schedule; ?>'><strong><?php echo stripslashes( $item->name ); ?></strong></a>
                                </td>

                                <td style='background: <?php echo $item->backgroundcolor ? $item->backgroundcolor : 'transparent'; ?>'></td>
                                <td><?php echo $item->dayname; ?></td>
                                <td><?=$this->display_starttime($item, $options);?></td>
                                <td class="ws-action-table">
                                    <a href='?page=weekly-schedule&amp;deleteitem=<?php echo $item->id; ?>&amp;schedule=<?php echo $schedule; ?>'
                                        <?php echo "onclick=\"if ( confirm('" . esc_js( sprintf( __( "You are about to delete the item '%s'\n  'Cancel' to stop, 'OK' to delete." ), $item->name ) ) . "') ) { return true;}return false;\""; ?>><span class="fa fa-trash-o"></span></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        </tbody>
                    </table>
                <?php else: ?>
                    <p><?=__("No items to display",'ws');?></p>
                <?php endif; ?>
                <form name="wsitemsform" action="" method="post" id="ws-config">
                <?php if ( function_exists( 'wp_nonce_field' ) ) wp_nonce_field( 'wspp-config' ); ?>
                    <fieldset>
                        <legend><strong><?=$title; ?></strong></legend>
                        <input type="hidden" name="id" value="<?=$id; ?>" />
                        <input type="hidden" name="oldrow" value="<?=$oldrow; ?>" />
                        <input type="hidden" name="oldday" value="<?=$oldday; ?>" />
                        <input type="hidden" name="schedule" value="<?php echo $schedule; ?>" />
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
                    </fieldset>
                </form>
            </div>
            <div class="ws-tab-content ws-days">
                <h3><?=__('Manage Days Labels','ws'); ?></h3>
                <form name="wsdaysform" action="" method="post" id="ws-config">
                    <?php
                    if ( function_exists( 'wp_nonce_field' ) ) wp_nonce_field( 'wspp-config' );
                  //$days = $this->get_days($schedule);
                    $days = $wpdb->get_results( "SELECT * from " . ws_db_prefix() . "wsdays WHERE scheduleid = " . $schedule . " ORDER by id" );
                    if ( $days ): ?>
                        <input type="hidden" name="schedule" value="<?=$schedule;?>" />
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
        <div class="ws-module-settings-content hidden hide">
            <h3 class="ws-modal-header">Security Check</h3>
            <div class="ws-module-messages-container"></div>
            <div class="ws-module-settings-content-main">
                <div class="ws-settings-module-description"></div>
                <div class="ws-settings-module-settings">
                    <div>
                        <p>When the button below is clicked the following modules will be enabled and configured:</p>
                        <ul class="ws-list">
                            <li>
                                <p>Utilisateurs bannis</p>
                            </li>
                            <li>
                                <p>Sauvegarde de la base de données</p>
                            </li>
                            <li>
                                <p>Local Brute Force Protection</p>
                            </li>
                            <li>
                                <p>Network Brute Force Protection</p>
                            </li>
                            <li>
                                <p>Mots de passe forts</p>
                            </li>
                            <li>
                                <p>Modifications WordPress</p>
                            </li>
                        </ul>
                    </div>
                    <p><input value="Secure Site" class="button-primary" name="" id="" type="button"> </p>
                </div>
            </div>
        </div>
    </div>
    <div class="ws-modal-content-footer">
        <button  title="<?=__('Valid','ws');?>" class="button button-primary align-left ws-close-modal"><?=__('Valid','ws');?></button>
        <button  title="<?=__('Cancel','ws');?>" class="button button-secondary align-right ws-module-settings-cancel"><?=__('Cancel','ws');?></button>
    </div>
</div>