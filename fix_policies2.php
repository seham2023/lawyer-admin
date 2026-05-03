<?php

$files = [
    'ExpensePolicy.php' => 'Expense',
    'CaseRecordPolicy.php' => 'CaseRecord',
    'ServicePolicy.php' => 'Service',
    'UserPolicy.php' => 'User',
    'PaymentPolicy.php' => 'Payment',
    'VisitPolicy.php' => 'Visit',
];

foreach ($files as $file => $modelName) {
    $path = "app/Policies/$file";
    if (!file_exists($path)) continue;
    
    $content = file_get_contents($path);
    
    // Replace viewAny
    $content = preg_replace(
        '/public function viewAny\([^)]+\): bool\s*\{\s*return true;[^}]*\}/',
        "public function viewAny(User \$user): bool\n    {\n        return \$user->checkPermissionTo('view-any $modelName');\n    }",
        $content
    );
    
    // Replace view
    $content = preg_replace(
        '/public function view\([^)]+\): bool\s*\{\s*return \$user->type === \'admin\'[^}]*\}/',
        "public function view(User \$user, \\App\\Models\\$modelName \$model): bool\n    {\n        return \$user->checkPermissionTo('view $modelName');\n    }",
        $content
    );
    // Or if the variable isn't just $model, wait, just matching the inner return is easier.
}
