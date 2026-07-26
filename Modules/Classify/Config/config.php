<?php

return [
    'name' => 'Classify',
    'upload_dir' => 'classify',
    'default_duration_days' => 30,
    'default_max_images' => 8,
    'conditions' => ['new', 'used', 'refurbished'],
    'statuses' => ['draft', 'pending', 'published', 'rejected', 'sold', 'expired', 'archived'],
];
