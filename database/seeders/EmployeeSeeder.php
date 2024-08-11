<?php

namespace Database\Seeders;

use App\Models\Division;
use App\Models\Employee;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mobileAppsDivisionId = Division::where('name', 'Mobile Apps')->value('id');
        $qaDivisionId = Division::where('name', 'QA')->value('id');

        $employees = [
            [
                'name' => 'John Doe',
                'phone' => '123-456-7890',
                'image' => 'image1.jpg',
                'position' => 'Mobile Developer',
                'division_id' => $mobileAppsDivisionId,
            ],
            [
                'name' => 'Jane Smith',
                'phone' => '987-654-3210',
                'image' => 'image2.jpg',
                'position' => 'QA Engineer',
                'division_id' => $qaDivisionId,
            ],
        ];

        foreach ($employees as $employee) {
            Employee::create([
                'name' => $employee['name'],
                'phone' => $employee['phone'],
                'image' => $employee['image'],
                'position' => $employee['position'],
                'division_id' => $employee['division_id'],
            ]);
        }
    }
}
