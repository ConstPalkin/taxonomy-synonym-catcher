<?php
/*
Plugin Name: Casepress taxonomy synonyms catcher
Description: Casepress taxonomy synonyms catcher
Author: ConstPalkin
Version: 0.0.1
Author URI: http://casepress.org/
*/

// регистрация нового типа поста - Синонимы
add_action('init', 'cptui_register_my_cpt_tsc');
function cptui_register_my_cpt_tsc() {
register_post_type('cp_synonyms', array(
'label' => 'cp_synonyms',
'description' => '',
'public' => true,
'show_ui' => true,
'show_in_menu' => true,
'capability_type' => 'post',
'map_meta_cap' => true,
'hierarchical' => false,
'rewrite' => array('slug' => 'cp_synonyms', 'with_front' => true),
'query_var' => true,
//'supports' => array('title','editor','Основной_термин','revisions','custom-fields','page-attributes','post-formats'),
'supports' => array('title','Основной_термин','revisions','page-attributes'),
//'taxonomies' => array('post_tag','category','pro_category','type_product'),
'taxonomies' => get_taxonomies(),
'labels' => array (
  'name' => 'Синонимы',
  'singular_name' => 'Synonym',
  'menu_name' => 'Синонимы',
  'add_new' => 'Добавить',
  'add_new_item' => 'Add New Synonym',
  'edit' => 'Edit',
  'edit_item' => 'Edit Synonym',
  'new_item' => 'New Synonym',
  'view' => 'View Synonym',
  'view_item' => 'View Synonym',
  'search_items' => 'Search Synonym',
  'not_found' => 'No Synonyms Found',
  'not_found_in_trash' => 'No Synonyms Found in Trash',
  'parent' => 'Parent Synonym',
)
) ); }

//формирование Селекта
add_action('add_meta_boxes', 'meta_init'); 
function meta_init() { 
	add_meta_box('metabox1', 'Основной термин', 'meta_showup', 'cp_synonyms', 'advanced', 'high'); 
} 
function meta_showup($post, $box) { 
	$data = get_post_meta($post->ID, '_meta_data', true); 
	wp_nonce_field('meta_action', 'meta_nonce'); 
	//echo '<p>Основной термин: <input type="text" name="meta_field" value="' . esc_attr($data) . '"/></p>'; 
	$args1 = array(
		'show_option_all'    => '',
		'show_option_none'   => '',
		'orderby'            => 'name',
		'order'              => 'ASC',
		'show_last_update'   => 0,
		'show_count'         => 0,
		'hide_empty'         => 1,
		'child_of'           => 0,
		'exclude'            => '',
		'echo'               => 1,
		'selected'           => esc_attr($data),
		'hierarchical'       => 0,
		'name'               => 'meta_field',
		'id'                 => 'name',
		'class'              => 'postform',
		'depth'              => 0,
		'tab_index'          => 0,
		'taxonomy'           => 'category',
		'hide_if_empty'      => false
	); 
	echo '<p>Значение';
	wp_dropdown_categories( $args1 );
	echo '</p>';
} 

//сохранение мета тегов при сохранениии поста
add_action('save_post', 'meta_save'); 
function meta_save($postID) { 

	// пришло ли поле наших данных? 
	if (!isset($_POST['meta_field'])) 
	return; 
	
	// не происходит ли автосохранение? 
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) 
	return; 
	
	// не ревизию ли сохраняем? 
	if (wp_is_post_revision($postID)) 
	return; 
	
	// проверка достоверности запроса 
	check_admin_referer('meta_action', 'meta_nonce'); 
	
	// коррекция данных 
	$data = sanitize_text_field($_POST['meta_field']); 
	
	// запись 
	update_post_meta($postID, '_meta_data', $data); 

} 










?>
