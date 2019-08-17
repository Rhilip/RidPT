<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 8/17/2019
 * Time: 2019
 */
?>

<?= $this->layout('layout/base') ?>

<?php $this->start('title') ?>Subtitle<?php $this->end(); ?>

<?php $this->start('container') ?>
<div class="row">
    <div class="col-md-12">
        <div class="panel-group">
            <div class="panel">
                <div class="panel-heading">General rules - Breaking these rules can and will get you banned!</div>
                <div class="panel-body">
                    <ul>
                        <li>Do not do things we forbid.</li>
                        <li>Do not spam.</li>
                        <li>Cherish your user account. Inactive accounts would be deleted based on the following </li>
                    </ul>
                </div>
            </div>
            <div class="panel">
                <div class="panel-heading">Other rules</div>
                <div class="panel-body">
                    ...
                </div>
            </div>
        </div>
    </div>
</div>
<?php $this->end(); ?>
