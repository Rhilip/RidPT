<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 8/7/2019
 * Time: 10:07 PM
 */

namespace apps\models\form\Traits;


trait FileDownloadTrait
{

    protected function getSendFileName(): string
    {
        return static::$SEND_FILE_NAME ?? 'file';
    }

    protected function getContentType(): string
    {
        return static::$SEND_FILE_CONTENT_TYPE ?? 'application/octet-stream';
    }

    final private function setRespHeaders()
    {
        $filename = $this->getSendFileName();
        app()->response->setHeader('Content-Type', $this->getContentType());

        if (strpos(app()->request->header('user-agent'), 'IE')) {
            app()->response->setHeader('Content-Disposition', 'attachment; filename=' . str_replace('+', '%20', rawurlencode($filename)));
        } else {
            app()->response->setHeader('Content-Disposition', "attachment; filename=\"$filename\" ; charset=utf-8");
        }
    }

    abstract protected function getSendFileContent();

    public function sendFileContentToClient()
    {
        $this->setRespHeaders();
        return $this->getSendFileContent();
    }
}
