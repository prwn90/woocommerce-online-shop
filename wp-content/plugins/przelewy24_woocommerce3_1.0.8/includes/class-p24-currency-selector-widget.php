<?php

/**
 * Class for a widget
 */
class P24_Currency_Selector_Widget extends WP_Widget {

	/**
	 * The P24_Core instance.
	 *
	 * @var P24_Core
	 */
	private $plugin_core;

	/**
	 * The constructor.
	 *
	 * @param P24_Core $plugin_core The P24_Core instance.
	 */
	public function __construct( P24_Core $plugin_core ) {
		$this->plugin_core = $plugin_core;
		$id_base           = 'p24_currency_selector';
		$name              = __( 'Widget wyboru waluty Przelewy24' );
		parent::__construct( $id_base, $name );
	}

	/**
	 * Display the widget.
	 *
	 * @param array $args     Display arguments, ignored.
	 * @param array $instance Settings.
	 */
	public function widget( $args, $instance ) {
		if ( isset( $instance['title'] ) && $instance['title'] ) {
			$title = $instance['title'];
		} else {
			$title = $this->name;
		}
		$params = [
			'widget_title'     => $title,
			'active_currency'  => get_woocommerce_currency(),
			'currency_options' => get_przelewy24_multi_currency_options(),
		];
		$this->plugin_core->render_template( 'change-currency-widget', $params );
		do_action( 'przelewy24_multi_currency_change_form_rendered' );
	}

	/**
	 * Handles updating settings.
	 *
	 * @param array $new_instance New settings for this instance.
	 * @param array $old_instance Old settings for this instance.
	 * @return array Updated settings to save.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance          = $old_instance;
		$instance['title'] = sanitize_text_field( $new_instance['title'] );

		return $instance;
	}

	/**
	 * Outputs the settings form.
	 *
	 * @param array $instance Current settings.
	 */
	public function form( $instance ) {
		$instance         = wp_parse_args( (array) $instance, array( 'title' => '' ) );
		$title            = sanitize_text_field( $instance['title'] );
		$title_field_id   = $this->get_field_id( 'title' );
		$title_field_name = $this->get_field_name( 'title' );
		$params           = [
			'widget_title'     => $title,
			'title_field_id'   => $title_field_id,
			'title_field_name' => $title_field_name,
		];
		$this->plugin_core->render_template( 'config-change-currency-widget', $params );
	}
}
