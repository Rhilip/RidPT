<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/5/31
 * Time: 10:09
 */

namespace apps\controllers;


use apps\models\form\News;
use Rid\Http\Controller;

class NewsController extends Controller
{
    public function actionIndex() {
        $pager = new News\SearchForm();
        $pager->setInput(app()->request->get());

        $success = $pager->validate();
        if (!$success) {
            return $this->render('action/action_fail', ['title' => 'Attack', 'msg' => $pager->getError()]);
        } else {
            return $this->render('news/index', ['pager'=>$pager]);
        }
    }

    public function actionNew() {
        if (app()->request->isPost()) {
            $newform = new News\EditForm();
            $newform->setInput(app()->request->post());
            $success = $newform->validate();
            if (!$success) {
                return $this->render('action/action_fail', ['title' => 'new blog failed', 'msg' => $newform->getError()]);
            } else {
                $newform->flush();  // Save the news
                return app()->response->redirect('/news');
            }
        } elseif (app()->auth->getCurUser()->isPrivilege('manage_news')) {
            return $this->render('news/edit');
        }
        return $this->render('action/action_fail', ['title' => 'Action Failed', 'msg' => 'action not allowed']);
    }

    public function actionEdit()
    {
        if (app()->request->isPost()) {
            $newform = new News\EditForm();
            $newform->setInput(app()->request->post());
            $success = $newform->validate();
            if (!$success) {
                return $this->render('action/action_fail', ['title' => 'Upload Failed', 'msg' => $newform->getError()]);
            } else {
                $newform->flush();  // Save the news
                return app()->response->redirect('/news');
            }
        } elseif (app()->auth->getCurUser()->isPrivilege('manage_news')) {
            $id = app()->request->get('id', 0);
            if (filter_var($id, FILTER_VALIDATE_INT) && $id > 0) {
                // TODO add other check
                $news = app()->pdo->createCommand('SELECT * FROM news WHERE id= :id')->bindParams(['id' => $id])->queryOne();
                return $this->render('news/edit', ['news' => $news]);
            }
        }
        return $this->render('action/action_fail', ['title' => 'Action Failed', 'msg' => 'action not allowed']);
    }

    public function actionDelete() {
        if (app()->auth->getCurUser()->isPrivilege('manage_news')) {
            $id = app()->request->get('id',0);
            if (filter_var($id,FILTER_VALIDATE_INT) && $id > 0) {
                // TODO add other check
                app()->pdo->createCommand('DELETE FROM news WHERE id= :id')->bindParams(['id'=>$id])->execute();
            }
            return app()->response->redirect('/news');
        }
        return $this->render('action/action_fail', ['title' => 'Action Failed', 'msg' => 'action not allowed']);

    }
}
