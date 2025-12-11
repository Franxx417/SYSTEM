<?php

namespace Tests\Unit;

use App\Http\Controllers\PurchaseOrderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Mockery;
use Tests\TestCase;

class PurchaseOrderControllerTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_show_redirects_to_index_modal_when_po_exists(): void
    {
        // Register the index route used by the controller redirect
        Route::get('/po', fn () => 'ok')->name('po.index');

        // Prepare session-authenticated requestor
        $this->app['session']->put('auth_user', ['user_id' => 'user-1', 'role' => 'requestor']);
        $this->app['session']->put('status', 'hello-status');

        $request = Request::create('/po/PO-123', 'GET');
        $request->setLaravelSession($this->app['session']);

        // Mock DB chain to return an existing PO row
        $builder = Mockery::mock();
        $builder->shouldReceive('leftJoin')->andReturnSelf();
        $builder->shouldReceive('where')->andReturnSelf();
        $builder->shouldReceive('whereNotNull')->andReturnSelf();
        $builder->shouldReceive('select')->andReturnSelf();
        $builder->shouldReceive('first')->andReturn((object) ['purchase_order_id' => 'po-id-1']);
        DB::shouldReceive('table')->once()->with('purchase_orders as po')->andReturn($builder);

        $controller = new PurchaseOrderController();
        $response = $controller->show($request, 'PO-123');

        $response->assertRedirect(route('po.index', ['open_po' => 'PO-123']));
        $this->assertSame('hello-status', $response->getSession()->get('status'));
    }

    public function test_show_aborts_404_when_po_missing(): void
    {
        Route::get('/po', fn () => 'ok')->name('po.index');
        $this->app['session']->put('auth_user', ['user_id' => 'user-1', 'role' => 'requestor']);

        $request = Request::create('/po/PO-404', 'GET');
        $request->setLaravelSession($this->app['session']);

        $builder = Mockery::mock();
        $builder->shouldReceive('leftJoin')->andReturnSelf();
        $builder->shouldReceive('where')->andReturnSelf();
        $builder->shouldReceive('whereNotNull')->andReturnSelf();
        $builder->shouldReceive('select')->andReturnSelf();
        $builder->shouldReceive('first')->andReturn(null);
        DB::shouldReceive('table')->once()->with('purchase_orders as po')->andReturn($builder);

        $controller = new PurchaseOrderController();

        $this->expectException(\Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class);
        $controller->show($request, 'PO-404');
    }
}
