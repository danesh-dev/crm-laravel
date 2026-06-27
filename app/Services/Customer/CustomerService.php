<?php

namespace App\Services\Customer;

use App\Models\Customer;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class CustomerService
{
    public function create(array $data): Customer
    {
        return DB::transaction(function () use ($data): Customer {
            $tagIds = Arr::pull($data, 'tag_ids');

            $customer = Customer::create($data);

            if ($tagIds !== null) {
                $customer->tags()->sync($tagIds);
            }

            return $customer;
        });
    }

    public function update(Customer $customer, array $data): Customer
    {
        return DB::transaction(function () use ($customer, $data): Customer {
            $tagIds = Arr::pull($data, 'tag_ids');

            $customer->update($data);

            if ($tagIds !== null) {
                $customer->tags()->sync($tagIds);
            }

            return $customer;
        });
    }

    public function delete(Customer $customer): void
    {
        $customer->delete();
    }
}
