public function toggleLeaderRole(bool $toLeaders): void
    {
        $auth = Yii::$app->authManager;
        $isLeader = false;
        foreach ($this->getUserDepartments() as $department) {
            $isLeader = $isLeader || $this->isLeaderInDepartment($department->department_id);
        }
        try {
            if ($isLeader && !array_key_exists(User::ROLE_TEAM_LEAD, $auth->getRolesByUser($this->getId()))) {
                $auth->assign($auth->getRole(User::ROLE_TEAM_LEAD), $this->getId());
            } elseif (!$isLeader && array_key_exists(User::ROLE_TEAM_LEAD, $auth->getRolesByUser($this->getId()))) {
                $auth->revoke($auth->getRole(User::ROLE_TEAM_LEAD), $this->getId());
            }
        } catch (\Throwable $e) {
            Yii::error("Failed to change role during department change", TrelloCustomException::CATEGORY_ERROR);
            throw $e;
        }
    }
