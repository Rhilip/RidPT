<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 8/8/2019
 * Time: 10:09 AM
 */

namespace apps\models\form\Traits;


trait isValidSubtitleTrait
{
    public $id;

    protected $subtitle;

    public static function inputRules()
    {
        return [
            'id' => 'required | Integer'
        ];
    }

    public static function callbackRules()
    {
        return ['isValidSubtitle'];
    }

    protected function isValidSubtitle()
    {
        $sub_id = $this->getData('id');

        $this->subtitle = app()->pdo->createCommand('SELECT * FROM `subtitles` WHERE id = :sid LIMIT 1;')->bindParams([
            'sid' => $sub_id
        ])->queryOne();

        if ($this->subtitle === false) {
            $this->buildCallbackFailMsg('file', 'File not found');
        }
    }
}
