<div class="wrap nosubsub ws-admin-page">
    <h1><?=__('Weekly Schedule Configuration','ws');?></h1>
    <div id="col-container" class="wp-clearfix">
        <div id="col-left">
            <div class="col-wrap">
                <div class="form-wrap">
                    <form name='wsadmingenform' action="<?php echo add_query_arg( 'page', 'weekly-schedule', admin_url( 'options-general.php' ) ); ?>" method="post" id="ws-conf" enctype="multipart/form-data">
                        <h2><?=__('General Settings','ws');?></h2>
                        <input type="hidden" name="MAX_FILE_SIZE" value="1000000" />
                        <?php
                        if ( function_exists( 'wp_nonce_field' ) ) {
                            wp_nonce_field( 'wspp-config' );
                        }
                        ?>
                        <div class="form-field">
                            <label for="schedulefile"><?=__('Import Schedule Items','ws');?> (<a href="<?php echo plugins_url( 'importtemplate.csv', __FILE__ ); ?>"><?=__('Template','ws');?></a>)</label>
                            <input style="display: inline" size="80" name="schedulefile" type="file" />
                            <input style="display: inline" class="button" type="submit" name="importschedule" value="<?=__('Import Items','ws');?>" />  
                        </div>
                        <div class="form-field">
                            <label for="csvdelimiter"><?=__('Import File Delimiter','ws');?></label>
                            <input type="text" id="csvdelimiter" name="csvdelimiter" size="1" value="<?php if ( !isset( $genoptions['csvdelimiter'] ) ) $genoptions['csvdelimiter'] = ','; echo $genoptions['csvdelimiter']; ?>" />
                        </div>
                        <hr/>
                        <div class="form-field">
                            <label for="stylesheet"><?=__('Stylesheet File Name','ws');?></label>
                            <input type="text" id="stylesheet" name="stylesheet" size="40" value="<?php echo $genoptions['stylesheet']; ?>" />
                        </div>
                        <?php if (current_user_can( 'manage_options' )) : ?>
                        <div class="form-field">
                            <?=__('Access level required', 'ws');?></td>
                            <select id="accesslevel" name="accesslevel">
                                <?php $levels = array( 'admin' => 'Administrator', 'editor' => 'Editor', 'author' => 'Author', 'contributor' => 'Contributor', 'subscriber' => 'Subscriber' );
                                if ( !isset( $genoptions['accesslevel'] ) || empty( $genoptions['accesslevel'] ) ) {
                                    $genoptions['accesslevel'] = 'admin';
                                }

                                foreach ( $levels as $key => $level ) {
                                    echo '<option value="' . $key . '" ' . selected( $genoptions['accesslevel'], $key, false ) . '>' . $level . '</option>';
                                } ?>
                            </select>
                        </div>
                        <?php endif; ?>
                        <div class="form-field">
                            <?=__('Number of Schedules','ws');?>
                            <input type="text" id="numberschedules" name="numberschedules" size="5" value="<?php if ( $genoptions['numberschedules'] == '' ) {
                                                    echo '2';
                                                }
                                                echo $genoptions['numberschedules']; ?>" />
                        </div>
                        <div class="form-field">
                            <?=__('Debug Mode','ws');?></td>
                            <input type="checkbox" id="debugmode" name="debugmode" <?php if ( $genoptions['debugmode'] ) {
                                                    echo ' checked="checked" ';
                                                } ?>/>
                        </div>
                        <div class="form-field">
                            <label><?=__('Additional pages to style (Comma-Separated List of Page IDs)','ws');?></label>
                            <input type='text' name='includestylescript' value='<?php echo $genoptions['includestylescript']; ?>' />
                            <p><?=__('Additional pages to style (Comma-Separated List of Page IDs)','ws');?></p>
                        </div>
                        <p class="submit">
                        <input class="button button-primary" type="submit" name="submitgen" value="<?=__('Update General Settings &raquo;','ws');?>" />
                        </p>
                    </form>
                </div>
            </div>
        </div><!-- /col-left -->
        <div id="col-right">
            <div class="col-wrap">
                <form id="posts-filter" method="post" data-selected="<?=$schedule;?>"></form>
                <div class="tablenav top">

                    <div class="alignleft actions bulkactions hidden">
                        <label for="bulk-action-selector-top" class="screen-reader-text">Sélectionnez l’action groupée</label>
                        <select name="action" id="bulk-action-selector-top">
                            <option value="-1">Actions groupées</option>
                            <option value="delete">Supprimer</option>
                        </select>
                        <input id="doaction" class="button action" value="Appliquer" type="submit">
                    </div>
                    <div class="tablenav-pages one-page hidden"><span class="displaying-num"><?=$genoptions['numberschedules'];?> élément</span>
                        <span class="pagination-links"><span class="tablenav-pages-navspan" aria-hidden="true">«</span>
                        <span class="tablenav-pages-navspan" aria-hidden="true">‹</span>
                        <span class="paging-input"><label for="current-page-selector" class="screen-reader-text">Page actuelle</label><input class="current-page" id="current-page-selector" name="paged" value="1" size="1" aria-describedby="table-paging" type="text"><span class="tablenav-paging-text"> sur <span class="total-pages">1</span></span></span>
                        <span class="tablenav-pages-navspan" aria-hidden="true">›</span>
                        <span class="tablenav-pages-navspan" aria-hidden="true">»</span>
                        </span>
                    </div>
                    <br class="clear">
                </div>
                <table class='widefat striped'>
                    <thead>
                        <tr>
                            <th class="tooltip"><?=__('ID','ws');?></th>
                            <th class="tooltip"><?=__('Schedule Name','ws');?></th>
                            <th class="tooltip"><?=__('Code to insert on a Wordpress page to see Weekly Schedule','ws');?></th>
                            <th style="width:112px"></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php 
                    $numberofschedules = $genoptions['numberschedules'];
                    for( $counter = 1; $counter <= $numberofschedules; $counter ++ ) {
                        $tempoptionname = "WS_PP" . $counter;
                        $tempoptions    = get_option( $tempoptionname );
                        $schedulename   = $tempoptions['schedulename'];
                        $selected       = ($schedule == $counter) ? "selected" : "";
                        $url            = ($schedule == $counter) ? "#" : "?page=weekly-schedule&settings=$adminpage&schedule=$counter";
                        $openmodal      = ($schedule == $counter) ? "ws-open-modal" : "";
                        echo(
                        "<tr class='$selected' >
                            <td style=''>$counter</td>
                            <td style=''>$schedulename</td>
                            <td style=''>[weekly-schedule schedule=\"$counter\"]</td>
                            <td style='text-align:right'>
                                <a class='button ".$openmodal."' href='" . $url . "' title='" . __('modify','ws') . "'><span class='fa fa-pencil'/></a>
                                <a class='button' href='#' title='".__('copy','ws')."'><span class='fa fa-files-o'/></a>
                                <a class='button' href='#' title='".__('delete','ws')."'><span class='fa fa-trash-o'/></a>
                            </td>
                        </tr>"
                        );
                    }
                    ?>
                    </tbody>
                </table>
            </div>
        </div><!-- /col-right -->
    </div>
</div>