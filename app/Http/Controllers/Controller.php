<?php

namespace App\Http\Controllers;

use App\ActivityLog;
use App\Customer;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\UserResource;
use App\Laravue\Models\Role;
use App\Laravue\Models\User;
use App\Models\Invoice\InvoiceStatus;
use App\Models\Logistics\AutomobileEngineer;
use App\Models\Logistics\VehicleType;
use App\Models\Order\OrderStatus;
use App\Models\Setting\Currency;
use App\Models\Setting\CustomerType;
use App\Models\Setting\Setting;
use App\Models\Setting\Tax;
use App\Models\Stock\Item;
use App\Models\Stock\ItemStockSubBatch;
use App\Models\Warehouse\Warehouse;
use Notification;
use App\Notifications\AuditTrail;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $user;
    public function __construct()
    {
        // $this->middleware('guest')->except('logout');
        $this->checkForNegativeTransitProduct();
    }

    private function checkForNegativeTransitProduct()
    {

        $items_in_stock = ItemStockSubBatch::where('in_transit', '<', 0)->get();
        if ($items_in_stock->isNotEmpty()) {
            foreach ($items_in_stock as $item_in_stock) {
                $in_transit = $item_in_stock->in_transit;
                $item_in_stock->in_transit = 0;
                $item_in_stock->supplied += $in_transit;
                $item_in_stock->save();
            }
        }
    }
    public function uploadFile(Request $request)
    {
        if ($request->file('avatar') != null && $request->file('avatar')->isValid()) {
            $mime = $request->file('avatar')->getClientMimeType();

            if ($mime == 'image/png' || $mime == 'image/jpeg' || $mime == 'image/jpg' || $mime == 'image/gif') {
                $name = time() . "." . $request->file('avatar')->guessClientExtension();
                $folder = "items";
                $avatar = $request->file('avatar')->storeAs($folder, $name, 'public');

                return response()->json(['avatar' => 'storage/' . $avatar], 200);
            }
        }
    }

    public function setUser()
    {
        $this->user  = new UserResource(Auth::user());
    }

    public function getUser()
    {
        $this->setUser();

        return $this->user;
    }
    public function fetchCustomers()
    {
        $customers = Customer::with(['user', 'type'])->get();
        $customer_types = CustomerType::get();
        return response()->json(compact('customers', 'customer_types'), 200);
    }
    public function fetchNecessayParams()
    {
        $user = $this->getUser();
        $all_warehouses = Warehouse::with(['itemStocks.item.taxes', 'itemStocks.item.price', 'vehicles'])->where('enabled', 1)->get();
        if ($user->isAdmin() || $user->isAssistantAdmin()) {
            $warehouses = $all_warehouses;
        } else {
            $warehouses = $user->warehouses()->with(['itemStocks.item.taxes', 'itemStocks.item.price', 'vehicles'])->where('enabled', 1)->get();
        }
        $items = Item::with(['taxes', 'price'])->orderBy('name')->get();
        $currencies = Currency::get();
        $taxes = Tax::get();
        $order_statuses = OrderStatus::get();
        $invoice_statuses = InvoiceStatus::get();
        $company_name = $this->settingValue('company_name');
        $company_contact = $this->settingValue('company_contact');
        $currency = $this->settingValue('currency');
        $product_expiry_date_alert = $this->settingValue('product_expiry_date_alert_in_months');
        $vehicle_types = VehicleType::get();
        $automobile_engineers = AutomobileEngineer::get();
        $engine_types = ['Diesel', 'Petrol', 'Kerosene'];
        $expense_types = ['Insurance', 'Maintenance / Repairs', 'Fuel'];
        $package_types = ['BAG', 'BOTTLE', 'BUCKET', 'CYLINDER', 'DRUM', 'IBCS', 'JUMBO', 'KEG', 'SACK', 'TABLET'];
        $unit_of_measurement = ['L' => 'Litres', 'Kg' => 'Kilogram'];
        $product_return_reasons = ['Product short-dated', 'Mass return - expired', 'Mass return - unexpired', 'Spillage', 'Others'];
        $teams = ['customer'];
        $dispatch_companies = ['GGI LOGISTICS', 'COURIER SERVICE', 'FOB (Free On Board)'];
        $all_roles = Role::orderBy('name')->select('name')->get();
        $default_roles = Role::where('role_type', 'default')->orderBy('name')->select('name')->get();

        return response()->json([
            'params' => compact('company_name', 'company_contact', 'all_warehouses', 'warehouses', 'items', 'currencies', 'taxes', 'order_statuses', 'invoice_statuses', 'currency', 'vehicle_types', 'engine_types', 'expense_types', 'package_types', 'automobile_engineers', 'all_roles', 'default_roles', 'product_return_reasons', 'product_expiry_date_alert', 'teams', 'dispatch_companies', 'unit_of_measurement')
        ]);
    }
    public function settingValue($key)
    {
        return Setting::where('key', $key)->first()->value;
    }
    public function generalSettings()
    {
        $settings = Setting::get();
        return response()->json(compact('settings'), 200);
    }
    public function nextReceiptNo($key_prefix = 'invoice')
    {
        $prefix = $this->settingValue($key_prefix . '_number_prefix');
        $no_of_digits = $this->settingValue($key_prefix . '_number_digit');
        $next_no = $this->settingValue($key_prefix . '_number_next');

        $digit_of_next_no = strlen($next_no);
        $unused_digit = $no_of_digits - $digit_of_next_no;
        $zeros = '';
        for ($i = 1; $i <= $unused_digit; $i++) {
            $zeros .= '0';
        }

        $invoice_no = $prefix . $zeros . $next_no;
        return $invoice_no;
    }

    public function incrementReceiptNo($key_prefix = 'invoice')
    {
        $next_no = $this->settingValue($key_prefix . '_number_next');
        $setting = Setting::where('key', $key_prefix . '_number_next')->first();
        $setting->value = $next_no + 1;
        $setting->save();
        return $setting;
    }

    public function logUserActivity($title, $description, $roles = [])
    {
        // $user = $this->getUser();
        // if ($role) {
        //     $role->notify(new AuditTrail($title, $description));
        // }
        // return $user->notify(new AuditTrail($title, $description));
        // send notification to admin at all times
        $users = User::whereHas('roles', function ($query) {
            $query->where('name', '=', 'admin'); // this is the role id inside of this callback
        })->get();

        if (in_array('assistant admin', $roles)) {
            $assistant_admin = User::whereHas('roles', function ($query) {
                $query->where('name', '=', 'assistant admin'); // this is the role id inside of this callback
            })->get();
            $users = $users->merge($assistant_admin);
        }
        if (in_array('warehouse manager', $roles)) {
            $warehouse_managers = User::whereHas('roles', function ($query) {
                $query->where('name', '=', 'warehouse manager'); // this is the role id inside of this callback
            })->get();
            $users = $users->merge($warehouse_managers);
        }
        if (in_array('stock officer', $roles)) {
            $stock_officers = User::whereHas('roles', function ($query) {
                $query->where('name', '=', 'stock officer'); // this is the role id inside of this callback
            })->get();
            $users = $users->merge($stock_officers);
        }
        if (in_array('warehouse auditor', $roles)) {
            $auditors = User::whereHas('roles', function ($query) {
                $query->where('name', '=', 'warehouse auditor'); // this is the role id inside of this callback
            })->get();
            $users = $users->merge($auditors);
        }
        // var_dump($users);
        $notification = new AuditTrail($title, $description);
        return Notification::send($users->unique(), $notification);
        // $activity_log = new ActivityLog();
        // $activity_log->user_id = $user->id;
        // $activity_log->action = $action;
        // $activity_log->user_type = $user->roles[0]->name;
        // $activity_log->save();
    }

    public function getInvoiceNo($prefix, $next_no)
    {
        $no_of_digits = 5;

        $digit_of_next_no = strlen($next_no);
        $unused_digit = $no_of_digits - $digit_of_next_no;
        $zeros = '';
        for ($i = 1; $i <= $unused_digit; $i++) {
            $zeros .= '0';
        }

        return $prefix . $zeros . $next_no;
    }
}
