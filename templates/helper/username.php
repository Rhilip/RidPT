<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 8/1/2019
 * Time: 10:57 AM
 *
 * @var League\Plates\Template\Template $this
 * @var App\Entity\User\User $user
 */

$hide = $hide ?? false;
$show_badge = $show_badge ?? false;
?>
<?php if ($user === false): // User is not exist?>
    <s>(orphaned)</s>
<?php elseif ($hide): // User in hide status?>
    <i>Anonymous</i>
    <?php if (app()->auth->getCurUser()->isPrivilege('see_anonymous_info')): ?>
        (<?= $this->insert('helper/username', ['user' => $user, 'hide' => false, 'user_badge' => $show_badge]) ?>)
    <?php endif; ?>
<?php else: ?>
    <a href="/user?id=<?= $user->getId() ?>"><?= $user->getUsername() ?></a>
    <?php if ($show_badge): ?>
    <!-- TODO -->
    <?php endif; ?>
<?php endif; ?>
