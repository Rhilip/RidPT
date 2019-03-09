<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/2/22
 * Time: 21:46
 *
 * This templates was made by Colorlib (https://colorlib.com)
 * It's origin demo link: https://codepen.io/saransh/pen/aezht
 * And Rewrite By Rhilip
 *
 * @var League\Plates\Template\Template $this
 * @var string $status http status code
 * @var mixed|int $code the exception code as integer in
 * @var string $message the Exception message as a string.
 * @var string $file the filename in which the exception was created.
 * @var int $line the line number where the exception was created.
 * @var string $type the name of the Throwable class
 * @var string $trace the Exception stack trace as a string.
 */

use Rid\Helpers\StringHelper;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php $this->insert('layout/head'); ?>

    <title><?= app()->config->get('base.site_name') ?> :: Error Page -- Powered by <?= app()->config->get('base.site_generator') ?></title>

    <!-- Custom stlylesheet -->
    <link rel="stylesheet" href="/static/css/error.css?<?= app()->config->get('base.site_css_update_date') ?>"/>

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="/lib/html5shiv/dist/html5shiv.min.js"></script>
    <script src="/lib/respond/dest/respond.min.js"></script>
    <![endif]-->
</head>
<body>
<div class="message-img">
    <svg width="380px" height="500px" viewBox="0 0 837 1045" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
        <g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
            <path d="M353,9 L626.664028,170 L626.664028,487 L353,642 L79.3359724,487 L79.3359724,170 L353,9 Z" id="Polygon-1" stroke="#007FB2" stroke-width="6"></path>
            <path d="M78.5,529 L147,569.186414 L147,648.311216 L78.5,687 L10,648.311216 L10,569.186414 L78.5,529 Z" id="Polygon-2" stroke="#EF4A5B" stroke-width="6"></path>
            <path d="M773,186 L827,217.538705 L827,279.636651 L773,310 L719,279.636651 L719,217.538705 L773,186 Z" id="Polygon-3" stroke="#795D9C" stroke-width="6"></path>
            <path d="M639,529 L773,607.846761 L773,763.091627 L639,839 L505,763.091627 L505,607.846761 L639,529 Z" id="Polygon-4" stroke="#F2773F" stroke-width="6"></path>
            <path d="M281,801 L383,861.025276 L383,979.21169 L281,1037 L179,979.21169 L179,861.025276 L281,801 Z" id="Polygon-5" stroke="#36B455" stroke-width="6"></path>
        </g>
    </svg>
    <div class="buttons-con">
        <div class="action-link-wrap" align="center">
            <a onclick="history.back()" class="link-button link-back-button">Go Back</a>
            <a href="/" class="link-button">Go to Home Page</a>
        </div>
    </div>
</div>

<?php
// Get Trace Information
if (env('APP_DEBUG')) {
    $output_trace = $type . ' Code ' . $code . PHP_EOL. PHP_EOL;
    $output_trace .= $file . ' Line ' . $line . PHP_EOL. PHP_EOL;
    $output_trace .= $trace;
} else {
    $output_trace = StringHelper::encrypt($this->data());
}
?>

<div class="message-box">
    <div class="message-info" align="center">
        <h1><?=$status ?></h1>
        <p><?= $status == 404 ? 'Page not found' : (env('APP_DEBUG') ? $message : 'Internal server error') ?></p>
    </div>
    <hr>
    <div class="message-trace">
        <p>Please Report those code to our Sysop Team.</p>
        <div class="textwrapper">
            <label>
                <textarea name="encrypt_error_data" rows="25" onclick="this.focus();this.select()" readonly="readonly"><?= $output_trace ?></textarea>
            </label>
        </div>
    </div>
</div>
</body>
</html>
