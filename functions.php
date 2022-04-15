<?php
function my_enqueue_scripts(){
    wp_enqueue_script('jquery'); 
    wp_enqueue_script('bundle_js', get_template_directory_uri().'/assets/js/bundle.js', array()); 
    wp_enqueue_style('my_styles', get_template_directory_uri().'/assets/css/styles.css', array());
}
add_action('wp_enqueue_scripts', 'my_enqueue_scripts');


//ヘッダ－・フッターのカスタムメニュー化
register_nav_menus(
    array(
        'place_global' => 'グローバル',
        'place_footer' => 'フッターナビ',
    )
);


//メイン画像上にテンプレートごとのタイトルを表示
function get_main_title(){
    if(is_singular('post')):
        $category_obj=get_the_category();
        return $category_obj[0] ->name;
    elseif(is_page()):
        return get_the_title();
    elseif(is_category() || is_tax()):
        return single_cat_title();
    elseif(is_search()):
        return 'サイト内検索結果';
    elseif(is_404()):
        return 'ページが見つかりません';
    elseif(is_singular('daily_contribution')):
        global $post;
        $term_obj = get_the_terms($post->ID,'event');
        return $term_obj[0]->name;
    endif;
}


//子ページを取得する関数
function get_child_pages($number = -1,$specified_id = null){
    if(isset($specified_id)):
       $parent_id = $specified_id;
    else:
        $parent_id = get_the_id();
    endif;
    
    $args = array (
        'posts_per_page' =>  $number , 
        'post_type' =>'page', 
        'orderby' => 'menu_order',
        'order' => 'ASC', 
        'post_parent' =>$parent_id, 
    );
    $child_pages = new WP_Query ($args);
    return $child_pages;
}


//アイキャッチ画像を利用できるようにする
add_theme_support('post-thumbnails');

//トップページのメイン画像用のサイズ指定
add_image_size('top',1077,622,true );

//地域貢献活動一覧画像用のサイズ指定
add_image_size('contribution',557,280,true);

//トップページの地域貢献活動に使用している画像用のサイズ指定
add_image_size('front-contribution',255,189,true);

//企業情報・店舗情報一覧画像用のサイズ指定
add_image_size('common',465,252,true);

//各ページのメイン画像用のサイズ指定
add_image_size('detail',1100,330,true);

//検索一覧画像用のサイズ指定
add_image_size('search',168,168,true);


//各テンプレートごとのメイン画像を表示
function get_main_image(){
    if(is_page()||is_singular('daily_contribution')):
        $attachment_id= get_field('main_image');
    if ( is_front_page() ):
        return wp_get_attachment_image($attachment_id,'top');
    else:
        return wp_get_attachment_image($attachment_id,'detail');
    endif;
    elseif(is_category('news') || is_singular('post')):
        return '<img src="'.get_template_directory_uri(). '/assets/images/bg-page-news.jpg"/>';
    elseif(is_search()||is_404()):
        return '<img src="'.get_template_directory_uri(). '/assets/images/bg-page-search.jpg"/>';
    elseif ( is_tax( 'event' ) ):
        $term_obj = get_queried_object();
        $image_id = get_field( 'event_image', $term_obj->taxonomy. '_'. $term_obj->term_id );
        return wp_get_attachment_image( $image_id, 'detail' );
    else:
        return '<img src="'.get_template_directory_uri(). '/assets/images/bg-page-dummy.jpg"/>';
    endif;
}


//特定の記事を抽出する関数
function get_specific_posts($post_type,$taxonomy = null,$term = null, $number = -1){
    if(!$term):
        $terms_obj = get_terms('event');
        $term = wp_list_pluck($term_obj,'slug');
    endif;

    $args = array(
        'post_type' => $post_type,
        'tax_query' => array(
            array(
                'taxonomy'=>$taxonomy,
                'field' => 'slug',
                'terms' => $term,
            ),
        ),
        'posts_per_page'=> $number,
    );
    $specific_posts = new WP_Query($args);
    return $specific_posts;
}



//抜粋文のデフォルト文字数を定義する
function cms_excerpt_more(){
    return '...';
}
add_filter('excerpt_more','cms_excerpt_more');

function cms_excerpt_length(){
    return 80;
}
add_filter('excerpt_mblength','cms_excerpt_length');


//抜粋文を適度な長さに調整する
function get_flexible_excerpt($number){
    $value = get_the_excerpt();
    $value = wp_trim_words($value,$number,'...');
    return $value;
}

//抜粋機能を固定ページに使えるように設定
add_post_type_support('page','excerpt');


//get_the_excerpt()で取得する文字列に改行タグを挿入(管理画面の抜粋欄の文も必要箇所は改行しておく)
function apply_excerpt_br($value){
    return nl2br($value);
}
add_filter('get_the_excerpt','apply_excerpt_br');


//ウィジェット機能を有効化
function theme_widgets_init(){
    register_sidebar( array(
        'name' => 'サイドバーウィジェットエリア',
        'id' => 'primary-widget-area',
        'description' => '固定ページのサイドバー',
        'before_widget' => '<aside class="side-inner">',
        'after_widget' => '</aside>',
        'before_title' => '<h4 class="title">',
        'after_title' => '</h4>',
    ));
}
add_action('widgets_init','theme_widgets_init');