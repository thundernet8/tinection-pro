<?php $this->layout('base', ['blogName' => get_bloginfo('name'), 'logo' => tt_get_option('tt_logo'), 'home' => home_url(), 'shopHome' => tt_url_for('shop_archive')]) ?>

<p>你好! 你的博客空间(<?=$this->e($blogName)?>)有登录!</p>
<p>请确定是您自己的登录失误, 以防别人攻击! 登录信息如下: </p>
<p>登录名: <?=$this->e($loginName)?><p>
<p>登录密码: ****** <p>
<p>登录时间<?php echo date("Y-m-d H:i:s"); ?><p>
<p>登录IP: <?php echo $_SERVER['REMOTE_ADDR'] . '&nbsp;[' . convertip($_SERVER['REMOTE_ADDR']) . ']'; ?><p>