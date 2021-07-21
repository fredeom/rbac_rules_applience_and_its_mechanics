### config/authManage.php

```
<?php

return [
    'class' => 'yii\rbac\DbManager',
    'defaultRoles' => ['guest'],
];
```

### config/console.php

```
$authManager = require(__DIR__ . '/authManager.php');
```

```
[
  'components' => [
      'authManager' => $authManager
  ]
]
```
