<?php

declare(strict_types=1);

namespace LegionHQ\LaravelPayrex\Concerns;

use Illuminate\Database\Eloquent\Model;
use LegionHQ\LaravelPayrex\Data\Customer;
use LegionHQ\LaravelPayrex\Data\DeletedResource;
use LegionHQ\LaravelPayrex\PayrexClient;
use LogicException;

/**
 * @mixin Model
 */
trait HasPayrexCustomer
{
    /**
     * Get the PayRex customer ID stored on this model.
     */
    public function payrexCustomerId(): ?string
    {
        return $this->{$this->payrexCustomerIdColumn()};
    }

    /**
     * Determine if the model has a PayRex customer ID.
     */
    public function hasPayrexCustomerId(): bool
    {
        return $this->payrexCustomerId() !== null;
    }

    /**
     * Create a customer in PayRex and store the ID on this model.
     *
     * @param  array<string, mixed>  $params
     */
    public function createAsPayrexCustomer(array $params = []): Customer
    {
        if ($this->hasPayrexCustomerId()) {
            throw new LogicException('This model already has a PayRex customer ID.');
        }

        $client = $this->payrexClient();

        $customer = $client->customers()->create(array_merge([
            'name' => $this->payrexCustomerName(),
            'email' => $this->payrexCustomerEmail(),
            'currency' => $client->defaultCurrency(),
        ], $params));

        $this->forceFill([$this->payrexCustomerIdColumn() => $customer->id])->save();

        return $customer;
    }

    /**
     * Retrieve the PayRex customer for this model.
     */
    public function asPayrexCustomer(): Customer
    {
        $this->assertHasPayrexCustomerId();

        return $this->payrexClient()->customers()->retrieve($this->payrexCustomerId());
    }

    /**
     * Update the PayRex customer for this model.
     *
     * @param  array<string, mixed>  $params
     */
    public function updatePayrexCustomer(array $params = []): Customer
    {
        $this->assertHasPayrexCustomerId();

        return $this->payrexClient()->customers()->update($this->payrexCustomerId(), $params);
    }

    /**
     * Delete the PayRex customer and clear the ID on this model.
     */
    public function deleteAsPayrexCustomer(): DeletedResource
    {
        $this->assertHasPayrexCustomerId();

        $deletedResource = $this->payrexClient()->customers()->delete($this->payrexCustomerId());

        $this->forceFill([$this->payrexCustomerIdColumn() => null])->save();

        return $deletedResource;
    }

    /**
     * Get the customer's name for PayRex. Defaults to the model's "name" attribute.
     *
     * Override this method if your model uses a different column for the customer name.
     */
    public function payrexCustomerName(): ?string
    {
        return $this->name;
    }

    /**
     * Get the customer's email for PayRex. Defaults to the model's "email" attribute.
     *
     * Override this method if your model uses a different column for the customer email.
     */
    public function payrexCustomerEmail(): ?string
    {
        return $this->email;
    }

    /**
     * Get the column name for the PayRex customer ID. Defaults to "payrex_customer_id".
     *
     * Override this method if your migration uses a different column name.
     */
    public function payrexCustomerIdColumn(): string
    {
        return 'payrex_customer_id';
    }

    /**
     * Get the PayRex client instance.
     *
     * Override this method to customize the client instance used by this model.
     */
    protected function payrexClient(): PayrexClient
    {
        return app(PayrexClient::class);
    }

    /**
     * Assert that the model has a PayRex customer ID.
     */
    protected function assertHasPayrexCustomerId(): void
    {
        if (! $this->hasPayrexCustomerId()) {
            throw new LogicException('This model does not have a PayRex customer ID.');
        }
    }
}
