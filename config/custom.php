<?php

return [
    // 'name' => env('APP_NAME', 'Laravel'),

    'column_counts' => 3,
    'column_names' =>  [
		'module_code',
        'module_name',
        'module_term',
    ],
    'row_validation_condition' => '[a-z0-9 ]',
];
