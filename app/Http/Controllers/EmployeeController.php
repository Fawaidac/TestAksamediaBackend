<?php

namespace App\Http\Controllers;

use App\Models\Division;
use App\Models\Employee;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

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

    public function store(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Silahkan login dulu',
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:15',
            'division_name' => 'required|exists:divisions,name',
            'position' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $division = Division::where('name', $request->division_name)->first();

        if (!$division) {
            return response()->json([
                'status' => 'error',
                'message' => 'Divisi tidak ditemukan',
            ], 404);
        }

        $fileNameImage = time() . '.' . $request->image->extension();
        $request->image->move(public_path('images/'), $fileNameImage);

        Employee::create([
            'image' => $fileNameImage,
            'name' => $request->name,
            'phone' => $request->phone,
            'division_id' => $division->id,
            'position' => $request->position,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Data karyawan berhasil ditambahkan',
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $employee = Employee::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:255',
            'division_name' => 'sometimes|exists:divisions,name',
            'position' => 'sometimes|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 400);
        }

        if ($request->hasFile('image')) {
            $deleteimage = $employee->image;
            if ($deleteimage) {
                File::delete(public_path('images/') . '/' . $deleteimage);
            }

            $fileNameImage = time() . '.' . $request->image->extension();
            $request->image->move(public_path('images/'), $fileNameImage);
            $employee->image = $fileNameImage;
        }


        $division_id = $employee->division_id;
        if ($request->has('division_name')) {
            $division = Division::where('name', $request->input('division_name'))->firstOrFail();
            $division_id = $division->id;
        }

        $employee->update([
            'name' => $request->input('name', $employee->name),
            'phone' => $request->input('phone', $employee->phone),
            'division_id' => $division_id,
            'position' => $request->input('position', $employee->position),
        ]);


        return response()->json([
            'status' => 'success',
            'message' => 'Employee updated successfully',
        ]);
    }
}
