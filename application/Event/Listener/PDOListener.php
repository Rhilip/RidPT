<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 5/10/2020
 * Time: 2020
 */

declare(strict_types=1);

namespace App\Event\Listener;

use League\Event\AbstractListener;
use League\Event\EventInterface;
use Rid\Component\Context;

class PDOListener extends AbstractListener
{
    protected Context $runtime;

    public function __construct(Context $runtime)
    {
        $this->runtime = $runtime;
    }

    public function handle(EventInterface $event, $params = null)
    {
        if (!isset($this->runtime['pdo'])) {
            $this->runtime['pdo'] = [];
        }

        $this->runtime['pdo'][] = $params;
    }
}
