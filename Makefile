rbac-migrate:
	./yii migrate --migrationPath=@yii/rbac/migrations/

rbac-init:
	./yii rbac/init

rbac-dispose:
	./yii rbac/dispose

rbac-admin-assign:
	./yii rbac/set-admin ${id}
