<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/6/15
 * Time: 19:57
 */
?>

<?= $this->layout('layout/base') ?>

<?php $this->start('title')?>Apply for Links<?php $this->end();?>

<?php $this->start('container') ?>
<div class="row">
    <div class="text-center"><h2>Apply for Links</h2></div>
    <div class="col-md-8 col-md-offset-2">
        <div class="panel">
            <div class="panel-heading">Rules for Link Exchange</div>
            <div class="panel-body">
                <ol>
                    <li>Please make our link <?= config('base.site_name') ?> at your site before asking us to do the same.</li>
                    <li>Your site MUST NOT be involed in any illegal things. The administrators of this site OurBits take absolutely no responsibily for anything of your site.</li>
                    <li>All links we make here at our site <?= config('base.site_name') ?> are text-only.</li>
                    <li>Your site should has at least 200 registered users or 50 daily-visiting people.</li>
                    <li>We reserve the rights to MODIFY OR DELETE ANY LINKS at our site <?= config('base.site_name') ?> without notification.</li>
                    <li>If conformed to rules above, feel free to apply for links of your site at OurBits. However, we give no guarantee to accept all application.</li>
                </ol>
            </div>
        </div>

        <div class="panel">
            <div class="panel-heading">Apply Form</div>
            <div class="panel-body">
                <form method="post" class="form-horizontal" data-toggle="validator" role="form">
                    <div class="form-group">
                        <label for="link_name" class="col-sm-2 required">Site Name</label>
                        <div class="col-md-5 col-sm-10">
                            <input type="text" class="form-control" id="link_name" name="link_name" required>
                        </div>
                        <div class="help-block">The name of your site. e.g. <i>RidPT</i></div>
                    </div>
                    <div class="form-group">
                        <label for="link_url" class="col-sm-2 required">URL</label>
                        <div class="col-md-5 col-sm-10">
                            <input type="url" class="form-control" id="link_url" name="link_url" required>
                        </div>
                        <div class="help-block">e.g. <i>https://ridpt.top/</i></div>
                    </div>
                    <div class="form-group">
                        <label for="link_title" class="col-sm-2">Title</label>
                        <div class="col-md-5 col-sm-10">
                            <input type="text" class="form-control" id="link_title" name="link_title">
                        </div>
                        <div class="help-block">Title is used to show tooltip at a link.</div>
                    </div>
                    <div class="form-group">
                        <label for="link_admin" class="col-sm-2 required">Administrator</label>
                        <div class="col-md-5 col-sm-10">
                            <input type="text" class="form-control" id="link_admin" name="link_admin" required>
                        </div>
                        <div class="help-block">We required administrator's <b>TRUE NAME</b>.</div>
                    </div>
                    <div class="form-group">
                        <label for="link_email" class="col-sm-2 required">Email</label>
                        <div class="col-md-5 col-sm-10">
                            <input type="email" class="form-control" id="link_email" name="link_email" required>
                        </div>
                        <div class="help-block">The administrator's contact email address</div>
                    </div>

                    <div class="form-group">
                        <label for="link_reason" class="col-sm-2 required">Reason</label>
                        <div class="col-md-10 col-sm-10">
                            <textarea class="form-control" id="link_reason" name="link_reason" rows="10" placeholder="" required></textarea>
                            <div class="help-block">Provide some evidence of your site.</div>
                        </div>
                    </div>

                    <?= $this->insert('layout/captcha', ['inline' => true]) ?>

                    <div class="text-center">
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>


    </div>
</div>
<?php $this->end();?>

