<?php

/**
 * Add custom meta tag 'cherry-services-alternate-url'
 */
add_filter( 'cherry_services_list_meta_options_args', '#theme#_services_list_meta_options_args' );
function #theme#_services_list_meta_options_args( $args ){

	$args['fields']['cherry-services-alternate-url'] = array(
		'type'        => 'text',
		'element'     => 'control',
		'parent'      => 'general',
		'placeholder' => esc_html__( 'URL', '#theme#' ),
		'label'       => esc_html__( 'Alternate URL', '#theme#' ),
	);

	return $args;
}

/**
 * A Adapter class for macross collbacks override
 */
class #Theme#_Services_List_Template_Callbacks_Adapter {
	/**
	 * Shortcode attributes array
	 * @var array
	 */
	public $instance = null;

	/**
	 * Constructor for the class
	 *
	 * @since 1.0.0
	 * @param array $atts input attributes array.
	 */
	function __construct( $instance ) {
		$this->instance = $instance;
	}

	/**
	 * Get service title
	 *
	 * @since  1.0.0
	 * @param  array $args Arguments array.
	 * @return string
	 */
	public function get_title( $args = array() ) {

		global $post;

		if ( isset( $this->instance->atts['show_title'] ) && false === $this->instance->atts['show_title'] ) {
			return;
		}

		$args = wp_parse_args( $args, array(
			'wrap'  => 'div',
			'class' => '',
			'link'  => false,
			'base'  => 'title_wrap',
		) );

		$result       = $this->instance->post_title();
		$args['link'] = filter_var( $args['link'], FILTER_VALIDATE_BOOLEAN );

		$link = $this->instance->post_permalink();

		if( ( $alternate_url = get_post_meta( $post->ID, 'cherry-services-alternate-url', true ) )
			&& ! empty( $alternate_url ) ){
			$link = esc_url( $alternate_url );
		}

		if ( true === $args['link'] ) {
			$result = '<a href="' . $link . '">' . $result . '</a>';
		}

		return $this->instance->macros_wrap( $args, $result );
	}

	/**
	 * Get post thumbnail
	 *
	 * @since  1.0.0
	 * @param  array $args Arguments array.
	 * @return string
	 */
	public function get_image( $args = array() ) {

		if ( isset( $this->instance->atts['show_media'] ) && false === $this->instance->atts['show_media'] ) {
			return;
		}

		global $post;

		$args = wp_parse_args( $args, array(
			'wrap'  => 'div',
			'class' => '',
			'base'  => 'image_wrap',
			'size'  => ! empty( $this->instance->atts['size'] ) ? esc_attr( $this->instance->atts['size'] ) : 'thumbnail',
			'link'  => true,
		) );

		$photo = $this->instance->post_image( $args['size'] );

		if ( ! $photo ) {
			return;
		}

		$args['link']             = filter_var( $args['link'], FILTER_VALIDATE_BOOLEAN );
		$this->instance->atts['show_media'] = filter_var( $this->instance->atts['show_media'], FILTER_VALIDATE_BOOLEAN );

		$link = $this->instance->post_permalink();

		if( ( $alternate_url = get_post_meta( $post->ID, 'cherry-services-alternate-url', true ) )
			&& ! empty( $alternate_url ) ){
			$link = esc_url( $alternate_url );
		}

		if ( true === $args['link'] ) {
			$format = '<a href="%2$s">%1$s</a>';
		} else {
			$format = '%1$s';
			$link   = false;
		}

		if ( true === $this->instance->atts['show_media'] ) {
			return $this->instance->macros_wrap( $args, sprintf( $format, $photo, $link ) );
		}
	}

	/**
	 * Get read more button
	 *
	 * @since  1.0.0
	 * @param  array $args Arguments array.
	 * @return string
	 */
	public function get_button( $args = array() ) {

		if ( ! isset( $this->instance->atts['show_item_more'] ) ) {
			$this->instance->atts['show_item_more'] = true;
		}

		$this->instance->atts['show_item_more'] = filter_var( $this->instance->atts['show_item_more'], FILTER_VALIDATE_BOOLEAN );

		if ( ! $this->instance->atts['show_item_more'] ) {
			return;
		}

		$args = wp_parse_args( $args, array(
			'class' => 'btn btn-primary',
			'label' => __( 'Read more', 'cherry-services' ),
		) );

		$label = ! empty( $this->instance->atts['item_more_text'] ) ? $this->instance->atts['item_more_text'] : $args['label'];

		$format = apply_filters(
			'cherry_services_button_format',
			'<a href="%1$s" class="%2$s">%3$s</a>'
		);

		$link = $this->instance->post_permalink();

		if( ( $alternate_url = get_post_meta( $post->ID, 'cherry-services-alternate-url', true ) )
			&& ! empty( $alternate_url ) ){
			$link = esc_url( $alternate_url );
		}

		return sprintf( $format, $link, $args['class'], wp_kses_post( $label ) );
	}
}

/**
 * Overwrite macross services callbacs
 */
add_filter( 'cherry_services_data_callbacks', '#theme#_services_data_callbacks', 10, 2 );
function #theme#_services_data_callbacks( $data, $atts=array() ){

	if( ! ( $callbacks = &cherry_services_templater()->callbacks ) ){
		return $data;
	}

	$handler = new #Theme#_Services_List_Template_Callbacks_Adapter( $callbacks );

	$data['title']  = array( $handler, 'get_title' );
	$data['image']  = array( $handler, 'get_image' );
	$data['button'] = array( $handler, 'get_button' );

	return $data;
}
