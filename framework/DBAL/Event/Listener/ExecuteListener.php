<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/8/2020
 * Time: 8:53 PM
 */

declare(strict_types=1);

namespace Rid\DBAL\Event\Listener;


use League\Event\AbstractListener;
use League\Event\EventInterface;

class ExecuteListener extends AbstractListener
{
    public function handle(EventInterface $event, $params = null)
    {
        context()->append('record.pdo', $params);
    }
}
