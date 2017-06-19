<?php
class WSTodayScheduleWidget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		parent::__construct(
			'weekly_schedule_widget', // Base ID
			'Weekly Schedule Widget', // Name
			array( 'description' => 'Displays a list of schedule items' ) // Args
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		global $wp_locale;
		$before_widget = '';
		$before_title  = '';
		$after_title   = '';
		$after_widget  = '';

		extract( $args );

		$title       = apply_filters( 'widget_title', $instance['title'] );
		$max_items   = ( !empty( $instance['max_items'] ) ? $instance['max_items'] : 5 );
		$schedule_id = ( !empty( $instance['schedule_id'] ) ? $instance['schedule_id'] : 1 );
		$empty_msg   = ( !empty( $instance['empty_msg'] ) ? $instance['empty_msg'] : 'No Items Found' );
		$only_next_items   = ( !empty( $instance['only_next_items'] ) ? $instance['only_next_items'] : false );

		$schedulename = 'WS_PP' . $schedule_id;
		$options      = get_option( $schedulename );

		$today = date( 'w', current_time( 'timestamp', 0 ) ) + 1;
		$system_hour = date( 'H', current_time( 'timestamp', 0 ) );
		$system_minute = date( 'i', current_time( 'timestamp', 0 ) ) / 60;
		$time_now = $system_hour + $system_minute;

		echo $before_widget;
		if ( !empty( $title ) ) {
			echo $before_title . $title . $after_title;
		}

		//fetch results
		global $wpdb;

		$schedule_query = 'SELECT * from ' . ws_db_prefix() .
			'wsitems WHERE day = ' . $today .
			' AND scheduleid = ' . $schedule_id . ' ORDER by starttime ASC';

		$schedule_items = $wpdb->get_results( $schedule_query );

		$itemcount = 0;

		if ( !empty( $schedule_items ) ) {
			echo '<ul>';

			foreach ( $schedule_items as $schedule_item ) {
				if ( $only_next_items && ! ( $schedule_item->starttime < $time_now && $time_now < ( $schedule_item->starttime + $schedule_item->duration ) ) && ! ( $schedule_item->starttime > $time_now ) ) {
					continue;
				}

				$itemcount++;

				if ( $itemcount > $max_items ) {
					break;
				}

				$item_name  = stripslashes( $schedule_item->name );
				$start_hour = $schedule_item->starttime;

				if ( fmod( $schedule_item->starttime, 1 ) == 0.25 ) {
					$minutes = '15';
				} elseif ( fmod( $schedule_item->starttime, 1 ) == 0.50 ) {
					$minutes = '30';
				} elseif ( fmod( $schedule_item->starttime, 1 ) == 0.75 ) {
					$minutes = '45';
				} else {
					$minutes = '';
				}

				if ( $options['timeformat'] == '24hours' || empty( $options['timeformat'] ) ) {
					$start_hour = floor( $schedule_item->starttime ) . "h" . $minutes;
				} else if ( $options['timeformat'] == '24hourscolon' ) {
					$start_hour = floor( $schedule_item->starttime ) . ":" . ( empty( $minutes ) ? "00" : $minutes );
				} else if ( $options['timeformat'] == '12hours' ) {
					if ( $schedule_item->starttime < 12 ) {
						$timeperiod = 'am';
						if ( $schedule_item->starttime == 0 ) {
							$hour = 12;
						} else {
							$hour = floor( $schedule_item->starttime );
						}
					} else {
						$timeperiod = 'pm';
						if ( $schedule_item->starttime >= 12 && $schedule_item->starttime < 13 ) {
							$hour = floor( $schedule_item->starttime );
						} else {
							$hour = floor( $schedule_item->starttime ) - 12;
						}
					}

					$start_hour = $hour;
					if ( !empty( $minutes ) ) {
						$start_hour .= ":" . $minutes;
					}
					$start_hour .= $timeperiod;
				}

				echo '<li';

				if ( $schedule_item->starttime < $time_now && $time_now < ( $schedule_item->starttime + $schedule_item->duration ) && $today == $schedule_item->day ) {
					echo ' class="now-playing"';
				}

				echo '>';
				if ( !empty( $schedule_item->address ) ) {
					echo '<a href="' . $schedule_item->address . '">';
				}
				echo $start_hour . ' - ' . $item_name;

				if ( !empty( $schedule_item->address ) ) {
					echo '</a>';
				}
				echo '</li>';
			}

			echo '</ul>';
		} else {
			echo $empty_msg;
		}

		echo $after_widget;
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance              = array();
		$instance['title']     = strip_tags( $new_instance['title'] );
		$instance['max_items'] = strip_tags( $new_instance['max_items'] );

		if ( is_numeric( $new_instance['schedule_id'] ) ) {
			$instance['schedule_id'] = intval( $new_instance['schedule_id'] );
		} else {
			$instance['schedule_id'] = $instance['schedule_id'];
		}

		if ( isset( $new_instance['only_next_items'] ) ) {
			$instance['only_next_items'] = true;
		} else {
			$instance['only_next_items'] = false;
		}

		$instance['empty_msg'] = strip_tags( $new_instance['empty_msg'] );

		return $instance;
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		/* Set initial values/defaults */
		$title       = ( !empty( $instance['title'] ) ? $instance['title'] : "Today's Scheduled Items" );
		$max_items   = ( !empty( $instance['max_items'] ) ? $instance['max_items'] : 5 );
		$schedule_id = ( !empty( $instance['schedule_id'] ) ? $instance['schedule_id'] : 1 );
		$empty_msg   = ( !empty( $instance['empty_msg'] ) ? $instance['empty_msg'] : 'No Items Found' );
		$only_next_items   = ( !empty( $instance['only_next_items'] ) ? $instance['only_next_items'] : false );

		$genoptions = get_option( 'WeeklyScheduleGeneral' );
		?>

		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>">Title:</label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'empty_msg' ); ?>">Empty Item List Message:</label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'empty_msg' ); ?>" name="<?php echo $this->get_field_name( 'empty_msg' ); ?>" type="text" value="<?php echo esc_attr( $empty_msg ); ?>" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'max_items' ); ?>">Max Number of Items:</label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'max_items' ); ?>" name="<?php echo $this->get_field_name( 'max_items' ); ?>" type="text" value="<?php echo esc_attr( $max_items ); ?>" />
			<span class='description'><?php __( 'Maximum number of items to display' ); ?></span>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'max_items' ); ?>">Only show current and later items</label>
			<input type="checkbox" id="<?php echo $this->get_field_id( 'only_next_items' ); ?>" name="<?php echo $this->get_field_name( 'only_next_items' ); ?>" <?php checked( $only_next_items ); ?> />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'schedule_id' ); ?>">Schedule ID</label>

			<SELECT class="widefat" id="<?php echo $this->get_field_id( 'schedule_id' ); ?>" name="<?php echo $this->get_field_name( 'schedule_id' ); ?>">
				<?php if ( empty( $genoptions['numberschedules'] ) ) {
					$number_of_schedules = 2;
				} else {
					$number_of_schedules = $genoptions['numberschedules'];
				}
				for ( $counter = 1; $counter <= $number_of_schedules; $counter ++ ): ?>
					<?php $tempoptionname = "WS_PP" . $counter;
					$tempoptions          = get_option( $tempoptionname ); ?>
					<option value="<?php echo $counter ?>" <?php selected( $schedule_id, $counter ); ?>>Schedule <?php echo $counter ?><?php if ( $tempoptions != "" ) {
							echo " (" . $tempoptions['schedulename'] . ")";
						} ?></option>
				<?php endfor; ?>
			</SELECT>
		</p>

	<?php
	}

}
?>