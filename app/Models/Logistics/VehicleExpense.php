<?php

namespace App\Models\Logistics;

use App\Laravue\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VehicleExpense extends Model
{
    use SoftDeletes;
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
    public function engineer()
    {
        return $this->belongsTo(AutomobileEngineer::class);
    }
    public function expenseDetails()
    {
        return $this->hasMany(VehicleExpenseDetail::class);
    }
    public function confirmer()
    {
        return $this->belongsTo(User::class, 'confirmed_by', 'id');
    }
}
