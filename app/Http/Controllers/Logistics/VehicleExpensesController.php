<?php

namespace App\Http\Controllers\Logistics;

use App\Http\Controllers\Controller;
use App\Models\Logistics\AutomobileEngineer;
use App\Models\Logistics\VehicleExpense;
use App\Models\Logistics\VehicleExpenseDetail;
use Illuminate\Http\Request;

class VehicleExpensesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $warehouse_id = $request->warehouse_id;
        $status = $request->status;
        if ($status !== 'All') {
            $option = ['warehouse_id' => $warehouse_id, 'status' => $status];
        } else {
            $option = ['warehouse_id' => $warehouse_id];
        }
        if (isset($request->from, $request->to, $request->status) && $request->from != '' && $request->from != '' && $request->status != '') {
            $date_from = date('Y-m-d', strtotime($request->from)) . ' 00:00:00';
            $date_to = date('Y-m-d', strtotime($request->to)) . ' 23:59:59';
            $status = $request->status;
            $panel = $request->panel;
            $vehicle_expenses = VehicleExpense::with(['confirmer', 'vehicle', 'expenseDetails', 'engineer'])->where($option)->where('created_at', '>=', $date_from)->where('created_at', '<=', $date_to)->orderBy('id', 'DESC')->get();
        } else {
            $status = $request->status;
            $vehicle_expenses = VehicleExpense::with(['confirmer', 'vehicle', 'expenseDetails', 'engineer'])->where($option)->orderBy('id', 'DESC')->get();
        }

        return response()->json(compact('vehicle_expenses'), 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $vehicle_expense = new VehicleExpense();
        $vehicle_expense->warehouse_id = $request->warehouse_id;
        $vehicle_expense->vehicle_id = $request->vehicle_id;
        $vehicle_expense->expense_type = $request->expense_type;
        $vehicle_expense->automobile_engineer_id = $request->automobile_engineer_id;
        $vehicle_expense->details = $request->details;
        $vehicle_expense->amount = $request->amount;
        $vehicle_expense->service_charge = $request->service_charge;
        $vehicle_expense->grand_total = $request->grand_total;
        $vehicle_expense->status = $request->status;


        // $vehicle_expense->service_date = date('Y-m-d H:i:s', strtotime($request->service_date));
        if ($vehicle_expense->save()) {
            $servicing_details = json_decode(json_encode($request->servicing_details));
            foreach ($servicing_details as $servicing_detail) {
                if ($servicing_detail->vehicle_part != null) {
                    $vehicle_expense_detail = new VehicleExpenseDetail();
                    $vehicle_expense_detail->warehouse_id = $request->warehouse_id;
                    $vehicle_expense_detail->vehicle_expense_id = $vehicle_expense->id;

                    $vehicle_expense_detail->vehicle_part = $servicing_detail->vehicle_part;
                    $vehicle_expense_detail->service_type = $servicing_detail->service_type;
                    $vehicle_expense_detail->amount = $servicing_detail->amount;
                    $vehicle_expense_detail->save();
                }
            }
        }
        $actor = $this->getUser();
        $title = "Vehicle Expenses Request";
        $description = "Vehicle (" . $vehicle_expense->vehicle->plate_no . ") expenses request was made by $actor->name ($actor->email) on " . $vehicle_expense->created_at;
        $roles = ['assistant admin', 'warehouse manager', 'warehouse auditor'];
        $this->logUserActivity($title, $description, $roles);
        return $this->show($vehicle_expense);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Logistics\VehicleExpense  $vehicleExpense
     * @return \Illuminate\Http\Response
     */
    public function show(VehicleExpense $vehicleExpense)
    {
        $vehicle_expense = $vehicleExpense->with(['confirmer', 'vehicle', 'expenseDetails', 'engineer'])->find($vehicleExpense->id);
        return response()->json(compact('vehicle_expense'), 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Logistics\VehicleExpense  $vehicleExpense
     * @return \Illuminate\Http\Response
     */
    public function edit(VehicleExpense $vehicleExpense)
    {
        //
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Logistics\VehicleExpense  $vehicleExpense
     * @return \Illuminate\Http\Response
     */
    public function approval(Request $request, VehicleExpense $vehicleExpense)
    {
        $vehicleExpense->status = $request->status;
        $vehicleExpense->save();
        // log this action
        $actor = $this->getUser();
        $title = "Vehicle Expenses Approval";
        $description = "Vehicle (" . $vehicleExpense->vehicle->plate_no . ") expenses request, made on " . $vehicleExpense->created_at . ", is  $vehicleExpense->status by $actor->name ($actor->email)";
        $roles = ['assistant admin', 'warehouse manager', 'warehouse auditor'];
        $this->logUserActivity($title, $description, $roles);
        return $this->show($vehicleExpense);
    }

    public function addAutomobileEngineer(Request $request)
    {
        try {
            $company_name = $request->company_name;
            $automobile_engineer = new AutomobileEngineer();
            $automobile_engineer->name = $request->name;
            $automobile_engineer->phone_no = $request->phone_no;
            $automobile_engineer->email = $request->email;
            $automobile_engineer->company_name = $company_name;
            $automobile_engineer->workshop_address = $request->workshop_address;
            $automobile_engineer->save();

            // log this activity
            $actor = $this->getUser();
            $title = "New automobile engineer added";
            $description = ucwords($automobile_engineer->name) . " was added as company automobile engineer. From $company_name by $actor->name ($actor->email)";
            $roles = ['assistant admin', 'warehouse manager', 'warehouse auditor'];
            $this->logUserActivity($title, $description, $roles);
            return response()->json(compact('automobile_engineer'), 200);
        } catch (\Exception $exception) {
            return response()->json(compact('exception'), 500);
        }
    }

    public function updateAutomobileEngineer(Request $request, AutomobileEngineer $automobile_engineer)
    {
        try {
            $automobile_engineer->name = $request->name;
            $automobile_engineer->phone_no = $request->phone_no;
            $automobile_engineer->email = $request->email;
            $automobile_engineer->company_name = $request->company_name;
            $automobile_engineer->workshop_address = $request->workshop_address;
            $automobile_engineer->save();

            // log this activity
            $actor = $this->getUser();
            $title = "Automobile engineer info updated";
            $description = ucwords($automobile_engineer->name) . "'s automobile engineer info was updated $actor->name ($actor->email)";
            $roles = ['assistant admin', 'warehouse manager', 'warehouse auditor'];
            $this->logUserActivity($title, $description, $roles);
            return response()->json(compact('automobile_engineer'), 200);
        } catch (\Exception $exception) {
            return response()->json(compact('exception'), 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Logistics\VehicleExpense  $vehicleExpense
     * @return \Illuminate\Http\Response
     */
    public function destroy(VehicleExpense $vehicleExpense)
    {
        //
    }
}
