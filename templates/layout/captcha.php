<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/3/9
 * Time: 16:49
 *
 *
 */

$inline = $inline ?? false;
?>

<div class="form-group">
    <label for="captcha" class="<?= $inline ? 'col-sm-2 ' : '' ?>required">Captcha</label>
    <div class="row">
        <div class="col-xs-6 col-md-4">
            <div class="input-group">
                <span class="input-group-addon"><span class="fas fa-sync-alt fa-fw"></span></span>
                <input type="text" class="form-control" id="captcha" name="captcha" maxlength="6"
                       required autocomplete="off">
            </div>
            <div class="help-block">Case insensitive.</div>
        </div>
        <div class="col captcha_img_load">
            <img class="captcha_img" src="/static/pic/captcha_dummy.png" alt="Captcha Image"
                 data-toggle="tooltip" data-placement="right" title="Click to Refresh">
        </div>
    </div>
</div>
