<?php
/*
Plugin Name: Casepress taxonomy synonyms catcher
Description: Casepress taxonomy synonyms catcher
Author: ConstPalkin
Version: 1.0.1
Author URI: http://casepress.org/
*/

// -------------------------------------------регистрация нового типа поста - Синонимы
$catcher_version = '1.0.1';
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
'supports' => array('title','Основной_термин','revisions','page-attributes','category'),
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
	add_meta_box('metabox_catcher', 'Основной термин', 'meta_showup', 'cp_synonyms', 'advanced', 'high'); 
} 
function meta_showup($post, $box) { 
	$data = get_post_meta($post->ID, '_meta_data_syn', true); 
	wp_nonce_field('meta_action_syn', 'meta_nonce_syn'); 
	$args1 = array(
		'show_option_all'    => '',
		'show_option_none'   => '',
		'orderby'            => 'name',
		'order'              => 'ASC',
		'show_last_update'   => 0,
		'show_count'         => 0,
		'hide_empty'         => 0,
		'child_of'           => 0,
		'exclude'            => '',
		'echo'               => 1,
		'selected'           => esc_attr($data),
		'hierarchical'       => 0,
		'name'               => 'meta_field_syn',
		'id'                 => 'name',
		'class'              => 'postform',
		'depth'              => 0,
		'tab_index'          => 0,
		'taxonomy'           => 'category',
		'hide_if_empty'      => false
	); 
	echo '<p>Значение: ';
	wp_dropdown_categories( $args1 );
	echo '</p>';
} 

//сохранение мета тегов при сохранениии поста
add_action('save_post', 'meta_save'); 
function meta_save($postID) { 

	// пришло ли поле наших данных? 
	if (!isset($_POST['meta_field_syn'])) 
	return; 
	
	// не происходит ли автосохранение? 
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) 
	return; 
	
	// не ревизию ли сохраняем? 
	if (wp_is_post_revision($postID)) 
	return; 
	
	// проверка достоверности запроса 
	check_admin_referer('meta_action_syn', 'meta_nonce_syn'); 
	
	// коррекция данных 
	$data = sanitize_text_field($_POST['meta_field_syn']); 
	
	// запись 
	update_post_meta($postID, '_meta_data_syn', $data); 

} 
//---------------------------------------------------- экшн

//берем все посты типа СИНОНИМ
//составляем словарь соответствий
//составляем из всех словарей один
function make_s_dictionary() {
	$s_posts = get_posts(array('post_type'=>'cp_synonyms','nopaging'=>true,'post_status '=>'any'));
	$s_dict = array();
	foreach ($s_posts as $s_post) {
		$s_categories = get_the_category($s_post->ID);
		//$s_main_term = get_the_category_by_ID(get_post_meta($s_post->ID, '_meta_data', true)); 
		$s_main_term = get_post_meta($s_post->ID, '_meta_data_syn', true); 
		foreach($s_categories as $s_category) {
			$s_dict[$s_category->cat_ID] = $s_main_term;
		}
	}
	return $s_dict;
}
//при сохранениии поста проверяем все категории по словарю
function new_tax_save($post_id) { 
	if ('cp_synonyms' != get_post_type($post_id)) { // предостерегаем от мазохизма
		if ($syn_dict = make_s_dictionary()) {
			$categories = get_the_category($post_id);
			$add_category = array();
			foreach($categories as $category) {
				if(array_key_exists($category->cat_ID, $syn_dict)) { //если есть совпадение - добавляем новую
					$add_category[] = $syn_dict[$category->cat_ID];
				} else { //если нет - оставляем категорию как есть - в составе категорий
					$add_category[] = $category->cat_ID;
				}
			}
			//вставляем список категорий с заменой предыдущих (false)	
			if ($uniq_arr = array_unique($add_category)) wp_set_post_categories($post_id, $uniq_arr);
		}
	}
} add_action('save_post', 'new_tax_save'); 

// кнопочка в админ-панели для обновления всех рубрик
add_action( 'admin_menu', 'tsc_plugin_menu' );
function tsc_plugin_menu() {
	add_menu_page( 'Taxonomy Synonyms Catcher', 'TSC', 'manage_options', 'tsc_main_menu', 'tsc_settings' );
}
function tsc_settings() {
	global $catcher_version;
	?>
	<div class="wrap">
		<h2>Taxonomy Synonyms Catcher plugin (ver. <?php echo $catcher_version; ?>)</h2>
		<h3>Ловец синонимов таксономий</h3>
		<h4><i>Используйте с аккуратностью, осознавая то, что делаете (будьте уверены в постах-синонимах).</i></h4>
		<p>
			<form action="<?php echo $_SERVER['REQUEST_URI']?>" method="POST">
				<?php wp_nonce_field('meta_action_syn1', 'meta_nonce_syn1'); ?>
				<input type="submit" name="tsc_apply" value="массово задействовать" onClick="return confirm('Проверили соответствие постов-синонимов?');" />
			</form>
		</p>
	</div>
	<?php
	//перебираем все посты, применяем к ним заменялку рубрик
	if (isset($_POST['tsc_apply']) && check_admin_referer('meta_action_syn1', 'meta_nonce_syn1')) {
		$posts = get_posts(array('post_type'=>'post','nopaging'=>true,'post_status '=>'any'));
		foreach($posts as $post) {
			new_tax_save($post->ID);
		}
	}
}








?>
