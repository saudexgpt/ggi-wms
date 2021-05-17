<?php

namespace App\Http\Controllers;

use App\Models\Invoice\Invoice;
use App\Models\Logistics\Vehicle;
use App\Models\Stock\Item;
use App\Models\Stock\ItemStock;
use App\Models\Warehouse\Warehouse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function adminDashboard()
    {
        $warehouses = Warehouse::get();
        $warehouses_count = $warehouses->count();
        $vehicles_count = Vehicle::count();
        $products_count = Item::count();
        $pending_invoices_count = Invoice::where('full_waybill_generated', '0')->count();
        // $pending_invoices_count = Invoice::where('status', 'pending')->count();

        return response()->json([
            'data_summary' => compact('warehouses_count', 'vehicles_count', 'products_count', 'pending_invoices_count'),
            'warehouses' => $warehouses,
        ], 200);
    }
}
