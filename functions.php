<?php
global $mobile; 
require_once locate_template('lib/mobile.php'); 
add_theme_support('menus');
add_theme_support('title-tag');
add_theme_support( 'post-thumbnails' );
$custom_header_defaults = array(
		'header-text' => false,
);
add_theme_support( 'custom-header', $custom_header_defaults );
add_theme_support('custom-logo');
add_image_size( 'pc_thumb', 276, 146 , true);
add_image_size( 'sp_popular_thumb', 160, 100 , true);
add_image_size( 'slide_image', 720, 400 , true);
register_nav_menu( 'header-nav',  ' ヘッダーナビゲーション ' );
register_nav_menu( 'footer-nav',  ' フッターナビゲーション ' );
//タイトル出力
add_filter( 'pre_get_document_title', 'get_title_temp' );
add_filter( 'mime_types', 'split_combined_mimes_for_apt' );
register_sidebar(array(
 'name' => 'PC_サイドバー',
 'id' => 'pc_side',
 'before_widget' => '<div class="side-category">',
 'after_widget' => '</div>',
 'before_title' => '<h3>',
 'after_title' => '</h3>'
));
/*
* thumbnail sizeをUAで見分ける
*/

function get_thumb_size($mobile){
  if(!$mobile){
    return "pc_thumb";
  } elseif ($mobile){
    return "thumbnail";
  }
}

global $thumb_size;
$thumb_size = get_thumb_size($mobile);

//まだ使ってない
function get_title_temp( $title ) {
    if ( is_front_page() && is_home() ) { //トップページなら
        $title = get_bloginfo( 'name', 'display' );
    } elseif ( is_singular() ) { //シングルページなら
        $title = single_post_title( '', false );
    }
    return $title;
}
function pre_links() {
    wp_enqueue_style( 'style', get_template_directory_uri() . '/style.css', array(), '1.0.0' );
}
function main_js() {
    wp_enqueue_script( 'style', get_template_directory_uri() . '/js/main.js', array(), '1.0.0', true );
}
/*
*enqueue_script
*/
add_action( 'wp_enqueue_scripts', 'swiper_styles');
add_action( 'wp_enqueue_scripts', 'swiper_scripts');
add_action( 'wp_enqueue_scripts', 'pre_links' );
add_action( 'wp_enqueue_scripts', 'main_js' );
function setPostViews($post_id) {
  $custom_key = 'post_views_count';
  $count = get_post_meta($post_id, $custom_key, true);
  //初めてのビューどうか
  if($count == ''){
    delete_post_meta($post_id, $custom_key);
    add_post_meta($post_id, $custom_key, '0');
  }else{
    $count++;
    update_post_meta($post_id, $custom_key, $count);
  }
}
function swiper_styles() {
  wp_enqueue_style( 'swiper', get_template_directory_uri() . '/css/swiper.min.css', array(), false, 'all');
}
function swiper_scripts() {
  wp_enqueue_script( 'swiper', get_template_directory_uri() . '/js/swiper.min.js', array(), false, true );
}
/*
*記事のビュー数を取得
*/
function getPostViews($post_id){
  $custom_key = 'post_views_count';
  $count = get_post_meta($post_id, $custom_key, true);
  if($count==''){
    //まだ0ビューなら
    delete_post_meta($post_id, $custom_key);
    add_post_meta($post_id, $custom_key, '0');
    return "0 View";
  }
  return $count.' Views';
}
/*
*thumnail
*/
function split_combined_mimes_for_apt( $mime_types ) {
	foreach ( $mime_types as $regex => $mime_type ) {
		if ( false !== strpos( $regex, '|' ) ) {
			$keys = explode( '|', $regex );
			foreach ( $keys as $key ) {
				$mime_types[ $key ] = $mime_type;
			}
		}
	}
	return $mime_types;
}

/*
*文字数制限
*/
function trimwidth($str, $length = 70, $append = "...", $mobile = true) {
    $str = strip_tags($str);
    $str = preg_replace('/\s(?=\s)/', '', $str);
    $str = strip_shortcodes($str);
    $str = str_replace(array("\r", "\n"), ' ', $str);
    if (mb_strlen($str) > $length) {
        //mb_strimwidth("123456", 0, 5, "…", "UTF-8");
        $str= mb_strimwidth($str, 0, $length, $append , 'UTF-8');
        return $str;
    }
    return $str;
}
/*
*表示カテゴリーの絞り込み
*/
function include_widget_categories($a){
    $include = '1,37,40,35';
    $a['include'] = $include;
    return $a;
}
add_filter( 'widget_categories_args', 'include_widget_categories');

/*
*文字数制限あり新着記事
*/
class Mb_Trim extends WP_Widget {
    function __construct() {
        //ウィジェットボックスの名前と説明文
        parent::__construct(false,'文字数制限あり最新記事',array('description' => '直近の新着・更新'));
    }
    function widget($args, $instance) {
        extract( $args );
        //タイトル名を取得
        $title_new = apply_filters( 'widget_title_new', $instance['title_new'] );
        //表示数を取得
        $entry_count = apply_filters( 'widget_entry_count', $instance['entry_count'] );
        $entry_title_max = apply_filters( 'widget_entry_title_max', $instance['entry_title_max'] );
        if ( !$entry_count ) $entry_count = 5; //表示数が設定されていない時は5にする
        if ( !$entry_title_max ) $entry_title_max = 30; //表示数が設定されていない時は5にする
      ?>
      <?php echo $before_widget;?>
        <h3><?php if ($title_new) {
          echo $title_new; //タイトルが設定されている場合は使用する
        } else {
          echo '新着・更新記事';
        }
      ?></h3>
        <div class="widgetContent">
          <ul class="wpp-list">
          <?php global $post; /* グローバル変数$postの宣言 */ ?>
          <?php $myposts = get_posts(array('numberposts'=> $entry_count,'orderby'=>'date'));
          foreach($myposts as $post) : 
              setup_postdata($post); ?>
              <li><a href="<?php the_permalink(); ?>" alt="<?php the_title(); ?>">
                      <?php if(!strlen($post->post_title)){
                          echo "記事タイトルがありません";
                        }elseif(mb_strlen($post->post_title, 'UTF-8') > $entry_title_max){
                        	$title= mb_substr($post->post_title, 0, $entry_title_max, 'UTF-8');
                        	echo $title.'…';
                        }else{
                        	echo $post->post_title;
                        } ?></a></li>
          <?php endforeach; ?>
          <?php wp_reset_postdata();?>
          </ul>
          </div>
        <?php echo $after_widget;?>
      <?php
    }
    function update($new_instance, $old_instance) {
     $instance = $old_instance;
     $instance['title_new'] = strip_tags($new_instance['title_new']);
     $instance['entry_count'] = strip_tags($new_instance['entry_count']);
     $instance['entry_title_max'] = strip_tags($new_instance['entry_title_max']);
        return $instance;
    }
    function form($instance) {
        if(empty($instance)){
          $instance = array('title_new' => null, 'entry_count' => null, 'entry_title_max' => null);
        }
        $title_new = esc_attr($instance['title_new']);
        $entry_count = esc_attr($instance['entry_count']);
        $entry_title_max = esc_attr($instance['entry_title_max']);
        ?>
        <?php //タイトル入力フォーム ?>
        <p>
          <label for="<?php echo $this->get_field_id('title_new'); ?>">
          <?php echo('新着・更新記事のタイトル'); ?>
          </label>
          <input class="widefat" id="<?php echo $this->get_field_id('title_new'); ?>" name="<?php echo $this->get_field_name('title_new'); ?>" type="text" value="<?php echo $title_new; ?>" />
        </p>
        <?php //表示数入力フォーム ?>
        <p>
          <label for="<?php echo $this->get_field_id('entry_count'); ?>">
          <?php echo('表示件数（半角数字、デフォルト：5）'); ?>
          </label>
          <input class="widefat" id="<?php echo $this->get_field_id('entry_count'); ?>" name="<?php echo $this->get_field_name('entry_count'); ?>" type="text" value="<?php echo $entry_count; ?>" />
        </p>
        <?php //表示文字数入力フォーム ?>
        <p>
          <label for="<?php echo $this->get_field_id('entry_title_max'); ?>">
          <?php echo('表示文字数（半角数字、デフォルト：30）'); ?>
          </label>
          <input class="widefat" id="<?php echo $this->get_field_id('entry_title_max'); ?>" name="<?php echo $this->get_field_name('entry_title_max'); ?>" type="text" value="<?php echo $entry_title_max; ?>" />
        </p>
<?php
    }
}
add_action('widgets_init', create_function('', 'return register_widget("Mb_Trim");'));
/*
*top adsense
*/
class Ads_Flex extends WP_Widget {
    function __construct() {
        //ウィジェットボックスの名前と説明文
        parent::__construct(false,'adsense:横並び',array('description' => 'トップの横並びのadsenseコード'));
    }
    function widget($args, $instance) {
        extract( $args );
        $ad_1 = apply_filters( 'widget_ad_1', $instance['ad_1'] );
        $ad_2 = apply_filters( 'widget_ad_2', $instance['ad_2'] );
      ?>
      <?php echo $before_widget;?>
          <div class="adsense"><?php echo $ad_1; ?></div>
          <div class="adsense"><?php echo $ad_2; ?></div>
        <?php echo $after_widget;?>
      <?php
    }
    function update($new_instance, $old_instance) {
     $instance = $old_instance;
     $instance['ad_1'] = $new_instance['ad_1'];
     $instance['ad_2'] = $new_instance['ad_2'];
     return $instance;
    }
    function form($instance) {
        if(empty($instance)){
          $instance = array('ad_1' => null, 'ad_2' => null);
        }
        $ad_1 = $instance['ad_1'];
        $ad_2 = $instance['ad_2'];
        ?>
        <?php //adsense: 左 ?>
        <p>
          <label for="<?php echo $this->get_field_id('ad_1'); ?>">
          <?php echo('adsense: 左'); ?>
          </label>
          <textarea  class="widefat" rows="8" colls="20" id="<?php echo $this->get_field_id('ad_1'); ?>" name="<?php echo $this->get_field_name('ad_1'); ?>"><?php echo $ad_1; ?></textarea>
        </p>
        <?php //adsense: 右 ?>
        <p>
          <label for="<?php echo $this->get_field_id('entry_count'); ?>">
          <?php echo('adsense: 右'); ?>
          </label>
          <textarea  class="widefat" rows="8" colls="20" id="<?php echo $this->get_field_id('ad_2'); ?>" name="<?php echo $this->get_field_name('ad_2'); ?>"><?php echo $ad_2; ?></textarea>
        </p>
<?php
    }
}
add_action('widgets_init', create_function('', 'return register_widget("Ads_Flex");'));
register_sidebar(array(
 'name' => 'アドセンス：横並び',
 'id' => 'top_ads',
 'before_widget' => '<div class="ads">',
 'after_widget' => '</div>',
 'before_title' => '<h3>',
 'after_title' => '</h3>'
 ));

/*
*人気記事
*/
class Popular_Widget extends WP_Widget {
    function __construct() {
        //ウィジェットボックスの名前と説明文
        parent::__construct(false,'人気記事',array('description' => '人気記事'));
    }
    function widget($args, $instance) {
        extract( $args );
        //タイトル名を取得
        $popular_title = apply_filters( 'widget_popular_title', $instance['popular_title'] );
        //表示数を取得
        $popular_count = apply_filters( 'widget_popular_count', $instance['popular_count'] );
        $popular_title_max = apply_filters( 'widget_popular_title_max', $instance['popular_title_max'] );
        if ( !$popular_count ) $popular_count = 5; //表示数が設定されていない時は5にする
        if ( !$popular_title_max ) $popular_title_max = 30; //表示数が設定されていない時は5にする
      ?>
      
      <?php //人気記事取得用クエリ 
      $args = array(
        'post_type' => 'post',
        'meta_key' => 'post_views_count',
        'orderby' => 'meta_value_num',
        'order'=>'DESC',
        'posts_per_page' => 5,
      );?>
        
      <?php echo $before_widget;?>
        <h3 class="common__title">
          <picture>
            <source media="(max-width: 600px)" srcset="<?php bloginfo('template_directory'); ?>/images/sp_recommend.png">
            <img src="<?php bloginfo('template_directory'); ?>/images/recommend.png" alt="人気記事">
          </picture>
        </h3>
        <div class="widgetContent">
          <?php global $post; /* グローバル変数$postの宣言 */ ?>
          <?php $popular_posts = get_posts($args);?>
          <?php if($popular_posts): foreach($popular_posts as $post): setup_postdata($post); ?>
           <div class="card">
             <div class="card__thumb"><a href="<?php the_permalink(); ?>">
             <?php if( has_post_thumbnail() ): ?>
                <?php the_post_thumbnail('sp_popular_thumb'); ?>
              <?php else: ?>
                <img src="<?php echo get_template_directory_uri(); ?>/add/no-image.gif" alt="no-img"/>
              <?php endif; ?></a>
              </div>
              <div class="card-body">
              <h3 class="card-body__title"><a href="<?php the_permalink(); ?>" alt="<?php the_title(); ?>">
              <?php if(!strlen($post->post_title)){
                    echo "記事タイトルがありません";
                  }elseif(mb_strlen($post->post_title, 'UTF-8')>$popular_title_max){
                  	$title= mb_substr($post->post_title, 0, $popular_title_max, 'UTF-8');
                  	echo $title.'…';
                  }else{
                  	echo $post->post_title;
                  }                           
                ?></a></h3>
              </div>
          </div>
          <?php endforeach; endif;?>
          <?php wp_reset_postdata();?>
          </div>
        <?php echo $after_widget;?>
      <?php
    }
    function update($new_instance, $old_instance) {
      $instance = $old_instance;
      $instance['popular_title'] = strip_tags($new_instance['popular_title']);
      $instance['popular_count'] = strip_tags($new_instance['popular_count']);
      $instance['popular_title_max'] = strip_tags($new_instance['popular_title_max']);
      return $instance;
    }
    function form($instance) {
        if(empty($instance)){
          $instance = array('popular_title' => null, 'popular_count' => null, 'popular_title_max' => null);
        }
        $popular_title = esc_attr($instance['popular_title']);
        $popular_count = esc_attr($instance['popular_count']);
        $popular_title_max = esc_attr($instance['popular_title_max']);
        ?>
        <?php //タイトル入力フォーム ?>
        <p>
          <label for="<?php echo $this->get_field_id('popular_title'); ?>">
          <?php echo('人気記事のタイトル'); ?>
          </label>
          <input class="widefat" id="<?php echo $this->get_field_id('popular_title'); ?>" name="<?php echo $this->get_field_name('popular_title'); ?>" type="text" value="<?php echo $popular_title; ?>" />
        </p>
        <?php //表示数入力フォーム ?>
        <p>
          <label for="<?php echo $this->get_field_id('popular_count'); ?>">
          <?php echo('表示件数（半角数字、デフォルト：5）'); ?>
          </label>
          <input class="widefat" id="<?php echo $this->get_field_id('popular_count'); ?>" name="<?php echo $this->get_field_name('popular_count'); ?>" type="text" value="<?php echo $popular_count; ?>" />
        </p>
        <?php //表示文字数入力フォーム ?>
        <p>
          <label for="<?php echo $this->get_field_id('popular_title_max'); ?>">
          <?php echo('表示文字数（半角数字、デフォルト：30）'); ?>
          </label>
          <input class="widefat" id="<?php echo $this->get_field_id('popular_title_max'); ?>" name="<?php echo $this->get_field_name('popular_title_max'); ?>" type="text" value="<?php echo $popular_title_max; ?>" />
        </p>
<?php
    }
}
add_action('widgets_init', create_function('', 'return register_widget("Popular_Widget");'));

register_sidebar(array(
 'name' => 'SP_記事ページ：footer',
 'id' => 'sp_footer',
 'before_widget' => '<div>',
 'after_widget' => '</div>',
 'before_title' => '<h3 class="common__title">',
 'after_title' => '</h3>'
 ));
 
 /*
 * accordion
 */
 
class Archives_Accordion extends WP_Widget {
    function __construct() {
        //ウィジェットボックスの名前と説明文
        parent::__construct(false,'新着記事: Accordion',array('description' => '新着記事: Accordion'));
    }
    function widget($args, $instance) {
        extract( $args );
        //タイトル名を取得
        $title_new = apply_filters( 'widget_title_new', $instance['title_new'] );
        //表示数を取得
        $entry_count = apply_filters( 'widget_entry_count', $instance['entry_count'] );
        $entry_title_max = apply_filters( 'widget_entry_title_max', $instance['entry_title_max'] );
        if ( !$entry_count ) $entry_count = 5; //表示数が設定されていない時は5にする
        if ( !$entry_title_max ) $entry_title_max = 30; //表示数が設定されていない時は5にする
      ?>
      <?php echo $before_widget;?>
        <h3 class="common__title accordion archives__accordion" id="archives__accordion"><?php if ($title_new) {
          echo $title_new; //タイトルが設定されている場合は使用する
        } else {
          echo '新着・更新記事';
        }
      ?></h3>
        <div class="accordion__box">
          <ul class="accordion__items">
          <?php global $post; /* グローバル変数$postの宣言 */ ?>
          <?php $myposts = get_posts(array('numberposts'=> $entry_count,'orderby'=>'date'));
          foreach($myposts as $post) : 
              setup_postdata($post); ?>
              <li><a href="<?php the_permalink(); ?>" alt="<?php the_title(); ?>">
                <span><?php $title  = $post->post_title;
                        if(mb_strlen($title, 'UTF-8') > $entry_title_max){
                        	$title= mb_substr($title, 0, $entry_title_max, 'UTF-8');
                        	echo $title.'…';
                        }else{
                        	echo $title;
                        } ?>
              </span></a></li>
          <?php endforeach; ?>
          <?php wp_reset_postdata();?>
          </ul>
          </div>
        <?php echo $after_widget;?>
      <?php
    }
    function update($new_instance, $old_instance) {
     $instance = $old_instance;
     $instance['title_new'] = strip_tags($new_instance['title_new']);
     $instance['entry_count'] = strip_tags($new_instance['entry_count']);
     $instance['entry_title_max'] = strip_tags($new_instance['entry_title_max']);
        return $instance;
    }
    function form($instance) {
        if(empty($instance)){
          $instance = array('title_new' => null, 'entry_count' => null, 'entry_title_max' => null);
        }
        $title_new = esc_attr($instance['title_new']);
        $entry_count = esc_attr($instance['entry_count']);
        $entry_title_max = esc_attr($instance['entry_title_max']);
        ?>
        <?php //タイトル入力フォーム ?>
        <p>
          <label for="<?php echo $this->get_field_id('title_new'); ?>">
          <?php echo('新着・更新記事のタイトル'); ?>
          </label>
          <input class="widefat" id="<?php echo $this->get_field_id('title_new'); ?>" name="<?php echo $this->get_field_name('title_new'); ?>" type="text" value="<?php echo $title_new; ?>" />
        </p>
        <?php //表示数入力フォーム ?>
        <p>
          <label for="<?php echo $this->get_field_id('entry_count'); ?>">
          <?php echo('表示件数（半角数字、デフォルト：5）'); ?>
          </label>
          <input class="widefat" id="<?php echo $this->get_field_id('entry_count'); ?>" name="<?php echo $this->get_field_name('entry_count'); ?>" type="text" value="<?php echo $entry_count; ?>" />
        </p>
        <?php //表示文字数入力フォーム ?>
        <p>
          <label for="<?php echo $this->get_field_id('entry_title_max'); ?>">
          <?php echo('表示文字数（半角数字、デフォルト：30）'); ?>
          </label>
          <input class="widefat" id="<?php echo $this->get_field_id('entry_title_max'); ?>" name="<?php echo $this->get_field_name('entry_title_max'); ?>" type="text" value="<?php echo $entry_title_max; ?>" />
        </p>
<?php
    }
}
add_action('widgets_init', create_function('', 'return register_widget("Archives_Accordion");'));

/*
* カテゴリアコーディオン
*/

class Category_Accordion extends WP_Widget {
    function __construct() {
        //ウィジェットボックスの名前と説明文
        parent::__construct(false,'カテゴリ: Accordion',array('description' => '新着記事: Accordion'));
    }
    function widget($args, $instance) {
        extract( $args );
        //タイトル名を取得
        $title_new = apply_filters( 'widget_title_new', $instance['title_new'] );
        //表示数を取得
        $entry_count = apply_filters( 'widget_entry_count', $instance['entry_count'] );
        $entry_title_max = apply_filters( 'widget_entry_title_max', $instance['entry_title_max'] );
        if ( !$entry_count ) $entry_count = 5; //表示数が設定されていない時は5にする
        if ( !$entry_title_max ) $entry_title_max = 30; //表示数が設定されていない時は5にする
      ?>
      <?php echo $before_widget;?>
        <h3 class="common__title accordion category__accordion" id="category__accordion"><?php if ($title_new) {
          echo $title_new; //タイトルが設定されている場合は使用する
        } else {
          echo 'カテゴリー';
        }
      ?></h3>
        <div class="accordion__box">
          <ul class="accordion__items">
          <?php $arg = array(
              'hide_empty' => 0,
          );
          $categories = get_categories($arg); ?>
          <?php foreach($categories as $category) : ?>
          <li><a href="<?php echo get_category_link( $category->term_id ); ?>" alt="<?php the_title(); ?>">
            <span><?php $title  = $category->name;
                    if(mb_strlen($title, 'UTF-8') > $entry_title_max){
                    	$title= mb_substr($title, 0, $entry_title_max, 'UTF-8');
                    	echo $title.'…';
                    }else{
                    	echo $title;
                    } ?>
            </span></a></li>
          <?php endforeach; ?>
          <?php wp_reset_postdata();?>
          </ul>
          </div>
        <?php echo $after_widget;?>
      <?php
    }
    function update($new_instance, $old_instance) {
     $instance = $old_instance;
     $instance['title_new'] = strip_tags($new_instance['title_new']);
     $instance['entry_count'] = strip_tags($new_instance['entry_count']);
     $instance['entry_title_max'] = strip_tags($new_instance['entry_title_max']);
        return $instance;
    }
    function form($instance) {
        if(empty($instance)){
          $instance = array('title_new' => null, 'entry_count' => null, 'entry_title_max' => null);
        }
        $title_new = esc_attr($instance['title_new']);
        $entry_count = esc_attr($instance['entry_count']);
        $entry_title_max = esc_attr($instance['entry_title_max']);
        ?>
        <?php //タイトル入力フォーム ?>
        <p>
          <label for="<?php echo $this->get_field_id('title_new'); ?>">
          <?php echo('タイトル'); ?>
          </label>
          <input class="widefat" id="<?php echo $this->get_field_id('title_new'); ?>" name="<?php echo $this->get_field_name('title_new'); ?>" type="text" value="<?php echo $title_new; ?>" />
        </p>
        <?php //表示文字数入力フォーム ?>
        <p>
          <label for="<?php echo $this->get_field_id('entry_title_max'); ?>">
          <?php echo('表示文字数（半角数字、デフォルト：30）'); ?>
          </label>
          <input class="widefat" id="<?php echo $this->get_field_id('entry_title_max'); ?>" name="<?php echo $this->get_field_name('entry_title_max'); ?>" type="text" value="<?php echo $entry_title_max; ?>" />
        </p>
<?php
    }
}
add_action('widgets_init', create_function('', 'return register_widget("Category_Accordion");'));

/*
* カテゴリ：ヘッダー
*/

register_sidebar(array(
 'name' => 'カテゴリ：ヘッダー',
 'id' => 'category_header',
));
class Category_Header extends WP_Widget {
    function __construct() {
        //ウィジェットボックスの名前と説明文
        parent::__construct(false,'カテゴリ:ボタン',array('description' => 'カテゴリ:ボタン'));
    }
    function widget($args, $instance) {
        extract( $args );
        $entry_categories = apply_filters( 'widget_entry_categories', $instance['entry_categories'] );
        ?>
          <?php $arg = array(
              'include' => $entry_categories,
          );
          $categories = get_categories($arg); ?>
          <?php foreach($categories as $category) : ?>
          <a class="c-btn" href="<?php echo get_category_link( $category->term_id ); ?>" alt="<?php the_title(); ?>">
            <?php echo $category->name; ?></a>
          <?php endforeach; ?>
          <?php wp_reset_postdata();?>
      <?php
    }
    function update($new_instance, $old_instance) {
     $instance = $old_instance;
     $instance['entry_categories'] = strip_tags($new_instance['entry_categories']);
     return $instance;
    }
    function form($instance) {
        if(empty($instance)){
          $instance = array('entry_categories' => null);
        }
        $entry_categories = esc_attr($instance['entry_categories']);
        ?>
        <?php //タイトル入力フォーム ?>
        <p>
          <label for="<?php echo $this->get_field_id('entry_categories'); ?>">
          <?php echo('表示カテゴリID: カンマ区切り'); ?>
          </label>
          <input class="widefat" id="<?php echo $this->get_field_id('entry_categories'); ?>" name="<?php echo $this->get_field_name('entry_categories'); ?>" type="text" value="<?php echo $entry_categories; ?>" />
        </p>
<?php
    }
}
add_action('widgets_init', create_function('', 'return register_widget("Category_Header");'));
register_sidebar(array(
 'name' => 'カテゴリ：フッター',
 'id' => 'category_footer',
 'before_widget' => '<div>',
 'after_widget' => '</div>',
 'before_title' => '<h3 class="common__title">',
 'after_title' => '</h3>'
));

/*
*  pager
*/

function pagination($pages = '', $range = 2){
  $showitems = ($range * 2)+1;
 
  global $paged;
  if(empty($paged)) $paged = 1;
  //ページ情報の取得
  if($pages == '') {
    global $wp_query;
    $pages = $wp_query->max_num_pages;
    if(!$pages){
      $pages = 1;
    }
  }
  if(1 != $pages) {
    echo '<ul>';
    for ($i=1; $i <= $pages; $i++){
      // 1 != 7 &&  1 <= 5  1 >= -3 7>=5 4 2~6
      if (1 != $pages &&( !($i >= $paged+$range+1 || $i <= $paged-$range-1) || $pages <= $showitems ))       {
        echo ($paged == $i)? '<li class="current"><a>'.$i.'</a></li>':'<li><a href="'.get_pagenum_link($i).'" class="inactive" >'.$i.'</a></li>';
      } elseif($i <= $showitems && $i >= $paged+$range+1 && $i >= $paged-$range-1){
        echo '<li><a href="'.get_pagenum_link($i).'" class="inactive" >'.$i.'</a></li>';
      }
    }
    echo '</ul>';
  }
}

function prevnext($pages = ''){
  global $paged;
  if(empty($paged)) $paged = 1;
  //ページ情報の取得
  if($pages == '') {
    global $wp_query;
    $pages = $wp_query->max_num_pages;
    if(!$pages){
      $pages = 1;
    }
  }
  if(1 != $pages) {
    if($paged == 1){
      echo '<a href="'.get_pagenum_link($paged+1).'" class="arrows__next" >次へ</a>';
    }elseif($paged == $pages){
      echo '<a href="'.get_pagenum_link($paged-1).'" class="arrows__prev" >前へ</a>';
    }else{
      echo '<a href="'.get_pagenum_link($paged-1).'" class="arrows__prev" >前へ</a>';
      echo '<a href="'.get_pagenum_link($paged+1).'" class="arrows__next" >次へ</a>';
    }
  }
}