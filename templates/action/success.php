<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/2/22
 * Time: 21:36
 *
 * @var League\Plates\Template\Template $this
 * @var string $title
 * @var string $notice
 * @var string $msg
 * @var string $redirect
 */

$redirect = app()->session->get('redirect') ?? $redirect ?? app()->request->getUri();
?>

<?= $this->layout('layout/base'); ?>

<?php $this->start('title'); ?><?= $title ?? 'Action Success'; ?><?php $this->end(); ?>

<?php $this->start('container'); ?>
<!-- Main component for a primary marketing message or call to action -->
<div class="jumbotron">
    <h1><?= $notice ?? 'Action Success'; ?></h1>
    <p>
        <?= nl2br($msg ?? ''); ?><br>
        Please Wait 5 seconds to automatically flush, Or Click <a href="<?=  $this->e($redirect) ?>">HERE</a> to redirect.
    </p>
</div>
<?php $this->end(); ?>

<?php $this->push('script'); ?>
<script>
    setTimeout(function () {
        window.location = '<?=  $this->e($redirect) ?>';
    }, 5e3);
</script>
<?php $this->end(); ?>
