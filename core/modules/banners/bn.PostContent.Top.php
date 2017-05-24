<?php
/**
 * Copyright (c) 2014-2017, WebApproach.net
 * All right reserved.
 *
 * @since 2.0.0
 * @package Tint
 * @author Zhiyan
 * @date 2017/03/04 22:23
 * @license GPL v3 LICENSE
 * @license uri http://www.gnu.org/licenses/gpl-3.0.html
 * @link https://webapproach.net/tint.html
 */
?>
<?php if(tt_get_option('tt_enable_post_content_top_banner', false)) { ?>
    <section class="ttgg" id="ttgg-6">
        <div class="tg-inner">
            <?php echo tt_get_option('tt_post_content_top_banner'); ?>
        </div>
    </section>
<?php } ?>