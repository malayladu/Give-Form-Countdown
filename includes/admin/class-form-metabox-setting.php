<?php

/**
 * Class Give_Form_Countdown_Metabox_Settings
 *
 * @since 1.0
 */
class Give_Form_Countdown_Metabox_Settings {

	/**
	 * Instance.
	 *
	 * @since  1.0
	 * @access static
	 * @var Give_Form_Countdown_Metabox_Settings
	 */
	private static $instance;

	/**
	 * Setting id.
	 *
	 * @since  1.0
	 * @access private
	 *
	 * @var string
	 */
	private $id = '';

	/**
	 * Setting label.
	 *
	 * @since  1.0
	 * @access private
	 * @var string
	 */
	private $label = '';


	/**
	 * Singleton pattern.
	 *
	 * @since  1.0
	 * @access private
	 * Give_Form_Countdown_Metabox_Settings constructor.
	 */
	private function __construct() {
	}


	/**
	 * Get single instance.
	 *
	 * @since  1.0
	 * @access public
	 * @return Give_Form_Countdown_Metabox_Settings
	 */
	public static function get_instance() {
		if ( null === static::$instance ) {
			static::$instance = new static();
		}

		return static::$instance;
	}


	/**
	 * Setup params.
	 *
	 * @since  1.0
	 * @access public
	 * @return Give_Form_Countdown_Metabox_Settings
	 */
	public function setup_params() {
		$this->id    = 'give-form-countdown';
		$this->label = __( 'Form Countdown', 'give-form-countdown' );

		return static::get_instance();

	}

	/**
	 * Give_Form_Countdown_Metabox_Settings constructor.
	 *
	 * @since  1.0
	 * @access public
	 */
	public function setup_hooks() {
		// Add settings.
		add_filter( 'give_metabox_form_data_settings', array( $this, 'setup_setting' ), 999 );

		// Enqueue scripts.
		add_filter( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ), 999 );
		add_filter( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_scripts' ), 999 );

		// Validate setting.
		add_action( 'give_post_process_give_forms_meta', array( $this, 'validate_settings' ) );

		// Add setting to goal section.
		add_filter( 'give_donation_goal_options', array( $this, 'add_goal_section_settings' ), 999999 );
	}


	/**
	 * Plugin setting.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param $settings
	 *
	 * @return mixed
	 */
	public function setup_setting( $settings ) {

		// Setup settings.
		$new_settings = array(
			$this->id => array(
				'id'        => $this->id,
				'title'     => $this->label,
				'icon-html' => '<span class="dashicons dashicons-clock" style="font-size: 15px;text-align: left;line-height: 20px"></span>',
				'fields'    => array(
					// Close Form.
					array(
						'id'          => 'form-countdown-close-form',
						'name'        => __( 'Duration', 'give-form-countdown' ),
						'type'        => 'radio_inline',
						'default'     => 'disabled',
						'options'     => array(
							'enabled'  => __( 'Enabled', 'give-form-countdown' ),
							'disabled' => __( 'Disabled', 'give-form-countdown' ),
						),
						'description' => __( 'Enable a duration for this form and display a message when the duration ends.', 'give-form-countdown' ),
					),

					// Donation duration type.
					array(
						'id'          => 'form-countdown-by',
						'name'        => __( 'Timeframe', 'give-form-countdown' ),
						'type'        => 'radio_inline',
						'default'     => 'number_of_days',
						'options'     => array(
							'number_of_days'      => __( 'Number of days', 'give-form-countdown' ),
							'end_on_day_and_time' => __( 'Specific day & time', 'give-form-countdown' ),
						),
						'description' => __( 'Choose how the end of the duration is calculated.', 'give-form-countdown' ),
					),

					// Days.
					array(
						'id'          => 'form-countdown-in-number-of-days',
						'name'        => __( 'Number of Days', 'give-form-countdown' ),
						'type'        => 'number',
						'min'         => '10',
                        'default'     => '30',
						'description' => __( 'Set the number of days from the date of publication that the duration should last.', 'give-form-countdown' ),
                        'callback'    => array($this, 'gfc_number_field'),
					),

					// Date
					array(
						'id'          => 'form-countdown-on-date',
						'name'        => __( 'End Date', 'give-form-countdown' ),
						'type'        => 'text-medium',
						'description' => __( 'Set the date when the duration ends.', 'give-form-countdown' ),
					),

					// Time
					array(
						'id'          => 'form-countdown-on-time',
						'name'        => __( 'End Time', 'give-form-countdown' ),
						'type'        => 'select',
						'options'     => gfc_get_time_list(),
                        'default'     => '1800',
						'description' => __( 'Set the time of day when the duration ends.', 'give-form-countdown' ),
					),

                    // Countdown clock.
                    array(
                        'id'          => 'form-countdown-countdown-clock',
                        'name'        => __( 'Countdown Clock', 'give-form-countdown' ),
                        'type'        => 'radio_inline',
                        'default'     => 'disabled',
                        'options'     => array(
                            'enabled'  => __( 'Enabled', 'give-form-countdown' ),
                            'disabled' => __( 'Disabled', 'give-form-countdown' ),
                        ),
                        'description' => __( 'Enable to display a countdown clock in your form.', 'give-form-countdown' ),
                    ),

					// Duration achieved message.
					array(
						'id'          => 'form-countdown-message',
						'name'        => __( 'End Message', 'give-form-countdown' ),
						'type'        => 'wysiwyg',
						'attributes'  => array(
							'placeholder' => __( 'Thank you to all our donors, this campaign has ended.', 'give-form-countdown' ),
						),
						'description' => __( 'Enter content that appears in your form when the duration ends.', 'give-form-countdown' ),
					),

					// Duration achieved message position.
					array(
						'id'          => 'form-countdown-message-achieved-position',
						'name'        => __( 'End Message Position', 'give-form-countdown' ),
						'type'        => 'radio',
						'default'     => 'close_form',
						'options'     => array(
							'close_form' => __( 'Close the form and replace its content', 'give-form-countdown' ),
							'above_form' => __( 'Keep the form open and show this message above the form', 'give-form-countdown' ),
							'below_form' => __( 'Keep the form open and show this message below the form', 'give-form-countdown' ),
						),
						'description' => __( 'Choose the position you want the "End Message" to appear in when the duration ends.', 'give-form-countdown' ),
					),
				),
			),
		);

		return array_merge( $settings, $new_settings );
	}

	/**
	 * Add settings to goal setting section.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param array $settings
	 *
	 * @return array
	 */
	public function add_goal_section_settings( $settings ) {
		if ( ! empty( $settings['fields'] ) ) {
			$goal_achieved_message_setting_index = null;
			foreach ( $settings['fields'] as $index => $field ) {
				if ( ! isset( $field['id'] ) ) {
					continue;
				}

				if ( '_give_form_goal_achieved_message' === $field['id'] ) {
					$goal_achieved_message_setting_index = $index;
				}
			}

			if ( ! is_null( $goal_achieved_message_setting_index ) ) {
				$gdc_setting = array(
					array(
						'id'          => 'form-countdown-use-end-message',
						'name'        => __( 'Use Donation End Message', 'give-form-countdown' ),
						'type'        => 'radio_inline',
						'default'     => 'disabled',
						'options'     => array(
							'enabled'  => __( 'Enabled', 'give-form-countdown' ),
							'disabled' => __( 'Disabled', 'give-form-countdown' ),
						),
						'description' => __( 'When goal is achieved, do you want to close the form and show the Form Countdown message', 'give-form-countdown' ),
					),
				);

				$settings['fields'] = array_merge(
					array_slice( $settings['fields'], 0, $goal_achieved_message_setting_index ),
					$gdc_setting,
					array_slice( $settings['fields'], $goal_achieved_message_setting_index )
				);

				$settings['fields'] = array_values( $settings['fields'] );
			}
		}

		return $settings;
	}


	/**
	 * Load admin scripts.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param string $hook
	 */
	function enqueue_admin_scripts( $hook ) {
		// Bailout.
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ) ) ) {
			return;
		}

		global $post;

		// Bailout.
		if ( 'give_forms' !== $post->post_type ) {
			return;
		}

		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_script( 'form-countdown-admin-script', GFC_PLUGIN_URL . 'assets/js/admin/admin-script.js', array( 'jquery' ), GFC_PLUGIN_VERSION );

		$gdc_vars = array(
			'duration_ended_message' => array(
				'warning' => __( 'You are currently using \' Donation Goal\' message when form closes. Change that to set your custom message here.', 'give-form-countdown' ),
			),
		);

		wp_localize_script( 'form-countdown-admin-script', 'gdc_vars', $gdc_vars );
	}

	/**
	 * Load scripts.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param string $hook
	 */
	function enqueue_frontend_scripts( $hook ) {
		wp_register_script( 'form-countdown-jquery-countdown-script', GFC_PLUGIN_URL . 'assets/js/plugin/jquery.countdown.js', array( 'jquery' ), GFC_PLUGIN_VERSION );
		wp_register_script( 'form-countdown-underscore-script', GFC_PLUGIN_URL . 'assets/js/plugin/underscore-min.js', array( 'jquery' ), GFC_PLUGIN_VERSION );
		wp_register_style( 'form-countdown-jquery-countdown-layout-1-style', GFC_PLUGIN_URL . 'assets/css/plugin/jquery.countdown-layout-1.css', array(), mt_rand(10, 1000) );
	}


	/**
	 * Validate setting.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param $form_id
	 */
	public function validate_settings( $form_id ) {
		if ( ! gfc_get_form_close_date( $form_id ) ) {
			update_post_meta( $form_id, 'form-countdown-close-form', 'disabled' );
		}
	}

    public function gfc_number_field($field) {

        global $thepostid;
        $field['value'] = give_get_field_value( $field, $thepostid );

        $number_default = empty(esc_attr($field['value'])) ? '10' : esc_attr($field['value']);
        $number_min = empty(esc_attr($field['min'])) ? '1' : esc_attr($field['min']);

        ?>
        <p class="give-field-wrap <?php echo esc_attr($field['id']); ?>_field <?php echo esc_attr($field['wrapper_class']); ?>">
            <label for="<?php echo give_get_field_name($field); ?>"><?php echo wp_kses_post($field['name']); ?></label>
            <?php echo $field['before_field']; ?>
            <input
                type="<?php echo esc_attr($field['type']); ?>"
                style="<?php echo esc_attr($field['style']); ?>"
                name="<?php echo give_get_field_name($field); ?>"
                id="<?php echo esc_attr($field['id']); ?>"
                min="<?php echo $number_min; ?>"
                value="<?php echo $number_default; ?>"
                <?php echo give_get_custom_attributes($field); ?>
            />

            <?php echo $field['after_field']; ?>
            <?php
            echo give_get_field_description($field);
            echo '</p>';
    }
}


// initialize.
Give_Form_Countdown_Metabox_Settings::get_instance()->setup_params()->setup_hooks();
