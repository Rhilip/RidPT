<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/1/2020
 * Time: 2020
 */

declare(strict_types=1);

namespace App\Forms\Auth;


use App\Entity\User\UserFactory;
use App\Libraries\Constant;

use Rid\Libraries\JWT;
use Rid\Validators\AbstractValidator;
use Symfony\Component\Validator\Constraints as Assert;

class LogoutForm extends AbstractValidator
{

    protected function loadInputMetadata(): Assert\Collection
    {
        return new Assert\Collection([]);
    }

    protected function loadCallbackMetaData(): array
    {
        return [];
    }

    public function flush()
    {
        $this->revokeSession();
    }

    private function revokeSession() {
        // Get Session id
        $jwt = container()->get('request')->cookies->get(Constant::cookie_name);
        $payload = container()->get(JWT::class)->decode($jwt);
        $session_id = $payload['jti'];

        if ($session_id) {
            container()->get('response')->headers->clearCookie(Constant::cookie_name);   // Clean cookie
            container()->get('redis')->zAdd(UserFactory::mapUserSessionToId, 0, $session_id);   // Quick Mark this invalid in cache

            // Set this session expired
            container()->get('pdo')->prepare('UPDATE sessions SET `expired` = 1 WHERE session = :sid')->bindParams([
                'sid' => $session_id
            ])->execute();
        }
    }
}
