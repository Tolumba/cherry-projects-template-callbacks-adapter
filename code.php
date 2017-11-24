<?php 

add_filter( 'cherry_projects_data_callbacks', 'theme_projects_data_callbacks', 10, 2 );

function theme_projects_data_callbacks( $data, $atts ){
	if( isset($data['termpermalink'])
		&& is_array($data['termpermalink'])
		&& 'Cherry_Projects_Template_Callbacks' === get_class( $data['termpermalink'][0] ) ){

		$adapter = ProjectsCallbacksAdapter::get_instance();
		@$adapter->callbacks =& $data['termpermalink'][0];

		$data['termname'] = array( $adapter, 'render_term_name' );
		$data['termexternalurl'] = array( $adapter, 'render_external_url' );
	}

	return $data;
}

class ProjectsCallbacksAdapter{

	private static $_instance;

	public $callbacks;
	
	public function render_external_url( $attr = array() ){

		$term_data = $this->callbacks->term_data;

		if( !$term_data ){
			return ;
		}

		$default_attr = array(
			'text_visible' => false
		);

		$attr = wp_parse_args( $attr, $default_attr );

		$redirect_URL = esc_url( get_term_meta( $term_data->term_id, 'cherry_terms_extra_url', true ) );

		if( !empty( $redirect_URL ) ){
			$permalink  = $redirect_URL;
		}else{
			$permalink = get_term_link( $term_data->term_id );
		}

		$permalink_text = apply_filters( 'cherry-projects-terms-permalink-text', esc_html__( 'More', 'cherry-projects' ) );

		$icon_content = ( filter_var( $attr['text_visible'], FILTER_VALIDATE_BOOLEAN ) ) ? '<span>' . $permalink_text . '</span>' : '<span class="dashicons"></span>';

		$html = sprintf( '<a class="term-permalink simple-icon" href="%1$s">%2$s</a>',
			$permalink,
			$icon_content
		);

		return $html;
	}

	public function render_term_name( $attr = array() ) {

		$default_attr = array( 'number_of_words' => 10 );
		$attr = wp_parse_args( $attr, $default_attr );

		$util = cherry_projects()->projects_data->cherry_utility->attributes;
		$term_data = $this->callbacks->term_data;

		$redirect_URL = esc_url( get_term_meta( $term_data->term_id, 'cherry_terms_extra_url', true ) );

		if( !empty( $redirect_URL ) ){
			$permalink  = $redirect_URL;
		}else{
			$permalink = get_term_link( $term_data->term_id );
		}

		$title = $util->cut_text( $term_data->name, $attr['number_of_words'], 'word', '&hellip;' );

		$html_class = '';

		return sprintf( '<h5 %1$s><a href="%2$s" %3$s>%4$s</a></h5>',
			$html_class,
			$permalink,
			$term_data->name,
			$title );
	}

	public static function get_instance() {

		if ( null == self::$_instance )
			self::$_instance = new self;

		return self::$_instance;
	}
}
