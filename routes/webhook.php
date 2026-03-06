<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use LegionHQ\LaravelPayrex\Http\Controllers\WebhookController;
use LegionHQ\LaravelPayrex\Middleware\VerifyWebhookSignature;

Route::post(config('payrex.webhook.path', 'payrex/webhook'), WebhookController::class)
    ->middleware(VerifyWebhookSignature::class)
    ->name('payrex.webhook');
