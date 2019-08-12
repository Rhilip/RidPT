<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 8/7/2019
 * Time: 10:02 PM
 */

namespace apps\models\form\Subtitles;


use apps\libraries\Constant;
use apps\models\form\Traits\isValidTorrentTrait;
use Rid\Http\UploadFile;
use Rid\Validators\Validator;

class UploadForm extends Validator
{
    use isValidTorrentTrait;

    /** @var UploadFile $file */
    public $file;

    public $title;
    public $torrent_id;
    // public $lang_id;  //TODO support
    public $anonymous;

    private $hashs;

    protected $_autoload_data = true;
    protected $_autoload_data_from = ['post', 'files'];

    const SubtitleExtension = ['sub', 'srt', 'zip', 'rar', 'ace', 'txt', 'ssa', 'ass', 'cue'];

    public static function defaultData()
    {
        return [
            'anonymous' => 0
        ];
    }

    public static function inputRules()
    {
        return [
            'torrent_id' => 'Required | Integer',
            'file' => [
                ['Required'],
                ['Upload\Required'],
                ['Upload\Extension', ['allowed' => static::SubtitleExtension]],
                ['Upload\Size', ['size' => config('upload.max_subtitle_file_size') . 'B']]
            ],
            'anonymous' => [
                ['InList', ['list' => [0, 1]]]
            ]
        ];
    }

    public static function callbackRules()
    {
        return ['isExistTorrent', 'checkSubtitleUniqueByHash'];
    }

    protected function checkSubtitleUniqueByHash()
    {
        /** @var UploadFile $file */
        $file = $this->getData('file');
        $this->hashs = $file_md5 = md5_file($file->tmpName);

        $exist_id = app()->pdo->createCommand('SELECT id FROM `subtitles` WHERE `hashs` = :hashs LIMIT 1;')->bindParams([
            'hashs' => $file_md5
        ])->queryOne();

        if ($exist_id !== false) {
            $this->buildCallbackFailMsg('file', 'This Subtitle has been upload before.');
        }
    }

    /**
     * @throws \Exception
     */
    public function flush()
    {
        $title = $this->title ?: $this->file->getFileName();

        app()->pdo->beginTransaction();
        try {
            $ext = $this->file->getExtension();
            app()->pdo->createCommand('INSERT INTO `subtitles`(`torrent_id`, `hashs` ,`title`, `filename`, `added_at`, `size`, `uppd_by`, `anonymous`, `ext`) 
VALUES (:tid, :hashs, :title, :filename, NOW(), :size, :upper, :anonymous, :ext)')->bindParams([
                'tid' => $this->torrent_id, 'hashs' => $this->hashs,
                'title' => $title, 'filename' => $this->file->getBaseName(),
                'size' => $this->file->size, 'upper' => app()->auth->getCurUser()->getId(),
                'anonymous' => $this->anonymous, 'ext' => $ext
            ])->execute();
            $id = app()->pdo->getLastInsertId();
            $file_loc = app()->getPrivatePath('subs') . DIRECTORY_SEPARATOR . $id . '.' . $ext;
            $this->file->saveAs($file_loc);
            app()->pdo->commit();
        } catch (\Exception $e) {
            if (isset($file_loc)) unlink($file_loc);
            app()->pdo->rollback();
            throw $e;
        }
        app()->redis->del(Constant::siteSubtitleSize);
    }
}
