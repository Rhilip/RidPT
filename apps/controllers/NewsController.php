<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/5/31
 * Time: 10:09
 */

namespace apps\controllers;


use apps\models\form\NewEditForm;
use Rid\Http\Controller;

class NewsController extends Controller
{
    public function actionIndex() {

        $query = app()->request->get('query','title');
        $search = app()->request->get('search','');
        if (empty($search)) {
            $count = app()->pdo->createCommand('SELECT COUNT(*) FROM `news`;')->queryScalar();
        } else {
            $count = app()->pdo->createCommand([
                ['SELECT COUNT(*) FROM `news` WHERE 1=1 '],
                ['AND `title` LIKE :search ', 'params' => ['search' => "%$search%"], 'if' => ($query == 'title' && !empty($search))],
                ['AND `body` LIKE :search ', 'params' => ['search' => "%$search%"], 'if' => ($query == 'body' && !empty($search))],
                ['AND `title` LIKE :st OR `body` LIKE :sb ', 'params' => ['st' => "%$search%",'sb' => "%$search%"], 'if' => ($query == 'both' && !empty($search))],
            ])->queryScalar();
        }

        $page  = app()->request->get('page',1);
        if (!filter_var($page,FILTER_VALIDATE_INT)) $page = 1;
        $limit = 10;

        $news = app()->pdo->createCommand([
            ['SELECT * FROM `news` WHERE 1=1 '],
            ['AND `title` LIKE :search ', 'params' => ['search' => "%$search%"], 'if' => ($query == 'title' && !empty($search))],
            ['AND `body` LIKE :search ', 'params' => ['search' => "%$search%"], 'if' => ($query == 'body' && !empty($search))],
            ['AND `title` LIKE :st OR `body` LIKE :sb ', 'params' => ['st' => "%$search%",'sb' => "%$search%"], 'if' => ($query == 'both' && !empty($search))],
            ['ORDER BY create_at DESC '],
            ['LIMIT :offset, :rows', 'params' => ['offset' => ($page - 1) * $limit, 'rows' => $limit]],
           ])->queryAll();

        return $this->render('news/index', ['news' => $news, 'query' => $query, 'search' => $search, 'count' => $count, 'limit' => $limit]);

    }

    public function actionNew() {
        if (app()->request->isPost()) {
            $newform = new NewEditForm();
            $newform->setData(app()->request->post());
            $success = $newform->validate();
            if (!$success) {
                return $this->render('action/action_fail', ['title' => 'new blog failed', 'msg' => $newform->getError()]);
            } else {
                $newform->flush();  // Save the news
                return app()->response->redirect('/news');
            }
        } elseif (app()->user->isPrivilege('manage_news')) {
            return $this->render('news/edit');
        }
        return $this->render('action/action_fail', ['title' => 'Action Failed', 'msg' => 'action not allowed']);
    }

    public function actionEdit()
    {
        if (app()->request->isPost()) {
            $newform = new NewEditForm();
            $newform->setData(app()->request->post());
            $success = $newform->validate();
            if (!$success) {
                return $this->render('action/action_fail', ['title' => 'Upload Failed', 'msg' => $newform->getError()]);
            } else {
                $newform->flush();  // Save the news
                return app()->response->redirect('/news');
            }
        } elseif (app()->user->isPrivilege('manage_news')) {
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
        if (app()->user->isPrivilege('manage_news')) {
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
