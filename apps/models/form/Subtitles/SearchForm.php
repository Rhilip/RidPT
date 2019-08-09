<?php
/** TODO
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 8/7/2019
 * Time: 10:03 PM
 */

namespace apps\models\form\Subtitles;

use Rid\Validators\Pager;

class SearchForm extends Pager
{

    protected function getRemoteTotal(): int
    {
        return app()->pdo->createCommand('SELECT COUNT(`id`) FROM `subtitles`')->queryScalar();  // TODO
    }

    protected function getRemoteData(): array
    {
       return app()->pdo->createCommand('SELECT * FROM `subtitles` ORDER BY id DESC')->queryAll();  // TODO
    }

    public function getSubsSizeSum()
    {
        if (false === $size = app()->redis->get('Site:subtitle_sub_size:string')) {
            $size = app()->pdo->createCommand('SELECT SUM(`size`) FROM `subtitles`')->queryScalar();
            app()->redis->set('Site:subtitle_sub_size:string', $size);
        }
        return $size;
    }

}
