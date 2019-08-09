<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 8/1/2019
 * Time: 10:57 AM
 *
 * @var League\Plates\Template\Template $this
 * @var apps\models\User $user
 */

$hide = $hide ?? false;
$show_badge = $show_badge ?? false;
?>
<?php if ($hide): ?>
    <i>Anonymous</i>
    <?php if (app()->site->getCurUser()->isPrivilege('see_anonymous_info')): ?>
        (<?= $this->insert('helper/username', ['user' => $user, 'user_name_hide' => false, 'user_badge' => $show_badge]) ?>)
    <?php endif; ?>
<?php else: ?>
    <a href="/user?id=<?= $user->getId() ?>"><?= $user->getUsername() ?></a>
    <?php if ($show_badge): ?>
    <!-- TODO -->
    <?php endif; ?>
<?php endif; ?>
