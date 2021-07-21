<?php

namespace app\commands\controllers;

use Yii;
use app\models\Leader;
use app\models\User;
use app\models\UserDepartment;
use app\modules\admin\controllers\AdminBaseController;
use app\modules\admin\controllers\UserController;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Console;

class RbacController extends Controller
{
    public function actionInit(): void
    {
        try {
            $auth = Yii::$app->authManager;
            $auth->removeAll();

            $guest = $auth->createRole(User::ROLE_GUEST);
            $auth->add($guest);

            $user = $auth->createRole(User::ROLE_USER);
            $auth->add($user);
            $auth->addChild($user, $guest);

            $strategy = $auth->createRole(User::ROLE_STRATEGY);
            $auth->add($strategy);
            $auth->addChild($strategy, $user);

            $admin = $auth->createPermission(AdminBaseController::ADMIN_PERMISSION);
            $admin->description = AdminBaseController::ADMIN_PERMISSION_DESCRIPTION;
            $auth->add($admin);

            $auth->addChild($strategy, $admin);

            $userView = $auth->createPermission(UserController::USER_VIEW_PERMISSION);
            $userView->description = UserController::USER_VIEW_PERMISSION_DESCRIPTION;
            $auth->add($userView);

            $teamLead = $auth->createRole(User::ROLE_TEAM_LEAD);
            $auth->add($teamLead);
            $auth->addChild($teamLead, $user);
            $auth->addChild($teamLead, $userView);

            $hr = $auth->createRole(User::ROLE_HR);
            $auth->add($hr);
            $auth->addChild($hr, $user);
            $auth->addChild($hr, $userView);

            $auth->addChild($strategy, $teamLead);
            $auth->addChild($strategy, $hr);

            foreach (Leader::find()->all() as $leader) {
                $auth->assign($teamLead, $leader->user_id);
            }

            foreach (UserDepartment::find()->all() as $userDepartment) {
                switch ($userDepartment->department_id) {
                    case User::DEP_HR: {
                        $auth->assign($hr, $userDepartment->user_id);
                        break;
                    }
                    case User::DEP_STRATEGY: {
                        $auth->assign($strategy, $userDepartment->user_id);
                        break;
                    }
                }
            }
        } catch (\Throwable $e) {
            $this->stderr($e->getMessage() . "\n");
        }
    }

    public function actionDispose(): void
    {
        try {
            $auth = Yii::$app->authManager;
            $auth->removeAll();
        } catch (\Throwable $e) {
            $this->stderr($e->getMessage() . "\n");
        }
    }

    public function actionSetAdmin($id)
    {
        if(!$id || is_int($id)){
            $this->stderr("Param 'id' must be set!\n", Console::BG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $user = (new User())->findIdentity($id);
        if (!$user) {
            $this->stderr("User witch id:'$id' is not found!\n", Console::BG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $auth = Yii::$app->authManager;

        $role = $auth->getRole(User::ROLE_STRATEGY);

        $auth->revokeAll($id);

        $auth->assign($role, $id);

        $this->stdout("Done!\n", Console::BOLD);

        return ExitCode::OK;
    }
}
