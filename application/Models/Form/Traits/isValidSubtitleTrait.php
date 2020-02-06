<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 8/8/2019
 * Time: 10:09 AM
 */

namespace App\Models\Form\Traits;

trait isValidSubtitleTrait
{
    public $id;  // Subtitle Id

    protected $subtitle;

    public static function inputRules(): array
    {
        return [
            'id' => 'required | Integer'
        ];
    }

    public static function callbackRules(): array
    {
        return ['isValidSubtitle'];
    }

    /** @noinspection PhpUnused */
    protected function isValidSubtitle()
    {
        $sub_id = $this->getInput('id');

        $this->subtitle = app()->pdo->prepare('SELECT * FROM `subtitles` WHERE id = :sid LIMIT 1;')->bindParams([
            'sid' => $sub_id
        ])->queryOne();

        if ($this->subtitle === false) {
            $this->buildCallbackFailMsg('file', 'File not found');
        }
    }
}
