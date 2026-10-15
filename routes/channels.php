<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('orders', function ($user = null) {
    return true;
});

Broadcast::channel('admin.notifications', function ($user = null) {
    return optional($user)->role === 'admin';
});
