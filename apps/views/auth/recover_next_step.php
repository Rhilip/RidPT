<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/6/4
 * Time: 21:42
 */
?>

<?= $this->layout('auth/base') ?>

<?php $this->start('container') ?>
<div class="row">
    <div class="col-md-7 col-md-offset-3">
        <div class="panel">
            <div class="panel-heading">Find the email~</div>
            <div class="panel-body">
                We will send your a email contains the username and a recover link to reset your password if your emails exist in our site.
                Check your email (Sometimes in Trash can), and be patient that the email may delay for some minutes.
                If you don't receive for long minutes or meet other problems, Please contact with our group ASAP.
            </div>
        </div>
    </div>
</div>
<?php $this->end(); ?>
