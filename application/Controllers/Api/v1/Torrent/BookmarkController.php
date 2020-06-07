<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/6/2020
 * Time: 11:02 PM
 */

declare(strict_types=1);

namespace App\Controllers\Api\v1\Torrent;

use App\Forms\Api\v1\Torrents\BookmarkForm;

class BookmarkController
{
    public function takeBookmark()
    {
        $form = new BookmarkForm();
        $form->setInput(container()->get('request')->request->all());
        if ($form->validate()) {
            $form->flush();
            return [
                'success' => true,
                'msg' => $form->getStatusMessage(),
                'result' => $form->getStatus()
            ];
        } else {
            return [
                'success' => false,
                'errors' => $form->getErrors()
            ];
        }
    }
}
