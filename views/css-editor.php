<div class="wrap">
    <h2><?=__('Weekly Schedule Stylesheet Editor','ws');?></h2>
    <a href="http://yannickcorner.nayanna.biz/wordpress-plugins/weekly-schedule/" target="weeklyschedule"><img src="<?php echo plugins_url( '../icons/btn_donate_LG.gif', __FILE__ ); ?>" /></a> |
    <a target='wsinstructions' href='http://wordpress.org/extend/plugins/weekly-schedule/installation/'>Installation Instructions</a> |
    <a href='http://wordpress.org/extend/plugins/weekly-schedule/faq/' target='llfaq'>FAQ</a> |
    <a href='http://yannickcorner.nayanna.biz/contact-me'>Contact the Author</a><br />

    <p>If the stylesheet editor is empty after upgrading, reset to the default stylesheet using the button below or copy/paste your backup stylesheet into the editor.</p>

    <form name='wsadmingenform' action="<?php echo add_query_arg( 'page', 'weekly-schedule-stylesheet', admin_url( 'admin.php' ) ); ?>" method="post" id="ws-conf">
        <?php
        if ( function_exists( 'wp_nonce_field' ) ) {
            wp_nonce_field( 'wspp-config' );
        }
        ?>
        <textarea name='fullstylesheet' id='fullstylesheet' style='font-family:Courier' rows="30" cols="100"><?php echo stripslashes( $genoptions['fullstylesheet'] ); ?></textarea>

        <div>
            <input class="button button-primary" type="submit" name="submitstyle" value="<?php _e( 'Submit', 'weekly-schedule' ); ?>" /><span style='padding-left: 650px'><input class="button button-primary" type="submit" name="resetstyle" value="<?php _e( 'Reset to default', 'weekly-schedule' ); ?>" /></span>
        </div>
    </form>
</div>