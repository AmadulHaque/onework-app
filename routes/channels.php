<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('chatChannel', function () {
    return true;
});
