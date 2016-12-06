<?php
/**
 * Copyright (c) 2014-2016, WebApproach.net
 * All right reserved.
 *
 * @since 2.0.0
 * @package Tint
 * @author Zhiyan
 * @date 2016/08/24 20:49
 * @license GPL v3 LICENSE
 * @license uri http://www.gnu.org/licenses/gpl-3.0.html
 * @link https://www.webapproach.net/tint.html
 */
?>
<?php

/**
 * 根据用户设置选择邮件发送方式
 *
 * @since   2.0.0
 *
 * @param   object  $phpmailer  PHPMailer对象
 * @return  void
 */
function tt_switch_mailer($phpmailer){
    $mailer = tt_get_option('tt_default_mailer');
    if($mailer === 'smtp'){
        $phpmailer->isSMTP();
        $phpmailer->Host = tt_get_option('tt_smtp_host');
        $phpmailer->SMTPAuth = true; // Force it to use Username and Password to authenticate
        $phpmailer->Port = tt_get_option('tt_smtp_port');
        $phpmailer->Username = tt_get_option('tt_smtp_username');
        $phpmailer->Password = tt_get_option('tt_smtp_password');

        // Additional settings…
        $phpmailer->SMTPSecure = tt_get_option('tt_smtp_secure');
        $phpmailer->FromName = tt_get_option('tt_smtp_name');
        //$phpmailer->From = "you@yourdomail.com"; // 多数SMTP提供商要求发信人与SMTP服务器匹配，自定义发件人地址无效
    }else{
        // when use php mail
        $phpmailer->FromName = tt_get_option('tt_mail_custom_sender');
        $phpmailer->From = tt_get_option('tt_mail_custom_address');
    }
}
add_action('phpmailer_init', 'tt_switch_mailer');


/**
 * 发送邮件
 *
 * @since 2.0.0
 *
 * @param string    $from   发件人
 * @param string    $to     收件人
 * @param string    $title  主题
 * @param string|array    $args    渲染内容所需的变量对象
 * @param string    $template   模板，例如评论回复邮件模板、新用户、找回密码、订阅信等模板
 * @return  void
 */
function tt_mail($from, $to, $title = '', $args = array(), $template = 'comment') {
    $title = $title ? trim($title) : tt_get_mail_title($template);
    $content = tt_mail_render($args, $template);
    $blog_name = get_bloginfo('name');
    $sender_name = tt_get_option('tt_mail_custom_sender') || tt_get_option('tt_smtp_name', $blog_name);
    if(empty($from)){
        $from = 'no-reply@' . preg_replace('#^www\.#', '', strtolower($_SERVER['SERVER_NAME'])); //TODO: case e.g subdomain.domain.com
    }

    $fr = "From: \"" . $sender_name . "\" <$from>";
    $headers = "$fr\nContent-Type: text/html; charset=" . get_option('blog_charset') . "\n";
    wp_mail( $to, $title, $content, $headers );
}
add_action('tt_async_send_mail', 'tt_mail', 10, 5);

/**
 * 异步发送邮件
 *
 * @since 2.0.0
 * @param $from
 * @param $to
 * @param string $title
 * @param array $args
 * @param string $template
 */
function tt_async_mail($from, $to, $title = '', $args = array(), $template = 'comment'){
    if(is_array($args)) {
        $args = base64_encode(json_encode($args));
    }
    do_action('send_mail', $from, $to, $title, $args, $template);
}


/**
 * 邮件内容的模板选择处理
 *
 * @since   2.0.0
 *
 * @param   string  $content    未处理的邮件内容或者内容必要参数数组
 * @param   string  $template   渲染模板选择(reset_pass|..)
 * @return  string
 */
function tt_mail_render($content, $template = 'comment') {
    // 使用Plates模板渲染引擎
    $templates = new League\Plates\Engine(THEME_TPL . '/plates/emails');
    if (is_string($content)) {
        return $templates->render('pure', ['content' => $content]);
    } elseif (is_array($content)) {
        return $templates->render($template, $content); // TODO confirm template exist
    }
    return '';
}

/**
 * 不同模板的邮件标题
 *
 * @since   2.0.0
 *
 * @param   string  $template   邮件模板
 * @return  string
 */
function tt_get_mail_title($template = 'comment') {
    $blog_name = get_bloginfo('name');
    switch ($template){
        case 'comment':
            return sprintf(__('New Comment Notification - %s', 'tt'), $blog_name);
            break;
        case 'comment-admin':
            return sprintf(__('New Comment In Your Blog - %s', 'tt'), $blog_name);
            break;
        case 'contribute-post':
            return sprintf(__('New Comment Reply Notification - %s', 'tt'), $blog_name);
            break;
        case 'download':
            return sprintf(__('The Files You Asking For In %s', 'tt'), $blog_name);
            break;
        case 'download-admin':
            return sprintf(__('New Download Request Handled In Your Blog %s', 'tt'), $blog_name);
            break;
        case 'findpass':
            return sprintf(__('New Comment Reply Notification - %s', 'tt'), $blog_name);
            break;
        case 'login':
            return sprintf(__('New Login Event Notification - %s', 'tt'), $blog_name);
            break;
        case 'login-fail':
            return sprintf(__('New Login Fail Event Notification - %s', 'tt'), $blog_name);
            break;
        case 'reply':
            return sprintf(__('New Comment Reply Notification - %s', 'tt'), $blog_name);
            break;
        //TODO more
        default:
            return sprintf(__('Site Internal Notification - %s', 'tt'), $blog_name);
    }
}
