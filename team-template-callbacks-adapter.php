<?php
/**
 * Add custom meta tag 'cherry-team-alternate-url'
 */
add_filter( 'cherry_team_members_meta_args', '#tehme#_team_members_meta_args' );
function #tehme#_team_members_meta_args( $args ){

	$args['fields']['cherry-team-alternate-url'] = array(
		'type'        => 'text',
		'placeholder' => esc_html__( 'URL', '#tehme#' ),
		'label'       => esc_html__( 'Alternate URL', '#tehme#' ),
	);

	return $args;
}

/**
 * A Adapter class for macross collbacks override
 */
class #tehme#_Team_Members_Template_Callbacks {
	/**
	 * Shortcode attributes array
	 * @var array
	 */
	public $instance = null;

	/**
	 * Constructor for the class
	 *
	 * @since 1.0.0
	 * @param array $instance Cherry_Team_Members_Template_Callbacks instance.
	 */
	function __construct( $instance ) {
		$this->instance = $instance;
	}

	/**
	 * Returns link to post or alternate link if specified
	 * @return String an URL
	 */
	public function get_link(){
		global $post;

		$link = $this->instance->post_permalink();

		if( ( $alternate_url = get_post_meta( $post->ID, 'cherry-team-alternate-url', true ) )
			&& ! empty( $alternate_url ) ){
			$link = esc_url( $alternate_url );
		}

		return $link;
	}

	/**
	 * Get post thumbnail
	 *
	 * @since  1.0.0
	 * @param  array $args Arguments array.
	 * @return string
	 */
	public function get_photo( $args = array() ) {

		if ( isset( $this->instance->atts['show_photo'] ) && false === $this->instance->atts['show_photo'] ) {
			return;
		}

		global $post;

		$args = wp_parse_args( $args, array(
			'wrap'  => 'div',
			'class' => '',
			'size'  => ! empty( $this->instance->atts['size'] ) ? esc_attr( $this->instance->atts['size'] ) : 'thumbnail',
			'link'  => true,
		) );

		$photo = $this->instance->post_image();

		if ( ! $photo ) {
			return;
		}

		$args['link'] = filter_var( $args['link'], FILTER_VALIDATE_BOOLEAN );

		if ( true === $args['link'] ) {
			$format = '<a href="%2$s">%1$s</a>';
			$link   = $this->get_link();
		} else {
			$format = '%1$s';
			$link   = false;
		}

		if ( true === $this->instance->atts['show_photo'] || 'yes' === $this->instance->atts['show_photo'] ) {
			return $this->instance->macros_wrap( $args, sprintf( $format, $photo, $link ) );
		}
	}

	/**
	 * Get team memeber name (post title)
	 *
	 * @since  1.0.0
	 * @param  array $args Arguments array.
	 * @return string
	 */
	public function get_name( $args = array() ) {

		global $post;

		if ( isset( $this->instance->atts['show_name'] ) && false === $this->instance->atts['show_name'] ) {
			return;
		}

		$args = wp_parse_args( $args, array(
			'wrap'  => 'div',
			'class' => '',
			'link'  => false
		) );

		$result       = $this->instance->post_title();
		$args['link'] = filter_var( $args['link'], FILTER_VALIDATE_BOOLEAN );

		if ( true === $args['link'] ) {
			$result = '<a href="' . $this->get_link() . '">' . $result . '</a>';
		}

		return $this->instance->macros_wrap( $args, $result );
	}

	/**
	 * Button macros
	 *
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
			'label' => esc_html__( 'Read more', 'cherry-team' ),
		) );

		$label = ! empty( $this->instance->atts['item_more_text'] ) ? $this->instance->atts['item_more_text'] : $args['label'];

		$format = apply_filters(
			'cherry_team_button_format',
			'<a href="%1$s" class="%2$s">%3$s</a>'
		);

		return sprintf( $format, $this->get_link(), $args['class'], wp_kses_post( $label ) );
	}
}

/**
 * Overwrite macross services callbacs
 */
add_filter( 'cherry_team_data_callbacks', '#tehme#_team_data_callbacks', 10, 2 );
function #tehme#_team_data_callbacks( $data, $atts=array() ){

	if( ! ( $callbacks = &cherry_team_members_templater()->callbacks ) ){
		return $data;
	}

	$handler = new #tehme#_Team_Members_Template_Callbacks( $callbacks );

	$data['name']  = array( $handler, 'get_name' );
	$data['photo']  = array( $handler, 'get_photo' );
	$data['button'] = array( $handler, 'get_button' );

	return $data;
}
