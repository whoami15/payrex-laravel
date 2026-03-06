<?php

declare(strict_types=1);

arch('all source files use strict types')
    ->expect('LegionHQ\LaravelPayrex')
    ->toUseStrictTypes();

arch('all events extend PayrexEvent')
    ->expect('LegionHQ\LaravelPayrex\Events')
    ->classes()
    ->toExtend('LegionHQ\LaravelPayrex\Events\PayrexEvent');

arch('all concrete events are final')
    ->expect('LegionHQ\LaravelPayrex\Events')
    ->classes()
    ->toBeFinal()
    ->ignoring('LegionHQ\LaravelPayrex\Events\PayrexEvent');

arch('all api exceptions extend PayrexApiException except WebhookVerificationException')
    ->expect('LegionHQ\LaravelPayrex\Exceptions')
    ->classes()
    ->toExtend('LegionHQ\LaravelPayrex\Exceptions\PayrexApiException')
    ->ignoring('LegionHQ\LaravelPayrex\Exceptions\WebhookVerificationException')
    ->ignoring('LegionHQ\LaravelPayrex\Exceptions\PayrexException');

arch('all resources extend ApiResource')
    ->expect('LegionHQ\LaravelPayrex\Resources')
    ->classes()
    ->toExtend('LegionHQ\LaravelPayrex\Resources\ApiResource');

arch('all concrete resources are final')
    ->expect('LegionHQ\LaravelPayrex\Resources')
    ->classes()
    ->toBeFinal()
    ->ignoring('LegionHQ\LaravelPayrex\Resources\ApiResource');

arch('enums are string-backed')
    ->expect('LegionHQ\LaravelPayrex\Enums')
    ->toBeStringBackedEnums();

arch('no debugging functions in source code')
    ->expect(['dd', 'dump', 'ray', 'var_dump', 'print_r'])
    ->not->toBeUsed();

arch('PayrexEvent is abstract')
    ->expect('LegionHQ\LaravelPayrex\Events\PayrexEvent')
    ->toBeAbstract();

arch('ApiResource is abstract')
    ->expect('LegionHQ\LaravelPayrex\Resources\ApiResource')
    ->toBeAbstract();

arch('all DTOs are readonly')
    ->expect('LegionHQ\LaravelPayrex\Data')
    ->classes()
    ->toBeReadonly();

arch('all concrete DTOs are final')
    ->expect('LegionHQ\LaravelPayrex\Data')
    ->classes()
    ->toBeFinal()
    ->ignoring('LegionHQ\LaravelPayrex\Data\PayrexObject');

arch('leaf exception classes are final')
    ->expect('LegionHQ\LaravelPayrex\Exceptions')
    ->classes()
    ->toBeFinal()
    ->ignoring('LegionHQ\LaravelPayrex\Exceptions\PayrexException')
    ->ignoring('LegionHQ\LaravelPayrex\Exceptions\PayrexApiException');

arch('commands are final')
    ->expect('LegionHQ\LaravelPayrex\Commands')
    ->classes()
    ->toBeFinal();

arch('middleware is final')
    ->expect('LegionHQ\LaravelPayrex\Middleware')
    ->classes()
    ->toBeFinal();

arch('controllers are final')
    ->expect('LegionHQ\LaravelPayrex\Http\Controllers')
    ->classes()
    ->toBeFinal();

arch('PayrexClient is final')
    ->expect('LegionHQ\LaravelPayrex\PayrexClient')
    ->toBeFinal();

arch('PayrexTransport is final')
    ->expect('LegionHQ\LaravelPayrex\PayrexTransport')
    ->toBeFinal();

arch('source code does not use env() directly')
    ->expect('LegionHQ\LaravelPayrex')
    ->not->toUse('env');

arch('source code does not use config() outside service provider')
    ->expect('LegionHQ\LaravelPayrex')
    ->not->toUse('config')
    ->ignoring('LegionHQ\LaravelPayrex\PayrexServiceProvider');
