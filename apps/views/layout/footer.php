<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/3/8
 * Time: 15:11
 *
 * @var League\Plates\Template\Template $this
 */
?>

<footer id="footer-menu">
    <div class="container" align="center">
        <div class="row">
            <p class="copyright">
                <a href="/" target="_self"><?= app()->config->get('base.site_name') ?></a> 2019-2020 Powered by <a href="https://github.com/Rhilip/RidPT">RidPT</a>
            </p>
            <p class="create-info">[ Page created in <b>{{ cost_time|number_format(5) }}</b> sec with <b><?= count(app()->pdo->getExecuteData()) ?></b> db queries, <b><?= array_sum(app()->redis->getCalledData())?></b> calls of Redis ]</p>
        </div>
    </div>
</footer>
