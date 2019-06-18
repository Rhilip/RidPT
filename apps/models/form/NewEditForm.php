<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/5/31
 * Time: 19:56
 */

namespace apps\models\form;


use Rid\Validators\Validator;

class NewEditForm extends Validator
{
    public $id = 0;

    public $user_id;
    public $title;
    public $body;
    public $notify = 0;
    public $force_read = 0;

    public function buildDefaultValue()
    {
        $this->user_id = app()->user->getId();
    }

    public static function inputRules()
    {
        return [
            'id' => 'integer',
            'title' => [
                ['required'],
                ['maxlength', ['max' => 255]]
            ],
            'body' => 'required',
            'notify' => 'Integer | Equal(value=1)',
            'force_read' => 'Integer | Equal(value=1)',
        ];
    }

    public function flush()
    {
        if ($this->id == 0) { // This is new news
            app()->pdo->createCommand('INSERT INTO news (user_id,create_at,title,body,notify,force_read) VALUES (:uid,CURRENT_TIMESTAMP,:title,:body,:notify,:fread);')->bindParams([
                'uid' => $this->user_id, 'title' => $this->title, 'body' => $this->body,
                'notify' => $this->notify, 'fread' => $this->force_read
            ])->execute();
        } else {  // This is news edit
            app()->pdo->createCommand('UPDATE news SET user_id = :uid, title = :title, body = :body, notify = :notify, force_read = :fread WHERE id=:id')->bindParams([
                'id' => $this->id, 'uid' => $this->user_id,
                'title' => $this->title, 'body' => $this->body,
                'notify' => $this->notify, 'fread' => $this->force_read
            ])->execute();
        }
        // Clean News Cache
        app()->redis->del('Site:recent_news');
    }
}
