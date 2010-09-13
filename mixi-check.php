<?php
/*
Plugin Name: mixi check plugin for wordpress
Plugin URI: http://github.com/takimo/mixi-check-for-wordpress
Description: mixi check plugin for wordpressはmixiチェックボタンをwordpressに簡単に配置することが出来るプラグインです。
Version: 1.2
Author: Shinya Takimoto
Author URI: http://takimo.net
License: GPL2
*/

/*  Copyright 2010  Shinya Takimoto  (email : shinya.takimoto@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

mb_language("Japanese");
mb_internal_encoding("UTF-8");

define('OGP_PROPERTY_TITLE', 'mixi:title');
define('OGP_PROPERTY_DESCRIPTION', 'mixi:description');
define('MIXI_PROPERTY_CONTENT_RATING', 'mixi:content-rating');
define('MIXI_CONTENT_RATING_NOTSUPPORT', '1');

define('MIXI_NAME_MIXI_CHECK_ROBOTS', 'mixi-check-robots');
define('MIXI_CHECK_ROBOTS_NOTITLE', 'notitle');
define('MIXI_CHECK_ROBOTS_NODESCRIPTION', 'nodescription');
define('MIXI_CHECK_ROBOTS_NOIMAGE', 'noimage');

define('MIXI_SETTINGS_KEY_MIXI_CHECK_KEY', 'mixi-mixicheck:key');
define('MIXI_SETTINGS_KEY_MIXI_CONTENT_RATING', 'mixi:content-rating');
define('MIXI_SETTINGS_KEY_MIXI_CHECK_ROBOTS', 'mixi-mixi-check:robots');
define('MIXI_SETTINGS_KEY_MIXI_CHECK_EXCERPT_LENGTH', 'mixi-mixi-check:excerpt-length');
define('MIXI_SETTINGS_KEY_MIXI_CHECK_BUTTON_TYPE', 'mixi-mixi-check:button-type');

$mixicheck_keys = array(
    MIXI_SETTINGS_KEY_MIXI_CHECK_KEY,
    MIXI_SETTINGS_KEY_MIXI_CONTENT_RATING,
    MIXI_SETTINGS_KEY_MIXI_CHECK_ROBOTS,
    MIXI_SETTINGS_KEY_MIXI_CHECK_EXCERPT_LENGTH,
    MIXI_SETTINGS_KEY_MIXI_CHECK_BUTTON_TYPE
);

function build_mixi_check_headers(){
    $data = array();
    $metadata = get_mixi_check_metadata();
    $rating = get_mixi_content_rating();
    $robots = get_mixi_check_robots();

    if(count($metadata)) $data = array_merge($data, create_meta_property($metadata));
    if(count($rating)) $data = array_merge($data, create_meta_property($rating));
    if(count($robots)) $data = array_merge($data, create_meta_name($robots));

    return implode("\n", $data);
}

function create_meta_property($metadata){
    $metatag = array();
    foreach($metadata as $key => $value){
        if($key == MIXI_SETTINGS_KEY_MIXI_CONTENT_RATING && $value == "on") $value = 1;
        $element =  "<meta property=\"$key\" content=\"".htmlentities($value, ENT_QUOTES, mb_internal_encoding())."\" />";
        $metatag[] = $element;
    }
    return $metatag;
}

function create_meta_name($metadata){
    $metatag = array();
    foreach($metadata as $key => $value){
        $element =  "<meta name=\"$key\" content=\"".htmlentities($value, ENT_QUOTES, mb_internal_encoding())."\" />";
        $metatag[] = $element;
    }
    return $metatag;
}

function get_mixi_check_metadata(){
    $metadata = array();
    $metadata[OGP_PROPERTY_TITLE] =  strip_tags(get_the_title() . " - " . get_bloginfo('name'));
    $metadata[OGP_PROPERTY_DESCRIPTION] = get_the_excerpt_for_multibyte();
    return $metadata;
}

function get_mixi_content_rating(){
    $rating = array();
    $content = get_option(MIXI_SETTINGS_KEY_MIXI_CONTENT_RATING);
    if($content) $rating[MIXI_PROPERTY_CONTENT_RATING] = $content;
    return $rating;
}

function get_mixi_check_robots(){
    $robots = array();
    $content = get_option(MIXI_SETTINGS_KEY_MIXI_CHECK_ROBOTS);
    if($content) $robots[MIXI_NAME_MIXI_CHECK_ROBOTS] = $content;
    return $robots;
}

function mixi_check_add_head(){
    if (!have_posts()) return;

    while (have_posts()){
        the_post();
        echo build_mixi_check_headers();
    }
}

function mixi_check_plugin_menu(){
    add_menu_page('mixiチェック', 'mixiチェック', 8, basename(__file__), '', plugins_url('m_icon.png',__FILE__));
    add_submenu_page(basename(__file__), '設定', '設定', 'manage_options', basename(__file__), 'mixicheck_plugin_options');
    add_submenu_page(basename(__file__), '埋め込みコード', '埋め込みコード', 'manage_options', widget, 'mixicheck_plugin_widget');
}

function mixicheck_plugin_options(){
    global $mixicheck_keys;
    if(get_option(MIXI_SETTINGS_KEY_MIXI_CONTENT_RATING)){
        $content_rating_checked = 'checked="checked"';
    }else{
        $content_rating_checked = '';
    }
    $button_type = get_option(MIXI_SETTINGS_KEY_MIXI_CHECK_BUTTON_TYPE);
    if($button_type){
        if($button_type == "button-1") $button_selected_1 = "selected";
        if($button_type == "button-2") $button_selected_2 = "selected";
        if($button_type == "button-3") $button_selected_3 = "selected";
        if($button_type == "button-4") $button_selected_4 = "selected";
    }

    $html = array(
        '<form method="post" action="options.php">',
        '<input type="hidden" name="action" value="update" />',
        '<input type="hidden" name="page_options" value="'.implode(',',$mixicheck_keys).'" />',
        wp_nonce_field('update-options'),
        '<h2>mixiチェックキー</h2>',
        'mixiの管理ページにて発行されたmixiチェックキーを入力してください',
        '<p><input type="text" name="'.MIXI_SETTINGS_KEY_MIXI_CHECK_KEY.'" value="'.get_option(MIXI_SETTINGS_KEY_MIXI_CHECK_KEY).'" style="width:300px;" /></p>',
        '<h2>Rating</h2>',
        '18 歳未満非対応サイトの場合チェックを入れてください',
        '<p><input type="checkbox" name="'.MIXI_SETTINGS_KEY_MIXI_CONTENT_RATING.'" '.$content_rating_checked.' /></p>',
        '<h2>Robots</h2>',
        '取得されたくない情報を設定します<br />カンマ区切りで入力してください',
        '<dl>',
        '<dt><b>notitle</b></dt>',
        '<dd>タイトルを取得しません</dd>',
        '<dt><b>nodescription</b></dt>',
        '<dd>本文(抜粋)を取得しません</dd>',
        '<dt><b>noimage</b></dt>',
        '<dd>画像を取得しません</dd>',
        'ex.) notitle, nodescription, noimage',
        '<p><input type="text" name="'.MIXI_SETTINGS_KEY_MIXI_CHECK_ROBOTS.'" value="'.get_option(MIXI_SETTINGS_KEY_MIXI_CHECK_ROBOTS).'" style="width:300px;"/></p>',
        '<h2>Excerpt length</h2>',
        '本文の抜粋文字数を設定します<br />全文配信したい場合は何も入力しないでください',
        '<p><input type="text" name="'.MIXI_SETTINGS_KEY_MIXI_CHECK_EXCERPT_LENGTH.'" value="'.get_option(MIXI_SETTINGS_KEY_MIXI_CHECK_EXCERPT_LENGTH).'"/></p>',
        '<h2>Button type</h2>',
        '<p>mixiチェックボタンの表示タイプを選択してください</p>',
        '<ul style="list-style:none;display:inline">',
        '<li style="float:left;">',
        '<p><b>button-1</b></p>',
        '<p><img src="http://img.mixi.jp/img/basic/mixicheck_entry/bt_check_1.png" /></p>',
        '</li>',
        '<li style="float:left;margin-left:15px;">',
        '<p><b>button-2</b></p>',
        '<p><img src="http://img.mixi.jp/img/basic/mixicheck_entry/bt_check_2.png" /></p>',
        '</li>',
        '<li style="float:left;margin-left:15px;">',
        '<p><b>button-3</b><p>',
        '<p><img src="http://img.mixi.jp/img/basic/mixicheck_entry/bt_check_3.png" /></p>',
        '</li>',
        '<li style="float:left;margin-left:15px;">',
        '<p><b>button-4</b></p>',
        '<p><img src="http://img.mixi.jp/img/basic/mixicheck_entry/bt_check_4.png" /></p>',
        '</li>',
        '</ul>',
        '<p style="clear:left">',
        '<select name="'.MIXI_SETTINGS_KEY_MIXI_CHECK_BUTTON_TYPE.'">',
        '<option value="" >----------</option>',
        '<option value="button-1" '.$button_selected_1.' >button-1</option>',
        '<option value="button-2" '.$button_selected_2.' >button-2</option>',
        '<option value="button-3" '.$button_selected_3.' >button-3</option>',
        '<option value="button-4" '.$button_selected_4.' >button-4</option>',
        '</select></p>',
        '<p><input type="submit" class="button-primary" value="Save Changes" /></p>',
        '</form>'
    );
    echo implode('', $html);
}

function mixicheck_plugin_widget(){
    if(get_option(MIXI_SETTINGS_KEY_MIXI_CHECK_KEY)){
        $code = '<?php get_the_mixi_check_button_code(); ?>';
    }else{
        $code = 'エラー：「mixiチェック」→「設定」でmixiチェックキーを設定してください';
    }
    $html = array(
        '<h2>1. &lt;html&gt;を書き換える & wp_head();を記述する</h2>',
        '<p><a href="./theme-editor.php" target="_blank">テーマの編集</a>モードにて以下の修正例のようにヘッダー(header.php)を修正してください。</p>',
        '<b>&lt;html&gt;タグに以下の属性を追加してください。</b><br />',
        '* xmlns:og="http://ogp.me/ns#"<br />',
        '* xmlns:mixi="http://mixi-platform.com/ns#"<br />',
        '<textarea cols="100">',
        '<html xmlns="http://www.w3.org/1999/xhtml" xmlns:og="http://ogp.me/ns#" xmlns:mixi="http://mixi-platform.com/ns#">',
        '</textarea>',
        '<br />',
        '<b>&lt;head&gt;内にwp_headの処理を入れる（既にある場合は追加しない）</b><br />',
        '<textarea cols="50" rows="5">',
        '<head>',
        '<!-- 元からあるタグ -->',
        '<?php wp_head(); ?>',
        '</head>',
        '</textarea>', 
        '<h2>2. mixiチェックボタンを埋め込む</h2>',
        '<p><a href="./theme-editor.php" target="_blank">テーマの編集</a>モードにて単一記事の投稿(single.php)のテーマに埋め込んでください。</a></p>',
        'mixiチェックボタンを埋め込みたいテンプレートの任意の場所に下記のコードを記述して下さい。<br />',
        '記事本文やページ本文に下記のコードを記述しても動きません。<br />',
        '<p>',
        '<textarea cols="50">',
        $code,
        '</textarea>',
        '</p>',
        'ボタンのデザインは「mixiチェック」→「設定」で変更できます。<br />',
        '※クリックしても正常に機能しません。',
        '<p>',
        the_mixi_check_button_code(),
        '</p>',
    );
    echo implode("\n", $html);
}

function get_the_mixi_check_button_code(){
    echo the_mixi_check_button_code();
}

function the_mixi_check_button_code(){
    $data_key = get_option(MIXI_SETTINGS_KEY_MIXI_CHECK_KEY);
    $data_button = get_option(MIXI_SETTINGS_KEY_MIXI_CHECK_BUTTON_TYPE);
    $data_button = $data_button ? $data_button : 'button-1';
    $data_url = get_permalink();

    if(!$data_key) return '<p>API KEYが設定されていません</p>';
    $html = array(
        '<a href="http://mixi.jp/share.pl" class="mixi-check-button" data-key="'.$data_key.'" data-button="'.$data_button.'"data-url="'.$data_url.'">Check</a>',
        '<script type="text/javascript" src="http://static.mixi.jp/js/share.js"></script>',
    );
    return implode('', $html);
}

function get_the_excerpt_for_multibyte(){
    $text = get_the_content();
    $text = strip_tags($text);
    $text = mb_ereg_replace("\n", "", $text);
    $text = mb_ereg_replace("\r\n", "", $text);
    $text = mb_ereg_replace("\r", "", $text);

    $except_length = get_option(MIXI_SETTINGS_KEY_MIXI_CHECK_EXCERPT_LENGTH);
    if(mb_strlen($text)> $except_length && $except_length != null){
        $text = mb_substr($text, 0, $except_length, mb_detect_encoding($text)) . "...";
    }
    return $text;
}

add_action('wp_head', 'mixi_check_add_head');
add_action('admin_menu', 'mixi_check_plugin_menu');
?>
