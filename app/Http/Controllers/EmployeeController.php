<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployeeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Silahkan login dulu',
            ], 401);
        }

        $query = Employee::query()->with('division');

        if ($request->has('name') && !empty($request->input('name'))) {
            $query->where('name', 'like', '%' . $request->input('name') . '%');
        }

        if ($request->has('division_name') && !empty($request->input('division_name'))) {
            $query->whereHas('division', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->input('division_name') . '%');
            });
        }

        $employees = $query->paginate(10);

        return response()->json([
            'status' => 'success',
            'message' => 'Data retrieved successfully',
            'data' => [
                'employees' => $employees->items(),
            ],
            'pagination' => [
                'total' => $employees->total(),
                'current_page' => $employees->currentPage(),
                'per_page' => $employees->perPage(),
                'last_page' => $employees->lastPage(),
                'from' => $employees->firstItem(),
                'to' => $employees->lastItem(),
            ],
        ]);
    }
}
