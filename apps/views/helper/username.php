<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 8/1/2019
 * Time: 10:57 AM
 *
 * @var League\Plates\Template\Template $this
 * @var \apps\models\User $user
 * @var \apps\models\Torrent $torrent
 */

$torrent = $torrent ?? false;
$user_name_hide = $user_name_hide ?? ($torrent && $torrent->getUplver());
$user_badge = $user_badge ?? false;
?>
<?php if ($user_name_hide): ?>
    <i>Anonymous</i>
    <?php if (app()->site->getCurUser()->isPrivilege('see_anonymous_uploader')): ?>
        (<?= $this->insert('helper/username', ['user' => $user, 'user_name_hide' => false, 'user_badge' => $user_badge]) ?>)
    <?php endif; ?>
<?php else: ?>
    <a href="/user?id=<?= $user->getId() ?>"><?= $user->getUsername() ?></a>
    <?php if ($user_badge): ?>
    <!-- TODO -->
    <?php endif; ?>
<?php endif; ?>
