<?php
/**
 * Copyright (c) 2014-2016, WebApproach.net
 * All right reserved.
 *
 * @since 2.0.0
 * @package Tint
 * @author Zhiyan
 * @date 2016/08/21 14:23
 * @license GPL v3 LICENSE
 * @license uri http://www.gnu.org/licenses/gpl-3.0.html
 * @link https://webapproach.net/tint.html
 */
?>
<?php

/**
 * Cache 封装
 *
 * @since   2.0.0
 *
 * @access  public
 * @param   string      $key        缓存键
 * @param   callable    $miss_cb    未命中缓存时的回调函数
 * @param   string      $group      缓存数据分组
 * @param   int         $expire     缓存时间，单位为秒
 * @return  mixed
 */
function tt_cached($key, $miss_cb, $group, $expire){
    // 无memcache等对象缓存组件时，使用临时的数据表缓存，只支持string|int内容 // 实际上get_transient|set_transient会自动判断有无wp-content下的object-cache.php， 但是直接使用该函数不支持group
    // https://core.trac.wordpress.org/browser/tags/4.5.3/src/wp-includes/option.php#L609
    if(tt_get_option('tt_object_cache', 'none')=='none' && !TT_DEBUG){
        $data = get_transient($key);
        if($data!==false){
            return $data;
        }
        if(is_callable($miss_cb)){
            $data = call_user_func($miss_cb);
            if(is_string($data) || is_int($data)) set_transient($key, $data, $expire);
            return $data;
        }
        return false;
    }
    // 使用memcache或redis内存对象缓存
    elseif(in_array(tt_get_option('tt_object_cache', 'none'), array('memcache', 'redis')) && !TT_DEBUG){
        $data = wp_cache_get($key, $group);
        if($data!==false){
            return $data;
        }
        if(is_callable($miss_cb)){
            $data = call_user_func($miss_cb);
            wp_cache_set($key, $data, $group, $expire);
            return $data;
        }
        return false;
    }
    return is_callable($miss_cb) ? call_user_func($miss_cb) : false;
}


/**
 * 定时清理大部分缓存(每小时)
 *
 * @since   2.0.0
 * @return  void
 */
function tt_cache_flush_hourly(){
    // Object Cache
    wp_cache_flush();

    // Transient cache
    // transient的缓存在get_transient函数调用时会自动判断过期并决定是否删除，对于每个查询请求执行两次delete_option操作
    // 这里采用定时任务一次性删除多条隔天的过期缓存，减少delete_option操作
    global $wpdb;
    $wpdb->query( "DELETE FROM $wpdb->options WHERE `option_name` LIKE '_transient_tt_cache_hourly_%' OR `option_name` LIKE '_transient_timeout_tt_cache_hourly%'" );
}
add_action('tt_setup_common_hourly_event', 'tt_cache_flush_hourly');


/**
 * 定时清理大部分缓存(每天执行)
 *
 * @since   2.0.0
 * @return  void
 */
function tt_cache_flush_daily(){
    // Rewrite rules Cache
    global $wp_rewrite;
    $wp_rewrite->flush_rules();

    // Transient cache
    global $wpdb;
    $wpdb->query( "DELETE FROM $wpdb->options WHERE `option_name` LIKE '_transient_tt_cache_daily_%' OR `option_name` LIKE '_transient_timeout_tt_cache_daily_%'" );
}
add_action('tt_setup_common_daily_event', 'tt_cache_flush_daily');


/**
 * 定时清理大部分缓存(每周)
 *
 * @since   2.0.0
 * @return  void
 */
function tt_cache_flush_weekly(){
    // Object Cache
    wp_cache_flush();

    // Transient cache
    global $wpdb;
    $wpdb->query( "DELETE FROM $wpdb->options WHERE `option_name` LIKE '_transient_tt_cache_weekly_%' OR `option_name` LIKE '_transient_timeout_tt_cache_weekly%'" );
}
add_action('tt_setup_common_weekly_event', 'tt_cache_flush_weekly');  // TODO rest api cache


/**
 * 清除所有缓存
 *
 * @since   2.0.0
 * @return  void
 */
function tt_clear_all_cache() {
    // Object Cache
    wp_cache_flush();

    // Rewrite rules Cache
    global $wp_rewrite;
    $wp_rewrite->flush_rules();

    // Transient cache
    // transient的缓存在get_transient函数调用时会自动判断过期并决定是否删除，对于每个查询请求执行两次delete_option操作
    // 这里采用定时任务一次性删除多条隔天的过期缓存，减少delete_option操作
    global $wpdb;
    $wpdb->query( "DELETE FROM $wpdb->options WHERE `option_name` LIKE '_transient_tt_cache_%' OR `option_name` LIKE '_transient_timeout_tt_cache_%'" );
}


/**
 * 模糊匹配键值删除transient的缓存
 *
 * @since 2.0.0
 * @param $key
 */
function tt_clear_cache_key_like($key) {
    if(wp_using_ext_object_cache()) {
        return; //object cache无法模糊匹配key
    }
    global $wpdb;
    $like1 = '_transient_' . $key . '%';
    $like2 = '_transient_timeout_' . $key . '%';
    $wpdb->query( $wpdb->prepare("DELETE FROM $wpdb->options WHERE `option_name` LIKE %s OR `option_name` LIKE %s", $like1, $like2) );
}


/**
 * 精确匹配键值删除transient的缓存(包括Object Cache)
 *
 * @since 2.0.0
 * @param $key
 */
function tt_clear_cache_by_key($key) { //use delete_transient
    if(wp_using_ext_object_cache()){
        wp_cache_delete($key, 'transient'); // object cache是由set_transient时设置的, group为transient
    }else{
        global $wpdb;
        $key1 = '_transient_' . $key;
        $key2 = '_transient_timeout_' . $key;
        $wpdb->query( $wpdb->prepare("DELETE FROM $wpdb->options WHERE `option_name` IN ('%s','%s')", $key1, $key2) );
    }
}



/**
 * 预读取菜单时寻找缓存
 *
 * @since   2.0.0
 * @param   string  $menu   导航菜单
 * @param   array   $args   菜单参数
 * @return  string
 */
function tt_cached_menu($menu, $args){
    if(TT_DEBUG) {
        return $menu;
    }

    global $wp_query;
    // 即使相同菜单位但是不同页面条件时菜单输出有细微区别，如当前active的子菜单, 利用$wp_query->query_vars_hash予以区分
    $cache_key = CACHE_PREFIX . '_hourly_nav_' . md5($args->theme_location . '_' . $wp_query->query_vars_hash);
    $cached_menu = get_transient($cache_key); //TODO： 尝试Object cache
    if($cached_menu !== false){
        return $cached_menu;
    }
    return $menu;
}
add_filter('pre_wp_nav_menu', 'tt_cached_menu', 10, 2);


/**
 * 读取菜单完成后设置缓存(缓存命中的菜单读取不会触发该动作)
 *
 * @since   2.0.0
 *
 * @param   string  $menu   导航菜单
 * @param   array   $args   菜单参数
 * @return  string
 */
function tt_cache_menu($menu, $args){
    if(TT_DEBUG) {
        return $menu;
    }

    global $wp_query;
    $cache_key = CACHE_PREFIX . '_hourly_nav_' . md5($args->theme_location . '_' . $wp_query->query_vars_hash);
    set_transient($cache_key, sprintf(__('<!-- Nav cached %s -->', 'tt'), current_time('mysql')) . $menu . __('<!-- Nav cache end -->', 'tt'), 3600);
    return $menu;
}
add_filter('wp_nav_menu', 'tt_cache_menu', 10 ,2);


/**
 * 设置更新菜单时主动删除缓存
 *
 */
function tt_delete_menu_cache(){
    global $wpdb;
    $wpdb->query("DELETE FROM $wpdb->options WHERE `option_name` LIKE '_transient_tt_cache_hourly_nav_%' OR `option_name` LIKE '_transient_timeout_tt_cache_hourly_nav_%'");

    //TODO: 如果使用object cache则wp_cache_flush()
    if(wp_using_ext_object_cache()){
        wp_cache_flush();
    }
}
add_action('wp_update_nav_menu', 'tt_delete_menu_cache');

//TODO 其他工具利用apply_filters do_action add_filter add_action调用或生成或删除缓存

/**
 * 文章点赞或取消赞时删除对应缓存
 *
 * @since   2.0.0
 * @param   int $post_ID
 * @return  void
 */
function tt_clear_cache_for_stared_or_unstar_post($post_ID) {
    $cache_key = 'tt_cache_daily_vm_SinglePostVM_post' . $post_ID;
    delete_transient($cache_key);
}
add_action('tt_stared_post', 'tt_clear_cache_for_stared_or_unstar_post', 10 , 1);
add_action('tt_unstared_post', 'tt_clear_cache_for_stared_or_unstar_post', 10, 1);


/**
 * 文章点赞或取消赞时删除对应用户的UC Stars缓存
 *
 * @since   2.0.0
 * @param   int $post_ID
 * @param   int $author_id
 * @return  void
 */
function tt_clear_cache_for_uc_stars($post_ID, $author_id) {
    $cache_key = 'tt_cache_daily_vm_UCStarsVM_author' . $author_id . '_page'; //模糊键值
    //delete_transient($cache_key);
    tt_clear_cache_key_like($cache_key);
    tt_clear_cache_by_key($cache_key . '1');
}
add_action('tt_stared_post', 'tt_clear_cache_for_uc_stars', 10 , 2);
add_action('tt_unstared_post', 'tt_clear_cache_for_uc_stars', 10, 2);


/**
 * 订单状态变更时删除相关缓存
 *
 * @since   2.0.0
 * @param   int $order_id
 * @return  void
 */
function tt_clear_cache_for_order_relates($order_id) {
    $order = tt_get_order($order_id);
    if(!$order) return;

    //Product VM
    delete_transient(sprintf('tt_cache_daily_vm_ShopProductVM_product%1$s_user%2$s', $order->product_id, $order->user_id));
    //Order Detail VM
    delete_transient(sprintf('tt_cache_daily_vm_MeOrderVM_user%1$s_seq%2$s', $order->user_id, $order->id));
    //Orders VM
    delete_transient(sprintf('tt_cache_daily_vm_MeOrdersVM_user%1$s_typeall', $order->user_id));
    delete_transient(sprintf('tt_cache_daily_vm_MeOrdersVM_user%1$s_typecash', $order->user_id));
    delete_transient(sprintf('tt_cache_daily_vm_MeOrdersVM_user%1$s_typecredit', $order->user_id));
}
add_action('tt_order_status_change', 'tt_clear_cache_for_order_relates');


/**
 * 保存文章时清理相关缓存
 *
 * @since 2.0.0
 * @param $post_id
 * @return void
 */
function tt_clear_cache_for_post_relates($post_id) {
    $post_type = get_post_type($post_id);

    if($post_type == 'post'){
        // 文章本身
        delete_transient(sprintf('tt_cache_daily_vm_SinglePostVM_post%1$s', $post_id));
        // 文章列表
        delete_transient('tt_cache_daily_vm_HomeLatestVM_page1');
        // 分类列表
        // TODO
    }elseif($post_type == 'page'){
        // 页面本身
        delete_transient(sprintf('tt_cache_daily_vm_SinglePageVM_page%1$s', $post_id));
    }elseif($post_type == 'product'){
        // 商品本身 //与用户id相关的cache key 只能缩短缓存时间自动过期

        // 商品列表
        delete_transient('tt_cache_daily_vm_ShopHomeVM_page1_sort_latest');
        delete_transient('tt_cache_daily_vm_ShopHomeVM_page1_sort_popular');
    }
}
add_action('save_post', 'tt_clear_cache_for_post_relates');


/**
 * 输出小工具前尝试检索缓存
 *
 * @since 2.0.0
 * @param $value
 * @param $type
 * @return string|bool
 */
function tt_retrieve_widget_cache($value, $type) {
    if(tt_get_option('tt_theme_debug', false)) {
        return false;
    }

    $cache_key = CACHE_PREFIX . '_daily_widget_' . $type;
    $cache = get_transient($cache_key);
    return $cache;
}
add_filter('tt_widget_retrieve_cache', 'tt_retrieve_widget_cache', 10 ,2);


/**
 * 将查询获得的小工具的结果缓存
 *
 * @since 2.0.0
 * @param $value
 * @param $type
 * @param $expiration
 * @return void
 */
function tt_create_widget_cache($value, $type, $expiration = 21600) {  // 21600 = 3600*6
    $cache_key = CACHE_PREFIX . '_daily_widget_' . $type;
    $value = '<!-- Widget cached ' . current_time('mysql') . ' -->' . $value;
    set_transient($cache_key, $value, $expiration);
}
add_action('tt_widget_create_cache', 'tt_create_widget_cache', 10, 2);


/**
 * 配置Object Cache服务器
 *
 * @since 2.0.0
 */
function tt_init_object_cache_server(){
    if(of_get_option('tt_object_cache', 'none') == 'memcache') {
        global $memcached_servers;
        $host = of_get_option('tt_memcache_host', '127.0.0.1');
        $port = of_get_option('tt_memcache_port', 11211);
        $memcached_servers[] = $host . ':' . $port;
    }elseif(of_get_option('tt_object_cache', 'none') == 'redis') {
        global $redis_server;
        $redis_server['host'] = of_get_option('tt_redis_host', '127.0.0.1');
        $redis_server['port'] = of_get_option('tt_redis_port', 6379);
    }
}
tt_init_object_cache_server();