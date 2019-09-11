<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 8/7/2019
 * Time: 10:07 PM
 */

namespace App\Models\Form\Traits;


trait FileSentTrait
{

    use actionRateLimitCheckTrait;

    protected function getSendFileName(): string
    {
        return static::$SEND_FILE_NAME ?? 'file';
    }

    protected function getSendFileContentLength(): int
    {
        return static::$SEND_FILE_CONTENT_LENGTH ?? 0;
    }

    protected function getSendFileContentType(): string
    {
        return static::$SEND_FILE_CONTENT_TYPE ?? 'application/octet-stream';
    }

    protected function getSendFileCacheControlStatus(): bool
    {
        return static::$SEND_FILE_CACHE_CONTROL ?? false;
    }

    final private function setRespHeaders()
    {
        if ($this->getSendFileCacheControlStatus()) {
            app()->response->setHeader('Cache-Control', 'no-cache, no-store, must-revalidate');
            app()->response->setHeader('Pragma', 'no-cache');
            app()->response->setHeader('Expires', '0');
        }

        app()->response->setHeader('Content-Type', $this->getSendFileContentType());
        if ($this->getSendFileContentLength() != 0)
            app()->response->setHeader('Content-Length', $this->getSendFileContentLength());

        $filename = $this->getSendFileName();
        if (strpos(app()->request->header('user-agent'), 'IE')) {
            app()->response->setHeader('Content-Disposition', 'attachment; filename=' . str_replace('+', '%20', rawurlencode($filename)));
        } else {
            app()->response->setHeader('Content-Disposition', "attachment; filename=\"$filename\" ; charset=utf-8");
        }
    }

    abstract protected function getSendFileContent();

    protected function hookFileContentSend()
    {
    }

    final public function sendFileContentToClient()
    {
        $this->hookFileContentSend();
        $this->setRespHeaders();
        return $this->getSendFileContent();
    }
}
