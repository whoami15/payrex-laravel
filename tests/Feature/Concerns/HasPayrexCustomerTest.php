<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use LegionHQ\LaravelPayrex\Concerns\HasPayrexCustomer;
use LegionHQ\LaravelPayrex\Data\Customer;
use LegionHQ\LaravelPayrex\Data\DeletedResource;

beforeEach(function () {
    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('email');
        $table->string('payrex_customer_id')->nullable();
        $table->timestamps();
    });
});

it('creates a customer in PayRex and stores the ID on the model', function () {
    Http::fake(['https://api.payrexhq.com/customers' => Http::response(loadFixture('customer/created.json'))]);

    $user = UserWithPayrexCustomer::create(['name' => 'Juan Dela Cruz', 'email' => 'juan@gmail.com']);
    $customer = $user->createAsPayrexCustomer();

    expect($customer)->toBeInstanceOf(Customer::class)
        ->and($customer->id)->toBe('cus_xxxxx')
        ->and($user->fresh()->payrex_customer_id)->toBe('cus_xxxxx');
});

it('uses the model name and email as defaults when creating a customer', function () {
    Http::fake(['https://api.payrexhq.com/customers' => Http::response(loadFixture('customer/created.json'))]);

    $user = UserWithPayrexCustomer::create(['name' => 'Juan Dela Cruz', 'email' => 'juan@gmail.com']);
    $user->createAsPayrexCustomer();

    Http::assertSent(function ($request) {
        return $request->url() === 'https://api.payrexhq.com/customers'
            && $request->method() === 'POST'
            && $request['name'] === 'Juan Dela Cruz'
            && $request['email'] === 'juan@gmail.com'
            && $request['currency'] === 'PHP';
    });
});

it('allows params to override defaults when creating a customer', function () {
    Http::fake(['https://api.payrexhq.com/customers' => Http::response(loadFixture('customer/created.json'))]);

    $user = UserWithPayrexCustomer::create(['name' => 'Juan Dela Cruz', 'email' => 'juan@gmail.com']);
    $user->createAsPayrexCustomer(['name' => 'Custom Name', 'email' => 'custom@example.com']);

    Http::assertSent(function ($request) {
        return $request['name'] === 'Custom Name'
            && $request['email'] === 'custom@example.com';
    });
});

it('throws when creating a customer that already has a PayRex ID', function () {
    $user = UserWithPayrexCustomer::create([
        'name' => 'Juan Dela Cruz',
        'email' => 'juan@gmail.com',
        'payrex_customer_id' => 'cus_existing',
    ]);

    $user->createAsPayrexCustomer();
})->throws(LogicException::class, 'This model already has a PayRex customer ID.');

it('retrieves the PayRex customer for the model', function () {
    Http::fake([
        'https://api.payrexhq.com/customers/cus_xxxxx' => Http::response(loadFixture('customer/created.json')),
    ]);

    $user = UserWithPayrexCustomer::create([
        'name' => 'Juan Dela Cruz',
        'email' => 'juan@gmail.com',
        'payrex_customer_id' => 'cus_xxxxx',
    ]);

    $customer = $user->asPayrexCustomer();

    expect($customer)->toBeInstanceOf(Customer::class)
        ->and($customer->id)->toBe('cus_xxxxx');

    Http::assertSent(function ($request) {
        return $request->url() === 'https://api.payrexhq.com/customers/cus_xxxxx'
            && $request->method() === 'GET';
    });
});

it('throws when retrieving a PayRex customer without an ID', function () {
    $user = UserWithPayrexCustomer::create(['name' => 'Juan Dela Cruz', 'email' => 'juan@gmail.com']);

    $user->asPayrexCustomer();
})->throws(LogicException::class, 'This model does not have a PayRex customer ID.');

it('updates the PayRex customer for the model', function () {
    Http::fake([
        'https://api.payrexhq.com/customers/cus_xxxxx' => Http::response(loadFixture('customer/updated.json')),
    ]);

    $user = UserWithPayrexCustomer::create([
        'name' => 'Juan Dela Cruz',
        'email' => 'juan@gmail.com',
        'payrex_customer_id' => 'cus_xxxxx',
    ]);

    $customer = $user->updatePayrexCustomer(['name' => 'Juan Dela Cruz Jr.']);

    expect($customer)->toBeInstanceOf(Customer::class)
        ->and($customer->name)->toBe('Juan Dela Cruz Jr.');

    Http::assertSent(function ($request) {
        return $request->url() === 'https://api.payrexhq.com/customers/cus_xxxxx'
            && $request->method() === 'PUT'
            && $request['name'] === 'Juan Dela Cruz Jr.';
    });
});

it('throws when updating a PayRex customer without an ID', function () {
    $user = UserWithPayrexCustomer::create(['name' => 'Juan Dela Cruz', 'email' => 'juan@gmail.com']);

    $user->updatePayrexCustomer(['name' => 'New Name']);
})->throws(LogicException::class, 'This model does not have a PayRex customer ID.');

it('deletes the PayRex customer and clears the ID on the model', function () {
    Http::fake([
        'https://api.payrexhq.com/customers/cus_xxxxx' => Http::response(loadFixture('customer/deleted.json')),
    ]);

    $user = UserWithPayrexCustomer::create([
        'name' => 'Juan Dela Cruz',
        'email' => 'juan@gmail.com',
        'payrex_customer_id' => 'cus_xxxxx',
    ]);

    $result = $user->deleteAsPayrexCustomer();

    expect($result)->toBeInstanceOf(DeletedResource::class)
        ->and($result->deleted)->toBeTrue()
        ->and($user->fresh()->payrex_customer_id)->toBeNull();

    Http::assertSent(function ($request) {
        return $request->url() === 'https://api.payrexhq.com/customers/cus_xxxxx'
            && $request->method() === 'DELETE';
    });
});

it('throws when deleting a PayRex customer without an ID', function () {
    $user = UserWithPayrexCustomer::create(['name' => 'Juan Dela Cruz', 'email' => 'juan@gmail.com']);

    $user->deleteAsPayrexCustomer();
})->throws(LogicException::class, 'This model does not have a PayRex customer ID.');

it('returns the PayRex customer ID from the model', function () {
    $user = UserWithPayrexCustomer::create([
        'name' => 'Juan Dela Cruz',
        'email' => 'juan@gmail.com',
        'payrex_customer_id' => 'cus_xxxxx',
    ]);

    expect($user->payrexCustomerId())->toBe('cus_xxxxx')
        ->and($user->hasPayrexCustomerId())->toBeTrue();
});

it('returns null when the model has no PayRex customer ID', function () {
    $user = UserWithPayrexCustomer::create(['name' => 'Juan Dela Cruz', 'email' => 'juan@gmail.com']);

    expect($user->payrexCustomerId())->toBeNull()
        ->and($user->hasPayrexCustomerId())->toBeFalse();
});

it('reads name and email from the model attributes', function () {
    $user = UserWithPayrexCustomer::create(['name' => 'Juan Dela Cruz', 'email' => 'juan@gmail.com']);

    expect($user->payrexCustomerName())->toBe('Juan Dela Cruz')
        ->and($user->payrexCustomerEmail())->toBe('juan@gmail.com');
});

it('returns the default column name for the PayRex customer ID', function () {
    $user = new UserWithPayrexCustomer;

    expect($user->payrexCustomerIdColumn())->toBe('payrex_customer_id');
});

it('supports a custom column name for the PayRex customer ID', function () {
    Http::fake(['https://api.payrexhq.com/customers' => Http::response(loadFixture('customer/created.json'))]);

    Schema::create('merchants', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('email');
        $table->string('prx_id')->nullable();
        $table->timestamps();
    });

    $merchant = MerchantWithCustomColumn::create(['name' => 'Test Merchant', 'email' => 'merchant@example.com']);
    $merchant->createAsPayrexCustomer();

    expect($merchant->payrexCustomerIdColumn())->toBe('prx_id')
        ->and($merchant->fresh()->prx_id)->toBe('cus_xxxxx');
});

class UserWithPayrexCustomer extends Model
{
    use HasPayrexCustomer;

    protected $table = 'users';

    protected $guarded = [];
}

class MerchantWithCustomColumn extends Model
{
    use HasPayrexCustomer;

    protected $table = 'merchants';

    protected $guarded = [];

    public function payrexCustomerIdColumn(): string
    {
        return 'prx_id';
    }
}
