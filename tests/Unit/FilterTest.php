<?php

namespace Bazar\Tests\Unit;

use Bazar\Models\Address;
use Bazar\Models\Category;
use Bazar\Models\Medium;
use Bazar\Models\Order;
use Bazar\Models\Product;
use Bazar\Models\User;
use Bazar\Models\Variant;
use Bazar\Tests\TestCase;
use Illuminate\Http\Request;

class FilterTest extends TestCase
{
    /** @test */
    public function a_product_query_can_be_filtered()
    {
        $request = Request::create('/', 'GET', [
            'search' => 'test',
            'state' => 'all',
            'exclude' => [1, 2],
            'sort' => ['by' => 'created_at', 'order' => 'desc'],
            'category' => 1,
        ]);

        $query = Product::query()
            ->where(function ($query) {
                $query->where('bazar_products.name', 'like', 'test%')
                    ->orWhere('bazar_products.inventory->sku', 'like', 'test%');
            })->withTrashed()
              ->whereNotIn('bazar_products.id', [1, 2])
              ->orderBy('bazar_products.created_at', 'desc')
              ->whereHas('categories', function ($query) {
                  return $query->where('bazar_categories.id', 1);
              });

        $this->assertSame(
            $query->toSql(), Product::filter($request)->toSql()
        );
    }

    /** @test */
    public function an_order_query_can_be_filtered()
    {
        $request = Request::create('/', 'GET', [
            'search' => 'test',
            'state' => 'all',
            'exclude' => [1, 2],
            'sort' => ['by' => 'created_at', 'order' => 'desc'],
            'status' => 'in_progress',
            'user' => 1,
        ]);

        $query = Order::query()
            ->whereHas('address', function ($query) {
                $query->where('bazar_addresses.first_name', 'like', 'test%')
                    ->orWhere('bazar_addresses.last_name', 'like', 'test%');
            })->withTrashed()
              ->whereNotIn('bazar_orders.id', [1, 2])
              ->orderBy('bazar_orders.created_at', 'desc')
              ->where('bazar_orders.status', 'in_progress')
              ->whereHas('user', function ($query) {
                  return $query->where('users.id', 1);
              });

        $this->assertSame(
            $query->toSql(), Order::filter($request)->toSql()
        );
    }

    /** @test */
    public function a_medium_query_can_be_filtered()
    {
        $request = Request::create('/', 'GET', [
            'search' => 'test',
            'state' => 'fake',
            'exclude' => [1, 2],
            'sort' => ['by' => 'created_at', 'order' => 'desc'],
            'type' => 'image',
        ]);

        $query = Medium::query()
            ->where('bazar_media.name', 'like', 'test%')
            ->whereNotIn('bazar_media.id', [1, 2])
            ->orderBy('bazar_media.created_at', 'desc')
            ->where('bazar_media.mime_type', 'like', 'image%');

        $this->assertSame(
            $query->toSql(), Medium::filter($request)->toSql()
        );
    }

    /** @test */
    public function an_address_query_can_be_filtered()
    {
        $request = Request::create('/', 'GET', [
            'search' => 'test',
            'exclude' => [1, 2],
            'sort' => ['by' => 'created_at', 'order' => 'desc'],
        ]);

        $query = Address::query()
            ->where('bazar_addresses.alias', 'like', 'test%')
            ->whereNotIn('bazar_addresses.id', [1, 2])
            ->orderBy('bazar_addresses.created_at', 'desc');

        $this->assertSame(
            $query->toSql(), Address::filter($request)->toSql()
        );
    }

    /** @test */
    public function a_category_query_can_be_filtered()
    {
        $request = Request::create('/', 'GET', [
            'search' => 'test',
            'exclude' => [1, 2],
            'sort' => ['by' => 'created_at', 'order' => 'desc'],
        ]);

        $query = Category::query()
            ->where('bazar_categories.name', 'like', 'test%')
            ->whereNotIn('bazar_categories.id', [1, 2])
            ->orderBy('bazar_categories.created_at', 'desc');

        $this->assertSame(
            $query->toSql(), Category::filter($request)->toSql()
        );
    }

    /** @test */
    public function a_user_query_can_be_filtered()
    {
        $request = Request::create('/', 'GET', [
            'search' => 'test',
            'state' => 'fake',
            'exclude' => [1, 2],
            'sort' => ['by' => 'created_at', 'order' => 'desc'],
        ]);

        $query = User::query()
            ->where(function ($query) {
                $query->where('users.name', 'like', 'test%')
                    ->orWhere('users.email', 'like', 'test%');
            })->whereNotIn('users.id', [1, 2])
              ->orderBy('users.created_at', 'desc');

        $this->assertSame(
            $query->toSql(), User::filter($request)->toSql()
        );
    }

    /** @test */
    public function a_variant_query_can_be_filtered()
    {
        $request = Request::create('/', 'GET', [
            'search' => 'test',
            'state' => 'trashed',
            'exclude' => [1, 2],
            'sort' => ['by' => 'created_at', 'order' => 'desc'],
        ]);

        $query = Variant::query()
            ->where('bazar_variants.alias', 'like', 'test%')
            ->onlyTrashed()
            ->whereNotIn('bazar_variants.id', [1, 2])
            ->orderBy('bazar_variants.created_at', 'desc');

        $this->assertSame(
            $query->toSql(), Variant::filter($request)->toSql()
        );
    }
}
