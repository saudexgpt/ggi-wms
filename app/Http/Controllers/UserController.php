<?php

/**
 * File UserController.php
 *
 * @author Tuan Duong <bacduong@gmail.com>
 * @package Laravue
 * @version 1.0
 */

namespace App\Http\Controllers;

use App\Customer;
use App\Driver;
use App\Http\Resources\PermissionResource;
use App\Http\Resources\UserResource;
use App\Laravue\JsonResponse;
use App\Laravue\Models\Permission;
use App\Laravue\Models\Role;
use App\Laravue\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Validator;

/**
 * Class UserController
 *
 * @package App\Http\Controllers
 */
class UserController extends Controller
{
    const ITEM_PER_PAGE = 10;

    /**
     * Display a listing of the user resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response|ResourceCollection
     */
    public function userNotifications()
    {
        $user = $this->getUser();
        $notifications = $user->unreadNotifications()->orderBy('created_at', 'DESC')->get();
        return response()->json(compact('notifications'), 200);
    }
    public function index(Request $request)
    {
        $searchParams = $request->all();
        $userQuery = User::query();
        $limit = Arr::get($searchParams, 'limit', static::ITEM_PER_PAGE);
        $role = Arr::get($searchParams, 'role', '');
        $keyword = Arr::get($searchParams, 'keyword', '');
        if (!empty($keyword)) {
            $userQuery->where(function ($q) use ($keyword) {
                $q->where('name', 'LIKE', '%' . $keyword . '%');
                $q->orWhere('email', 'LIKE', '%' . $keyword . '%');
                $q->orWhere('phone', 'LIKE', '%' . $keyword . '%');
                $q->orWhere('address', 'LIKE', '%' . $keyword . '%');
            });
        }
        if (!empty($role)) {
            $userQuery->whereHas('roles', function ($q) use ($role) {
                $q->where('name', $role);
            });
        } else {
            $userQuery->whereHas('roles', function ($q) {
                $q->where('name', '!=', 'customer');
                $q->where('name', '!=', 'driver');
            });
        }
        $userQuery->where('user_type', '!=', 'developer');

        return UserResource::collection($userQuery->paginate($limit));
    }
    public function addBulkCustomers(Request $request)
    {
        $actor = $this->getUser();
        $bulk_data = json_decode(json_encode($request->bulk_data));
        // try {
        $count = 0;
        foreach ($bulk_data as $data) {
            $name =  trim($data->CUSTOMER_NAME);
            $email =  trim($data->EMAIL);
            $phone =  trim($data->PHONE);
            $address =  trim($data->ADDRESS);
            // $address =  trim($data->ADDRESS);
            $password = $phone;
            if ($email == 'NIL') {
                $email = strtolower($this->randomCodeGenerator()) . '@gmail.com';
            }
            if ($phone == 'NIL') {
                $phone = time();
                $password = 'password';
            }
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => bcrypt($password),
                'phone' => $phone,
                'address' => $address,
                'user_type' => 'customer',

            ]);
            $role = Role::findByName('customer');
            $user->syncRoles($role);

            $new_user =  new UserResource($user);
            $customer = new Customer();
            $customer->user_id = $new_user->id;
            // $customer->customer_type = $request->customer_type;
            $customer->save();
            $count++;
        }

        $title = "Bulk upload of customers";
        $description = "Customers were added in bulk by " . $actor->name;
        $roles = ['assistant admin', 'warehouse manager'];
        $this->logUserActivity($title, $description, $roles);
        return response()->json([], 200);
    }
    public function addCustomer(Request $request)
    {
        //try {
        $email = $request->email;
        $phone = $request->phone;
        $password = $request->password;
        if ($email == null) {
            $email = 'default' . time() . '@gmail.com';
        }
        if ($phone == null) {
            $phone = time();
            $password = 'password';
        }
        $user = User::create([
            'name' => $request->name,
            'email' => $email,
            'password' => bcrypt($password),
            'phone' => $phone,
            'address' => $request->address,
            'user_type' => 'customer',

        ]);
        $role = Role::findByName($request->role);
        $user->syncRoles($role);

        $new_user =  new UserResource($user);
        //$this->store($request, 'customer'); //save customer's user details

        $customer = new Customer();
        $customer->user_id = $new_user->id;
        $customer->customer_type = $request->customer_type;
        $customer->save();
        $customer->user = $customer->user;
        // log this activity
        $actor = $this->getUser();
        $title = "New customer added";
        $description = ucwords($new_user->name) . " was added as new customer by $actor->name ($actor->email)";
        $roles = ['assistant admin', 'warehouse manager'];
        $this->logUserActivity($title, $description, $roles);
        return response()->json(compact('customer'), 200);
        // } catch (\Exception $exception) {
        //     return response()->json(compact('exception'), 500);
        // }



    }
    public function addDriver(Request $request)
    {
        try {
            $new_user = $this->store($request); //save customer's user details

            $driver = new Driver();
            $driver->user_id = $new_user->id;
            $driver->employee_no = $request->employee_no;
            $driver->license_no = $request->license_no;
            $driver->license_issue_date = date('Y-m-d H:i:s', strtotime($request->license_issue_date));
            $driver->license_expiry_date = date('Y-m-d H:i:s', strtotime($request->license_expiry_date));
            $driver->emergency_contact_details = $request->emergency_contact_details;
            $driver->save();
            $driver->user = $driver->user;

            // log this activity
            $user = $this->getUser();
            $title = "New driver added";
            $description = ucwords($new_user->name) . " was added as new driver by $user->name ($user->email)";
            $roles = ['assistant admin', 'warehouse manager'];
            $this->logUserActivity($title, $description, $roles);
            return response()->json(compact('driver'), 200);
        } catch (\Exception $exception) {
            return response()->json(['message' => 'Check your form for duplicate entries like email'], 500);
        }
    }
    public function updateDriver(Request $request, Driver $driver)
    {
        $user = User::where('email', $request->email)->first();
        $this->update($request, $user);
        $driver->employee_no = $request->employee_no;
        $driver->license_no = $request->license_no;
        $driver->license_issue_date = date('Y-m-d H:i:s', strtotime($request->license_issue_date));
        $driver->license_expiry_date = date('Y-m-d H:i:s', strtotime($request->license_expiry_date));
        $driver->emergency_contact_details = $request->emergency_contact_details;
        $driver->save();
        $driver->user = $driver->user;
        // log this activity
        $title = "Driver information updated";
        $description = ucwords($user->name) . "'s information was updated.";
        $roles = ['assistant admin', 'warehouse manager'];
        $this->logUserActivity($title, $description, $roles);

        return response()->json(compact('driver'), 200);
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $type = "staff")
    {
        $validator = Validator::make(
            $request->all(),
            array_merge(
                $this->getValidationRules(),
                [
                    'password' => ['required', 'min:6'],
                    'confirmPassword' => 'same:password',
                ]
            )
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 403);
        } else {
            $params = $request->all();
            $user = User::create([
                'name' => $params['name'],
                'email' => $params['email'],
                'phone' => $params['phone'],
                'address' => $params['address'],
                'user_type' => $type,
                'password' => Hash::make($params['password']),
            ]);
            $actor = $this->getUser();
            $title = "New Registration";
            $description = ucwords($user->name) . "'s was newly registered by $actor->name ($actor->email)";
            $this->logUserActivity($title, $description);

            $role = Role::findByName($params['role']);
            $user->syncRoles($role);

            return new UserResource($user);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  User $user
     * @return UserResource|\Illuminate\Http\JsonResponse
     */
    public function show(User $user)
    {
        return new UserResource($user);
    }

    public function assignRole(Request $request, User $user)
    {
        $actor = $this->getUser();
        if ($actor->isAdmin()) {
            $role = Role::findByName($request->role);
            $user->syncRoles($role);
            $title = "User assigned role";
            $description = ucwords($user->name) . " was assigned the role of " . $request->role . " by $actor->name ($actor->email)";
            $this->logUserActivity($title, $description);
            return new UserResource($user);
        }
    }
    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param User    $user
     * @return UserResource|\Illuminate\Http\JsonResponse
     */
    public function update(Request $request, User $user)
    {
        if ($user === null) {
            return response()->json(['error' => 'User not found'], 404);
        }
        // if ($user->isAdmin()) {
        //     return response()->json(['error' => 'Admin can not be modified'], 403);
        // }

        $currentUser = Auth::user();
        if (
            !$currentUser->isAdmin()
            && $currentUser->id !== $user->id
            && !$currentUser->hasPermission(\App\Laravue\Acl::PERMISSION_USER_MANAGE)
        ) {
            return response()->json(['error' => 'Permission denied'], 403);
        }

        $validator = Validator::make($request->all(), $this->getValidationRules(false));
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 403);
        } else {
            $email = $request->get('email');
            $phone = $request->get('phone');
            $address = $request->get('address');
            $found = User::where('email', $email)->first();
            if ($found && $found->id !== $user->id) {
                return response()->json(['error' => 'Email has been taken'], 403);
            }

            $user->name = $request->get('name');
            $user->email = $email;
            $user->phone = $phone;
            $user->address = $address;
            $user->save();

            $actor = $this->getUser();
            $title = "Profile Update";
            $description = ucwords($user->name) . "'s information was modified by $actor->name ($actor->email)";
            $this->logUserActivity($title, $description);
            return new UserResource($user);
        }
    }
    public function randomCodeGenerator()
    {
        $tokens = 'abcdefghABCDEFGH0123456789'; //'ABCDEF0123456789';
        $serial = '';
        for ($i = 0; $i < 6; $i++) {
            $serial .= $tokens[mt_rand(0, strlen($tokens) - 1)];
        }
        return $serial;
    }
    public function adminResetUserPassword(Request $request, User $user)
    {
        $currentUser = Auth::user();
        if (
            !$currentUser->isAdmin()
            && !$currentUser->hasPermission(\App\Laravue\Acl::PERMISSION_USER_MANAGE)
        ) {
            return response()->json(['error' => 'Permission denied'], 403);
        }
        $new_password = $this->randomCodeGenerator();
        $user->password = Hash::make($new_password);
        $user->password_status = 'default';
        $user->save();

        $actor = $currentUser;
        $title = "Password Reset";
        $description = ucwords($user->name) . "'s password was reset by $actor->name ($actor->email)";
        $this->logUserActivity($title, $description);
        return response()->json(['new_password' => $new_password], 200);
    }
    public function updatePassword(Request $request, User $user)
    {
        if ($user === null) {
            return response()->json(['error' => 'User not found'], 404);
        }
        // if ($user->isAdmin()) {
        //     return response()->json(['error' => 'Admin can not be modified'], 403);
        // }

        $currentUser = Auth::user();
        if (
            !$currentUser->isAdmin()
            && $currentUser->id !== $user->id
            && !$currentUser->hasPermission(\App\Laravue\Acl::PERMISSION_USER_MANAGE)
        ) {
            return response()->json(['error' => 'Permission denied'], 403);
        }
        $validator = Validator::make(
            $request->all(),
            array_merge(
                $this->getValidationRules(false),
                [
                    'password' => ['required', 'min:6'],
                    'confirmPassword' => 'same:password',
                ]
            )
        );
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 403);
        } else {
            $params = $request->all();
            $user->password = Hash::make($params['password']);
            $user->password_status = 'custom';
            $user->save();

            $actor = $this->getUser();
            $title = "Password updated";
            $description = ucwords($user->name) . "'s password was updated by $actor->name ($actor->email)";
            $this->logUserActivity($title, $description);
            return new UserResource($user);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param User    $user
     * @return UserResource|\Illuminate\Http\JsonResponse
     */
    public function updatePermissions(Request $request, User $user)
    {
        if ($user === null) {
            return response()->json(['error' => 'User not found'], 404);
        }

        if ($user->isAdmin()) {
            return response()->json(['error' => 'Admin can not be modified'], 403);
        }

        $permissionIds = $request->get('permissions', []);
        $rolePermissionIds = array_map(
            function ($permission) {
                return $permission['id'];
            },

            $user->getPermissionsViaRoles()->toArray()
        );

        $newPermissionIds = array_diff($permissionIds, $rolePermissionIds);
        $permissions = Permission::allowed()->whereIn('id', $newPermissionIds)->get();
        $user->syncPermissions($permissions);

        // log this action
        $actor = $this->getUser();
        $title = "Granted Permissions";
        $description = "$actor->name ($actor->email) granted permissions to roles.";
        //log this activity
        $this->logUserActivity($title, $description);
        return new UserResource($user);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  User $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        if ($user->isAdmin()) {
            response()->json(['error' => 'Ehhh! Can not delete admin user'], 403);
        }

        try {
            $actor = $this->getUser();
            $title = "User Deleted";
            $description = ucwords($user->name) . " was deleted by " . $actor->name;
            $this->logUserActivity($title, $description);
            $user->delete();
        } catch (\Exception $ex) {
            return response()->json(['error' => $ex->getMessage()], 403);
        }

        return response()->json(null, 204);
    }

    public function destroyCustomer(User $user)
    {
        $customer = Customer::where('user_id', $user->id)->first();
        try {
            $actor = $this->getUser();
            $title = "Customer Deleted";
            $description = ucwords($user->name) . " was deleted by " . $actor->name;
            $this->logUserActivity($title, $description);
            $customer->delete();
            $user->delete();
        } catch (\Exception $ex) {
            return response()->json(['error' => $ex->getMessage()], 403);
        }

        return response()->json(null, 204);
    }

    /**
     * Get permissions from role
     *
     * @param User $user
     * @return array|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function permissions(User $user)
    {
        try {
            return new JsonResponse([
                'user' => PermissionResource::collection($user->getDirectPermissions()),
                'role' => PermissionResource::collection($user->getPermissionsViaRoles()),
            ]);
        } catch (\Exception $ex) {
            response()->json(['error' => $ex->getMessage()], 403);
        }
    }

    /**
     * @param bool $isNew
     * @return array
     */
    private function getValidationRules($isNew = true)
    {
        return [
            'name' => 'required',
            'email' => $isNew ? 'required|email|unique:users' : 'required|email',
            'roles' => [
                'required',
                'array'
            ],
        ];
    }
}
