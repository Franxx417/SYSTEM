<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Services\ConstantsService;

/**
 * Requestor API Controller
 * 
 * RESTful API endpoints for requestor operations
 * Handles purchase orders, dashboard metrics, and related resources
 */
class RequestorController extends Controller
{
    use ApiResponseTrait;
    
    /**
     * Get requestor dashboard metrics
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMetrics(Request $request)
    {
        try {
            $auth = $request->attributes->get('auth_user');
            $statuses = ConstantsService::getStatuses();
            
            // Cache metrics for 5 minutes
            $metrics = Cache::remember("requestor_metrics_{$auth['user_id']}", 300, function() use ($auth, $statuses) {
                return [
                    'total_pos' => DB::table('purchase_orders')
                        ->where('requestor_id', $auth['user_id'])
                        ->count(),
                    
                    'verified_pos' => DB::table('purchase_orders as po')
                        ->leftJoin('approvals as ap', function($join) {
                            $join->on('ap.purchase_order_id', '=', 'po.purchase_order_id')
                                 ->whereRaw('ap.prepared_at = (SELECT MAX(prepared_at) FROM approvals WHERE purchase_order_id = po.purchase_order_id)');
                        })
                        ->leftJoin('statuses as st', 'st.status_id', '=', 'ap.status_id')
                        ->where('po.requestor_id', $auth['user_id'])
                        ->where('st.status_name', $statuses['verified'])
                        ->count(),
                    
                    'approved_pos' => DB::table('purchase_orders as po')
                        ->leftJoin('approvals as ap', function($join) {
                            $join->on('ap.purchase_order_id', '=', 'po.purchase_order_id')
                                 ->whereRaw('ap.prepared_at = (SELECT MAX(prepared_at) FROM approvals WHERE purchase_order_id = po.purchase_order_id)');
                        })
                        ->leftJoin('statuses as st', 'st.status_id', '=', 'ap.status_id')
                        ->where('po.requestor_id', $auth['user_id'])
                        ->where('st.status_name', $statuses['approved'])
                        ->count(),
                    
                    'pending_pos' => DB::table('purchase_orders as po')
                        ->leftJoin('approvals as ap', function($join) {
                            $join->on('ap.purchase_order_id', '=', 'po.purchase_order_id')
                                 ->whereRaw('ap.prepared_at = (SELECT MAX(prepared_at) FROM approvals WHERE purchase_order_id = po.purchase_order_id)');
                        })
                        ->leftJoin('statuses as st', 'st.status_id', '=', 'ap.status_id')
                        ->where('po.requestor_id', $auth['user_id'])
                        ->where('st.status_name', $statuses['pending'])
                        ->count(),
                    
                    'total_value' => DB::table('purchase_orders')
                        ->where('requestor_id', $auth['user_id'])
                        ->sum('total'),
                    
                    'average_value' => DB::table('purchase_orders')
                        ->where('requestor_id', $auth['user_id'])
                        ->avg('total'),
                ];
            });
            
            return $this->successResponse($metrics, 'Metrics retrieved successfully');
            
        } catch (\Exception $e) {
            Log::error('Failed to get requestor metrics', [
                'error' => $e->getMessage(),
                'user_id' => $auth['user_id'] ?? null
            ]);
            
            return $this->errorResponse(
                'Failed to retrieve metrics',
                'METRICS_ERROR',
                500
            );
        }
    }
    
    /**
     * Get recent purchase orders
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRecentPurchaseOrders(Request $request)
    {
        try {
            $auth = $request->attributes->get('auth_user');
            $limit = $request->query('limit', 5);
            
            // Validate limit
            if ($limit < 1 || $limit > 50) {
                return $this->validationErrorResponse([
                    'limit' => ['Limit must be between 1 and 50']
                ]);
            }
            
            $recentPOs = DB::table('purchase_orders as po')
                ->leftJoin('approvals as ap', function($join) {
                    $join->on('ap.purchase_order_id', '=', 'po.purchase_order_id')
                         ->whereRaw('ap.prepared_at = (SELECT MAX(prepared_at) FROM approvals WHERE purchase_order_id = po.purchase_order_id)');
                })
                ->leftJoin('statuses as st', 'st.status_id', '=', 'ap.status_id')
                ->leftJoin('suppliers as s', 's.supplier_id', '=', 'po.supplier_id')
                ->where('po.requestor_id', $auth['user_id'])
                ->select([
                    'po.purchase_order_id',
                    'po.purchase_order_no',
                    'po.purpose',
                    'po.total',
                    'po.created_at',
                    'po.date_requested',
                    'st.status_name',
                    's.name as supplier_name'
                ])
                ->orderByDesc('po.created_at')
                ->limit($limit)
                ->get();
            
            return $this->successResponse($recentPOs, 'Recent purchase orders retrieved successfully');
            
        } catch (\Exception $e) {
            Log::error('Failed to get recent purchase orders', [
                'error' => $e->getMessage(),
                'user_id' => $auth['user_id'] ?? null
            ]);
            
            return $this->errorResponse(
                'Failed to retrieve purchase orders',
                'PO_FETCH_ERROR',
                500
            );
        }
    }
    
    /**
     * Get paginated list of purchase orders
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPurchaseOrders(Request $request)
    {
        try {
            $auth = $request->attributes->get('auth_user');
            $perPage = $request->query('per_page', 15);
            $search = $request->query('search', '');
            $status = $request->query('status', '');
            
            // Build query
            $query = DB::table('purchase_orders as po')
                ->leftJoin('approvals as ap', function($join) {
                    $join->on('ap.purchase_order_id', '=', 'po.purchase_order_id')
                         ->whereRaw('ap.prepared_at = (SELECT MAX(prepared_at) FROM approvals WHERE purchase_order_id = po.purchase_order_id)');
                })
                ->leftJoin('statuses as st', 'st.status_id', '=', 'ap.status_id')
                ->leftJoin('suppliers as s', 's.supplier_id', '=', 'po.supplier_id')
                ->where('po.requestor_id', $auth['user_id'])
                ->select([
                    'po.purchase_order_id',
                    'po.purchase_order_no',
                    'po.purpose',
                    'po.total',
                    'po.subtotal',
                    'po.shipping_fee',
                    'po.discount',
                    'po.created_at',
                    'po.date_requested',
                    'po.delivery_date',
                    'st.status_name',
                    'st.status_id',
                    's.name as supplier_name',
                    's.supplier_id'
                ]);
            
            // Apply search filter
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('po.purchase_order_no', 'like', "%{$search}%")
                      ->orWhere('po.purpose', 'like', "%{$search}%")
                      ->orWhere('s.name', 'like', "%{$search}%");
                });
            }
            
            // Apply status filter
            if ($status) {
                $query->where('st.status_name', $status);
            }
            
            // Paginate results
            $purchaseOrders = $query->orderByDesc('po.created_at')
                ->paginate($perPage);
            
            return $this->paginatedResponse($purchaseOrders, 'Purchase orders retrieved successfully');
            
        } catch (\Exception $e) {
            Log::error('Failed to get purchase orders', [
                'error' => $e->getMessage(),
                'user_id' => $auth['user_id'] ?? null
            ]);
            
            return $this->errorResponse(
                'Failed to retrieve purchase orders',
                'PO_LIST_ERROR',
                500
            );
        }
    }
    
    /**
     * Get single purchase order details
     *
     * @param Request $request
     * @param string $poId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPurchaseOrder(Request $request, string $poId)
    {
        try {
            $auth = $request->attributes->get('auth_user');
            
            // Get PO with authorization check
            $po = DB::table('purchase_orders as po')
                ->leftJoin('approvals as ap', function($join) {
                    $join->on('ap.purchase_order_id', '=', 'po.purchase_order_id')
                         ->whereRaw('ap.prepared_at = (SELECT MAX(prepared_at) FROM approvals WHERE purchase_order_id = po.purchase_order_id)');
                })
                ->leftJoin('statuses as st', 'st.status_id', '=', 'ap.status_id')
                ->leftJoin('suppliers as s', 's.supplier_id', '=', 'po.supplier_id')
                ->where('po.purchase_order_no', $poId)
                ->where('po.requestor_id', $auth['user_id'])
                ->select([
                    'po.*',
                    'st.status_name',
                    'st.status_id',
                    's.name as supplier_name',
                    's.address as supplier_address',
                    's.contact_person',
                    's.contact_number',
                    's.tin_no',
                    's.vat_type'
                ])
                ->first();
            
            if (!$po) {
                return $this->notFoundResponse('Purchase Order');
            }
            
            // Get items
            $items = DB::table('items')
                ->where('purchase_order_id', $po->purchase_order_id)
                ->select([
                    'item_id',
                    'item_name',
                    'item_description',
                    'quantity',
                    'unit_price',
                    'total_cost'
                ])
                ->get();
            
            // Get approval history
            $approvals = DB::table('approvals as ap')
                ->leftJoin('statuses as st', 'st.status_id', '=', 'ap.status_id')
                ->leftJoin('users as u', 'u.user_id', '=', 'ap.prepared_by_id')
                ->where('ap.purchase_order_id', $po->purchase_order_id)
                ->select([
                    'st.status_name',
                    'ap.prepared_at',
                    'ap.remarks',
                    'u.name as prepared_by'
                ])
                ->orderBy('ap.prepared_at', 'desc')
                ->get();
            
            return $this->successResponse([
                'po' => $po,
                'items' => $items,
                'approvals' => $approvals
            ], 'Purchase order retrieved successfully');
            
        } catch (\Exception $e) {
            Log::error('Failed to get purchase order', [
                'error' => $e->getMessage(),
                'po_id' => $poId,
                'user_id' => $auth['user_id'] ?? null
            ]);
            
            return $this->errorResponse(
                'Failed to retrieve purchase order',
                'PO_DETAIL_ERROR',
                500
            );
        }
    }
    
    /**
     * Get statistics for requestor dashboard
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStatistics(Request $request)
    {
        try {
            $auth = $request->attributes->get('auth_user');
            
            // Get date range
            $dateFrom = $request->query('date_from', now()->subMonth()->toDateString());
            $dateTo = $request->query('date_to', now()->toDateString());
            
            // Validate dates
            $validator = Validator::make([
                'date_from' => $dateFrom,
                'date_to' => $dateTo
            ], [
                'date_from' => 'date',
                'date_to' => 'date|after_or_equal:date_from'
            ]);
            
            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors()->toArray());
            }
            
            $stats = [
                'period' => [
                    'from' => $dateFrom,
                    'to' => $dateTo
                ],
                'total_pos' => DB::table('purchase_orders')
                    ->where('requestor_id', $auth['user_id'])
                    ->whereBetween('created_at', [$dateFrom, $dateTo])
                    ->count(),
                'total_value' => DB::table('purchase_orders')
                    ->where('requestor_id', $auth['user_id'])
                    ->whereBetween('created_at', [$dateFrom, $dateTo])
                    ->sum('total'),
                'by_status' => DB::table('purchase_orders as po')
                    ->leftJoin('approvals as ap', function($join) {
                        $join->on('ap.purchase_order_id', '=', 'po.purchase_order_id')
                             ->whereRaw('ap.prepared_at = (SELECT MAX(prepared_at) FROM approvals WHERE purchase_order_id = po.purchase_order_id)');
                    })
                    ->leftJoin('statuses as st', 'st.status_id', '=', 'ap.status_id')
                    ->where('po.requestor_id', $auth['user_id'])
                    ->whereBetween('po.created_at', [$dateFrom, $dateTo])
                    ->select('st.status_name', DB::raw('COUNT(*) as count'))
                    ->groupBy('st.status_name')
                    ->get(),
                'by_month' => DB::table('purchase_orders')
                    ->where('requestor_id', $auth['user_id'])
                    ->whereBetween('created_at', [$dateFrom, $dateTo])
                    ->select(
                        DB::raw('YEAR(created_at) as year'),
                        DB::raw('MONTH(created_at) as month'),
                        DB::raw('COUNT(*) as count'),
                        DB::raw('SUM(total) as total_value')
                    )
                    ->groupBy(DB::raw('YEAR(created_at)'), DB::raw('MONTH(created_at)'))
                    ->orderBy('year')
                    ->orderBy('month')
                    ->get()
            ];
            
            return $this->successResponse($stats, 'Statistics retrieved successfully');
            
        } catch (\Exception $e) {
            Log::error('Failed to get statistics', [
                'error' => $e->getMessage(),
                'user_id' => $auth['user_id'] ?? null
            ]);
            
            return $this->errorResponse(
                'Failed to retrieve statistics',
                'STATS_ERROR',
                500
            );
        }
    }
}
