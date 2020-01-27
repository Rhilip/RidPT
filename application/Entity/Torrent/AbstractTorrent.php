<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 1/26/2020
 * Time: 2020
 */

declare(strict_types=1);

namespace App\Entity\Torrent;

use Rid\Base\BaseObject;

class AbstractTorrent extends BaseObject implements AbstractTorrentInterface
{





    public function __construct($id = null, $config = [])
    {


        parent::__construct($config);
    }
}
