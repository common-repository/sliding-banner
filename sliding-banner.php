<?php
/*
   Plugin Name: Sliding Banner - News and Offers
   Plugin URI: 
   Version: 1.0
   Author: josesd
   Author URI: https://josesd.com
   Description: Diferentes Banners con Slider
   Text Domain: sliding-banner-news-offers
   License: GPLv3
  */

defined( 'ABSPATH' ) or die( 'No puedes ver este contenido' );


$SBNO_minimalRequiredPHPVersion = '5.0';

function SBNO_NoticePHPVersionWrong() {
    global $SBNO_minimalRequiredPHPVersion;
    echo '<div class="updated fade">' .
      __('Error: plugin "Content Carousel" requires a newer version of PHP to be running.',  'carousel-de-contenido').
            '<br/>' . __('Minimal version of PHP required: ', 'carousel-de-contenido') . '<strong>' . $SBNO_minimalRequiredPHPVersion . '</strong>' .
            '<br/>' . __('Your server\'s PHP version: ', 'sliding-banner') . '<strong>' . phpversion() . '</strong>' .
         '</div>';
}


function SBNO_PHPVersionCheck() {
    global $SBNO_minimalRequiredPHPVersion;
    if (version_compare(phpversion(), $SBNO_minimalRequiredPHPVersion) < 0) {
        add_action('admin_notices', 'SBNO_NoticePHPVersionWrong');
        return false;
    }
    return true;
}

function SBNO_i18n_init() {
    $pluginDir = dirname(plugin_basename(__FILE__));
    load_plugin_textdomain('sliding-banner', false, $pluginDir . '/languages/');
}

add_action('plugins_loadedi','SBNO_i18n_init');

if (SBNO_PHPVersionCheck()) {

function SBNO_enqueue_scripts() {
wp_register_script('sb-script', plugins_url('/assets/js/sb-script.js', __FILE__), array('jquery'),'1.1', true);
wp_enqueue_script('sb-script');
}
  
add_action( 'wp_enqueue_scripts', 'SBNO_enqueue_scripts' );  

function SBNO_enqueue_styles() {
wp_register_style('sb-style', plugins_url('/assets/css/sb-style.css', __FILE__));
wp_enqueue_style('sb-style');
}
add_action( 'wp_enqueue_scripts', 'SBNO_enqueue_styles' ); 

add_action('init', 'SBNO_banner_register');
function SBNO_banner_register() {

    $labels = array(
        'name' => _x('Banners', 'post type general name'),
        'singular_name' => _x('Banner', 'post type singular name'),
        'add_new' => _x('Añadir nuevo', 'carousel item'),
        'add_new_item' => __('Añadir nuevo Banner'),
        'edit_item' => __('Editar Banner'),
        'new_item' => __('Nuevo Banner'),
        'view_item' => __('Ver Banner'),
        'search_items' => __('Buscar Banner'),
        'not_found' =>  __('Nada encontrado'),
        'not_found_in_trash' => __('Nada encontrado en la papelera'),
        'parent_item_colon' => ''
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
        'query_var' => true,
        'rewrite' => true,
        'capability_type' => 'post',
        'hierarchical' => false,
        'menu_position' => null,
        'supports' => array('title'),
        'rewrite' => array('slug' => 'banner', 'with_front' => FALSE),
      ); 

    register_post_type( 'banners' , $args );
}
	
  function SBNO_remove_revolution_slider_meta_boxes() {
		remove_meta_box( 'mymetabox_revslider_0', 'banners', 'normal' );
	}

add_action( 'do_meta_boxes', 'SBNO_remove_revolution_slider_meta_boxes' );


function SBNO_banner_metabox() {
  add_meta_box(
    'Banner',
    'BANNER INFO', // Titulo del Meta Box
    'SNBO_render_mb', // define funcion que imprime el contenido del Meta box
    'banners', // Custom Post al que esta asociada
    'advanced',
    'default'
  );
}
add_action( 'add_meta_boxes', 'SBNO_banner_metabox' );
/*
 * Imprime el contenido del Meta box
 */
function SNBO_render_mb( $post ) {
  wp_nonce_field( 'wpe_keywords_nonce_save', 'wpe_keywords_nonce' );
  $banner_info = get_post_meta( $post->ID, '_banner_info', true );
  $color_fondo = get_post_meta( $post->ID, '_color_fondo_banner', true );
  $html = '<table width="100%">
    <tr>
        <td colspan="2">
            <textarea name="banner_info" style="width: 100%; height: 100px;">'.esc_attr( $banner_info ).'</textarea>
        </td>
    </tr>
    <tr>
        <td style="width:10%">
            <p>Color de fondo:</p>
        </td>
        <td>
            <input type="text" name="bg_color_banner" style="width:100%" placeholder="#XXXXXX" value="'.$color_fondo.'">
        </td>
    </tr>
</table>';
  echo $html;
}


function SNBO_save_mb( $post_id ) {
  // Utilizamos nonce por seguridad. Debe venir correctamente del Meta Box.
  $nonce_name   = isset( $_POST['wpe_keywords_nonce'] ) ? $_POST['wpe_keywords_nonce'] : '';
  $nonce_action = 'wpe_keywords_nonce_save';
  // Verifica si nonce existe
  if ( ! isset( $nonce_name ) ) {
    return;
  }
  // Verifica si nonce es valido
  if ( ! wp_verify_nonce( $nonce_name, $nonce_action ) ) {
    return;
  }
  // Verifica si el usuario tiene permisos para editar posts
  if ( ! current_user_can( 'edit_post', $post_id ) ) {
    return;
  }
  
  // Verifica que este guardando un 'book'
  if ( 'banners' !== get_post_type() ) {
    return;
  }
  // Verifica si no es un guardado automatico
  if ( wp_is_post_autosave( $post_id ) ) {
    return;
  }
  // Verifica si no es una revision
  if ( wp_is_post_revision( $post_id ) ) {
    return;
  }
  // Si todas las validaciones anteriores pasaron,
    // entonces actualiza los valores en la base de datos.
  update_post_meta( $post_id, '_banner_info', sanitize_text_field( $_POST['banner_info'] ) );
  update_post_meta( $post_id, '_color_fondo_banner', sanitize_text_field( $_POST['bg_color_banner'] ) );
}
add_action( 'save_post', 'SNBO_save_mb' );




function SNBO_banner_item(){

  $mobile = get_option('sliding_banner_mobile'); 
  $speed = get_option('animation_speed'); 
  if(empty($speed)) $speed="20";
  $my_query = new WP_Query( array(
       'post_type' =>  array('post_type', 'banners'),
       'posts_per_page' => 20,
       'orderby' => 'modified',
       'order'   => 'ASC',
  ));


$html = '';
$html .= '<style>';
$o=0;
$html .='.marquee p {animation-duration: '.$speed.'s;}';
if($mobile=="yes"){
  $html .='@media only screen and (min-width:768px) { .sliding-banner{ display:none!important;} }';
}
if( $my_query->have_posts() ) : while( $my_query->have_posts() ) : $my_query->the_post();
$o++;
$bgcolorinfo = get_post_meta( get_the_ID(), '_color_fondo_banner', true);
if(empty($bgcolorinfo)) $bgcolorinfo="#BAD1DA";
$html .='.banner_info_'.$o.'{ background-color: '.$bgcolorinfo.';}';
  endwhile; 
    wp_reset_postdata();
endif;
$html .= '</style>';
$html .='<div class="sliding-banner" duration="'.$speed.'">';
$i=0;
if( $my_query->have_posts() ) : while( $my_query->have_posts() ) : $my_query->the_post();
$i++;
$banner_info = get_post_meta( get_the_ID(), '_banner_info', true);
$html .= '<div class="marquee banner_info banner_info_'.$i.'">';
$html .= '<p>'.$banner_info.'</p>';
$html .= '</div>';
  endwhile; 
    wp_reset_postdata();
endif;

$html .= '</div>';
$activate = get_option('sliding_banner_active');
$homepage = get_option('sliding_banner_homepage');
if($activate=="yes"){
	if($homepage=="yes"){
				if(is_front_page()){
					echo $html;
				}		
			} else {
					echo $html;
				}

}
}

add_action( 'wp_footer', 'SNBO_banner_item');


  add_action( 'admin_init', 'SNBO_update_sb_data' );
   function SNBO_update_sb_data() {
     register_setting( 'sliding_banner_data', 'sliding_banner_active' );
     register_setting( 'sliding_banner_data', 'sliding_banner_mobile' );
     register_setting( 'sliding_banner_data', 'animation_speed' );
     register_setting( 'sliding_banner_data', 'sliding_banner_homepage' );

}

add_action('admin_menu', 'SNBO_setup_menu');
 
function SNBO_setup_menu(){
        add_submenu_page( 'options-general.php','Sliding Banner', 'Sliding Banner', 'manage_options', 'sliding-banner', 'SNBO_options_page' );
}
 
function SNBO_options_page(){
  $activate = get_option('sliding_banner_active'); 
  $mobile = get_option('sliding_banner_mobile'); 
  $speed = get_option('animation_speed'); 
  $homepage = get_option('sliding_banner_homepage');

  echo "<h1>Sliding Banner</h1>";
  echo '<form method="post" action="options.php">';
  settings_fields( 'sliding_banner_data' );
  do_settings_sections( 'sliding_banner_data' );
    echo '<table class="form-table">';
      echo '<tr valign="top">';
      echo '<th scope="row">Activar Sliding Banner</th>';
      echo '<td><input type="checkbox" name="sliding_banner_active" value="yes"';
      if($activate=="yes") echo "checked";
      echo '>Marcar esta opción activará el Sliding Banner en la web.</td>';
      echo '</tr>';

      echo '<tr valign="top">';
      echo '<th scope="row">Activar solo en version móvil</th>';
      echo '<td><input type="checkbox" name="sliding_banner_mobile" value="yes"';
      if($mobile=="yes") echo "checked";
      echo '>Marcar esta opción mostrará el Sliding Banner solo en la version movil de la web</td>';
      echo '</tr>';
      echo '<tr valign="top">';
      echo '<th scope="row">Mostrar Sliding Banner solo en la Página de inicio</th>';
      echo '<td><input type="checkbox" name="sliding_banner_homepage" value="yes"';
      if($homepage=="yes") echo "checked";
      echo '>Marcar esta opción mostrará el Sliding Banner solo en la página de inicio de la web.</td>';
      echo '</tr>';
      echo '<tr valign="top">';
      echo '<th scope="row">Duración de la animación</th>';
      echo '<td><input type="number" name="animation_speed" value="'.$speed.'">';
      echo ' Introduce la duración de la animación en segundos</td>';
      echo '</tr>';


    echo '</table>';
    submit_button();
  echo '</form>';
}
}