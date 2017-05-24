<?php
/**
 * Copyright (c) 2014-2016, WebApproach.net
 * All right reserved.
 *
 * @since 2.0.0
 * @package Tint
 * @author Zhiyan
 * @date 2016/12/25 16:21
 * @license GPL v3 LICENSE
 * @license uri http://www.gnu.org/licenses/gpl-3.0.html
 * @link https://webapproach.net/tint.html
 */
?>
<?php

/* 引入常量 */
require_once 'Constants.php';

/* 设置默认时区 */
date_default_timezone_set('PRC');

if(!function_exists('load_dash')){
    function load_dash($path){
        load_template(THEME_DIR.'/dash/'.$path.'.php');
    }
}

if(!function_exists('load_api')){
    function load_api($path){
        load_template(THEME_DIR.'/core/api/'.$path.'.php');
    }
}

if(!function_exists('load_class')){
    function load_class($path, $safe = false){
        if($safe) {
            @include_once(THEME_DIR.'/core/classes/'.$path.'.php');
        }else{
            load_template(THEME_DIR.'/core/classes/'.$path.'.php');
        }
    }
}

if(!function_exists('load_func')){
    function load_func($path, $safe = false){
        if($safe){
            @include_once(THEME_DIR.'/core/functions/'.$path.'.php');
        }else{
            load_template(THEME_DIR.'/core/functions/'.$path.'.php');
        }
    }
}

if(!function_exists('load_mod')){
    function load_mod($path, $safe = false){
        if($safe) {
            @include_once(THEME_DIR.'/core/modules/'.$path.'.php');
        }else{
            load_template(THEME_DIR.'/core/modules/'.$path.'.php');
        }
    }
}

if(!function_exists('load_widget')){
    function load_widget($path, $safe = false){
        if($safe) {
            @include_once(THEME_DIR.'/core/modules/widgets/'.$path.'.php');
        }else{
            load_template(THEME_DIR.'/core/modules/widgets/'.$path.'.php');
        }
    }
}

if(!function_exists('load_vm')){
    function load_vm($path, $safe = false){
        if($safe) {
            @include_once(THEME_DIR.'/core/viewModels/'.$path.'.php');
        }else{
            load_template(THEME_DIR.'/core/viewModels/'.$path.'.php');
        }
    }
}

/* 载入option_framework */
load_dash('of_inc/options-framework');

/* 载入主题选项 */
load_dash('options');

defined('THEME_CDN_ASSET') || define('THEME_CDN_ASSET', of_get_option('tt_tint_static_cdn_path', THEME_ASSET));

/* 调试模式选项保存为全局变量 */
defined('TT_DEBUG') || define('TT_DEBUG', of_get_option('tt_theme_debug', false));
if(TT_DEBUG) {
    ini_set("display_errors","On");
    error_reporting(E_ALL);
}else{
    ini_set("display_errors","Off");
}

/* 载入后台相关处理逻辑 */
//if( is_admin() ){
load_dash('dash');
//}

/* 载入REST API功能控制函数 */
load_api('api.Config');

/* 载入功能函数 */
load_func('func.L10n');
load_func('func.Account');
load_func('func.Avatar');
load_func('func.Cache');
load_func('func.Comment');
load_func('func.Init');
load_func('func.Install');
load_func('func.Kits');
load_func('func.Mail');
load_func('func.Metabox');
load_func('func.Module');
load_func('func.Optimization');
load_func('func.Page');
load_func('func.PostMeta');
load_func('func.Rewrite');
load_func('func.Robots');
load_func('func.Schedule');
load_func('func.Script');
load_func('func.Seo');
load_func('func.Sidebar');
load_func('func.Template');
load_func('func.Thumb');
load_func('func.User');
load_func('func.Content');
load_func('func.Follow');
load_func('func.Message');
load_func('func.Referral');
load_func('func.Query');
load_func('func.Credit');
load_func('func.Member');
load_func('func.IP');
load_func('func.ShortCode');
load_func('func.Download');
load_func('func.Image');
load_func('func.Oauth');
load_func('func.API.Actions');
load_func('func.Tint');
if(TT_PRO && tt_get_option('tt_enable_shop', false)){
    load_func('shop/func.Shop');
    load_func('shop/func.Shop.Order');
    load_func('shop/func.Shop.Coupon');
    load_func('shop/func.Shop.Cart');
    load_func('shop/func.Shop.Address');
    load_func('shop/alipay/func.Alipay');
}
load_func('func.Bulletin');

/* 载入类 */
load_class('class.Avatar');
load_class('class.Captcha');
load_class('class.Open');
load_class('class.PostImage');
load_class('class.Utils');
load_class('class.Member');
load_class('class.Async.Task');
load_class('class.Async.Email');
load_class('class.Enum');
// Plates模板引擎
load_class('plates/Engine');
load_class('plates/Extension/ExtensionInterface');
load_class('plates/Template/Data');
load_class('plates/Template/Directory');
load_class('plates/Template/FileExtension');
load_class('plates/Template/Folder');
load_class('plates/Template/Folders');
load_class('plates/Template/Func');
load_class('plates/Template/Functions');
load_class('plates/Template/Name');
load_class('plates/Template/Template');
load_class('plates/Extension/Asset');
load_class('plates/Extension/URI');

if(is_admin()) {
    load_class('class.Tgm.Plugin.Activation');
}
if(TT_PRO && tt_get_option('tt_enable_shop', false)){
    load_class('shop/class.Product');
    load_class('shop/class.OrderStatus');
    load_class('shop/alipay/alipay_notify.class');
    load_class('shop/alipay/alipay_submit.class');
}

/* 载入数据模型 */
load_vm('vm.Base');
load_vm('vm.Home.Slides');
load_vm('vm.Home.Popular');
load_vm('vm.Stickys');
load_vm('vm.Home.Latest');
load_vm('vm.Home.FeaturedCategory');
load_vm('vm.Single.Post');
load_vm('vm.Single.Page');
load_vm('vm.Post.Comments');
load_vm('vm.Category.Posts');
load_vm('vm.Tag.Posts');
load_vm('vm.Date.Archive');
load_vm('vm.Term.Posts');
load_vm('widgets/vm.Widget.Author');
load_vm('widgets/vm.Widget.HotHit.Posts');
load_vm('widgets/vm.Widget.HotReviewed.Posts');
load_vm('widgets/vm.Widget.Recent.Comments');
load_vm('widgets/vm.Widget.Latest.Posts');
load_vm('uc/vm.UC.Latest');
load_vm('uc/vm.UC.Stars');
load_vm('uc/vm.UC.Comments');
load_vm('uc/vm.UC.Followers');
load_vm('uc/vm.UC.Following');
load_vm('uc/vm.UC.Chat');
load_vm('uc/vm.UC.Profile');
load_vm('me/vm.Me.Settings');
load_vm('me/vm.Me.Credits');
load_vm('me/vm.Me.Drafts');
load_vm('me/vm.Me.Messages');
load_vm('me/vm.Me.Notifications');
load_vm('me/vm.Me.EditPost');
load_vm('vm.Search');
if(TT_PRO && tt_get_option('tt_enable_shop', false)){
    load_vm('shop/vm.Shop.Header.SubNav');
    load_vm('shop/vm.Shop.Home');
    load_vm('shop/vm.Shop.Category');
    load_vm('shop/vm.Shop.Tag');
    load_vm('shop/vm.Shop.Search');
    load_vm('shop/vm.Shop.Product');
    load_vm('shop/vm.Shop.Comment');
    load_vm('shop/vm.Shop.LatestRated');
    load_vm('shop/vm.Shop.ViewHistory');
    load_vm('shop/vm.Embed.Product');
}
load_vm('bulletin/vm.Bulletin');
load_vm('bulletin/vm.Bulletins');

if(TT_PRO){
    load_vm('me/vm.Me.Order');
    load_vm('me/vm.Me.Orders');
    load_vm('me/vm.Me.Membership');
    load_vm('management/vm.Mg.Status');
    load_vm('management/vm.Mg.Comments');
    load_vm('management/vm.Mg.Coupons');
    load_vm('management/vm.Mg.Members');
    load_vm('management/vm.Mg.Orders');
    load_vm('management/vm.Mg.Order');
    load_vm('management/vm.Mg.Posts');
    load_vm('management/vm.Mg.Users');
    load_vm('management/vm.Mg.User');
    load_vm('management/vm.Mg.Products');
}

/* 载入小工具 */
load_widget('wgt.TagCloud');
load_widget('wgt.Author');
load_widget('wgt.HotHits.Posts');
load_widget('wgt.HotReviews.Posts');
load_widget('wgt.RecentComments');
load_widget('wgt.Latest.Posts');
load_widget('wgt.UC');
load_widget('wgt.Float');
load_widget('wgt.EnhancedText');
load_widget('wgt.Donate');

/* 实例化异步任务类实现注册异步任务钩子 */
new AsyncEmail();