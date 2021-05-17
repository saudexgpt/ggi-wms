<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\Warehouse\Warehouse;
use App\Models\Warehouse\UserWarehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\UserResource;
use App\Laravue\Models\User;

class WarehousesController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $user = $this->getUser();

        if ($user->isAdmin()) {
            $warehouses = Warehouse::with('users')->get();
        } else {

            $warehouses = $user->warehouses;
        }

        return response()->json(compact('warehouses'));
    }

    public function assignableUsers()
    {
        $staff_users = User::where('user_type', 'staff')->get();
        $assignable_users = [];
        foreach ($staff_users as $user) {
            if (!$user->isAdmin() && !$user->isAssistantAdmin()) {
                $assignable_users[] = $user;
            }
        }

        return response()->json(compact('assignable_users'));
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function addUserToWarehouse(Request $request)
    {
        //
        $user_ids = $request->user_ids;
        $warehouse_id = $request->warehouse_id;
        $warehouse = Warehouse::find($warehouse_id);
        $warehouse->users()->syncWithoutDetaching($user_ids);
        $warehouse_users = $warehouse->users;
        // $warehouse_user = UserWarehouse::where(['user_id'=> $user_id, 'warehouse_id' => $warehouse_id])->first();

        // if (!$warehouse_user) {
        //     $warehouse_user = new UserWarehouse();
        //     $warehouse_user->user_id = $user_id;
        //     $warehouse_user->warehouse_id = $warehouse_id;
        //     $warehouse_user->save();
        // }
        $actor = $this->getUser();
        $title = "Staff assigned to $warehouse->name";
        $description = "Staff assigned to $warehouse->name by $actor->name ($actor->email)";
        //log this activity
        $roles = ['assistant admin', 'warehouse manager'];
        $this->logUserActivity($title, $description, $roles);
        return response()->json(compact('warehouse_users'), 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Warehouse\Warehouse  $warehouse
     * @return \Illuminate\Http\Response
     */
    public function show(Warehouse $warehouse)
    {
        //
        return response()->json(compact('warehouse'), 200);
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function store(Request $request, Warehouse $warehouse)
    {
        //
        $name = $request->name;
        $address = $request->address;
        $warehouse = Warehouse::where('name', $name)->first();

        if (!$warehouse) {
            $warehouse = new Warehouse();
            $warehouse->name = $name;
            $warehouse->address = $address;
            $warehouse->save();
        }
        $actor = $this->getUser();
        $title = "Created new warehouse";
        $description = "$actor->name ($actor->email) created $warehouse->name";
        //log this activity
        $roles = ['assistant admin', 'warehouse manager'];
        $this->logUserActivity($title, $description, $roles);
        return $this->show($warehouse);
    }



    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Warehouse\Warehouse  $warehouse
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Warehouse $warehouse)
    {
        //
        $warehouse->name = $request->name;
        $warehouse->address = $request->address;
        $warehouse->enabled = $request->enabled;
        $warehouse->save();

        $actor = $this->getUser();
        $title = "Updated warehouse information";
        $description = "$actor->name ($actor->email) updated $warehouse->name information";
        //log this activity
        $roles = ['assistant admin', 'warehouse manager'];
        $this->logUserActivity($title, $description, $roles);
        return $this->show($warehouse);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Warehouse\Warehouse  $warehouse
     * @return \Illuminate\Http\Response
     */
    public function destroy(Warehouse $warehouse)
    {
        //
        $actor = $this->getUser();
        $title = "Deleted $warehouse->name";
        $description = "$actor->name ($actor->email) deleted $warehouse->name information";
        //log this activity
        $roles = ['assistant admin', 'warehouse manager'];
        $this->logUserActivity($title, $description, $roles);
        $warehouse->delete();
        return response()->json(null, 204);
    }
}
