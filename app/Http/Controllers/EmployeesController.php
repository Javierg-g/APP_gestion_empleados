<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Mail\PasswordRecovered;
use Illuminate\Support\Facades\DB;


class EmployeesController extends Controller
{
    public function register(Request $req)
    {
        $response = ["status" => 1, "msg" => ""];

        $validator = Validator::make(json_decode($req->getContent(), true), [

            "name" => 'required|max:50',
            "email" => 'required|email|unique:App\Models\Employee,email|max:40',
            "password" => 'required|regex:/(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[^A-Za-z0-9]).{6,}/',
            "work_role" => 'required|in:Dirección,RRHH,Empleado',
            "salary" => 'required', 'numeric',
            "bio" => 'required|max:150'

        ]);

        if ($validator->fails()) {
            $response['status'] = "0";
            print("Errores de la validación de la edición:" . $validator->errors());
            $response['msg'] = "Los campos introducidos no son correctos";

            return response()->json($response);
        } else {

            $data = json_decode($req->getContent());

            $employee = new Employee();

            $employee->name = $data->name;
            $employee->email = $data->email;
            $employee->password = Hash::make($data->password);
            $employee->work_role = $data->work_role;
            $employee->salary = $data->salary;
            $employee->bio = $data->bio;


            try {
                $employee->save();
                $response['msg'] = "Empleado registrado con exito";
            } catch (\Exception $e) {
                $response['status'] = 0;
                $req['msg'] = "Se ha producido un error" . $e->getMessage();
            }

            return response()->json($response);
        }
    }

    public function login(Request $req)
    {
        $response = ["status" => 1, "msg" => ""];

        $data = json_decode($req->getContent());

        try {

            if (Employee::where('email', '=', $data->email)->first()) {
                $employee = Employee::where('email', '=', $data->email)->first();
                if (Hash::check($data->password, $employee->password)) {
                    do {
                        $token = Hash::make($employee->id . now());
                    } while (Employee::where('api_token', $token)->first());

                    $employee->api_token = $token;
                    $employee->save();
                    $response['msg'] = $token;

                    $users = Employee::where('api_token', '=', $token)->first();
                    $role = Employee::where('employees.id', '=', $users->id)
                    ->select('employees.work_role')
                    ->get(); 

                    $response['employeelist'] = $role;
                
                } else {
                    $response['status'] = 0;
                    $response['msg'] = "La contraseña es incorrecta";
                }
            } else {
                $response['status'] = 0;
                $response['msg'] = "El usuario no se ha encontrado";
            }
        } catch (\Exception $e) {
            $response['status'] = 0;
            $response['msg'] = "Se ha producido un error" . $e->getMessage();
        }
        return response()->json($response);
    }

    public function passwordRecovery(Request $req)
    {

        $data = json_decode($req->getContent());

        try {

            if (Employee::where('email', '=', $data->email)->first()) {
                $employee = Employee::where('email', '=', $data->email)->first();

                $employee->api_token = null;

                $newPassword = md5("newPass");
                $employee->password = Hash::make($newPassword);
                $employee->save();


                Mail::to($employee->email)->send(new PasswordRecovered($newPassword));
                $response['msg'] = "Correo enviado a la dirección = " . $employee->email;
            } else {
                $response['msg'] = "El usuario no se ha encontrado";
            }
        } catch (\Exception $e) {
            $response['status'] = 0;
            $response['msg'] = "Se ha producido un error" . $e->getMessage();
        }
        return response()->json($response);
    }

    public function getlistEmployees(Request $req)
    {

        $response = ["status" => 1, "msg" => ""];

        $token = $req->query('api_token');

        try {
            if (Employee::where('api_token', '=', $token)->first()) {

                $employee = Employee::where('api_token', '=', $token)->first();

                if ($employee->work_role == 'RRHH') {
                    $employeeList = DB::table('employees')
                        ->select('name', 'work_role', 'salary')
                        ->where('work_role', 'like', 'Empleado')
                        ->get();
                } else if ($employee->work_role == 'Dirección') {
                    $employeeList = DB::table('employees')
                        ->select('name', 'work_role', 'salary')
                        ->where('work_role', 'like', 'Empleado')
                        ->orWhere('work_role', 'like', 'RRHH')
                        ->get();
                }
                $response['employeeList'] = $employeeList;
            }
        } catch (\Exception $e) {

            $response['status'] = 0;
            $response['msg'] = "Se ha producido un error: " . $e->getMessage();
        }

        return response()->json($response);
    }

    public function getEmployeeDetail(Request $req)
    {

        $response = ["status" => 1, "msg" => ""];

        $token = $req->query('api_token');
        $targetEmployeeId = $req->query('targetEmployeeId');

        $targetEmployee = Employee::find($targetEmployeeId);

        try {
            if (Employee::where('api_token', '=', $token)->first()) {

                $employee = Employee::where('api_token', '=', $token)->first();

                if ($targetEmployee) {
                    if ($employee->work_role == 'RRHH') {
                        $employeeDetails = DB::table('employees')
                            ->select('name', 'email', 'work_role', 'bio', 'salary')
                            ->where('id', '=', $targetEmployeeId)
                            ->where('work_role', 'like', 'Empleado')
                            ->get();
                    } else if ($employee->work_role == 'Dirección') {
                        $employeeDetails = DB::table('employees')
                            ->select('name', 'email', 'work_role', 'bio', 'salary')
                            ->where('id', '=', $targetEmployeeId)
                            ->where(function ($role) {
                                $role->where('work_role', 'like', 'Empleado')
                                    ->orWhere('work_role', 'like', 'RRHH');
                            })

                            ->get();
                    }
                    $response['msg'] = $employeeDetails;
                } else {

                    $response['status'] = 0;
                    $response['msg'] = "Usuario no existe";
                }
            }
        } catch (\Exception $e) {

            $response['status'] = 0;
            $response['msg'] = "Se ha producido un error: " . $e->getMessage();
        }

        return response()->json($response);
    }

    public function profile(Request $req)
    {
        $token = $req->query('api_token');

        try {
            if (Employee::where('api_token', '=', $token)->first()) {

                $employee = Employee::where('api_token', '=', $token)->first();

                $employeeList = DB::table('employees')
                    ->select('employees.name','employees.work_role','employees.bio','employees.salary')
                    ->where('id', '=', $employee->id)
                    ->get();

                $response['employeeDetails'] = $employeeList;
            }
        } catch (\Exception $e) {

            $response['status'] = 0;
            $response['msg'] = "Se ha producido un error: " . $e->getMessage();
        }

        return response()->json($response);
    }

    public function edit(Request $req)
    {
        $data = json_decode($req->getContent());
        $token = $req->query('api_token');

        $employee = Employee::find($req->editId);

        if ($employee) {
            $validator = Validator::make(json_decode($req->getContent(), true), [

                "name" => 'required|max:50',
                "email" => 'required|email|unique:App\Models\Employee,email|max:40',
                "password" => 'required|regex:/(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[^A-Za-z0-9]).{6,}/',
                "work_role" => 'required|in:Dirección,RRHH,Empleado',
                "salary" => 'required', 'numeric',
                "bio" => 'required|max:150'

            ]);

            if ($validator->fails()) {
                $response['status'] = "0";
                print("Errores de la validación de la edición:" . $validator->errors());
                $response['msg'] = "Los campos introducidos no son correctos";

                return response()->json($response);
            } else {

                if (isset($data->name)) {
                    $employee->name = $data->name;
                }
                if (isset($data->email)) {
                    $employee->email = $data->email;
                }
                if (isset($data->password)) {
                    $employee->password = Hash::make($data->password);
                }
                if (isset($data->work_role)) {
                    $employee->work_role = $data->work_role;
                }
                if (isset($data->salary)) {
                    $employee->salary = $data->salary;
                }
                if (isset($data->bio)) {
                    $employee->bio = $data->bio;
                }

                try {
                    $employee->save();
                    $response['msg'] = "Los cambios han sido guardados";
                } catch (\Exception $e) {
                    $response['status'] = 0;
                    print($e);
                    $response['msg'] = "Se ha producido un error" . $e->getMessage();
                }
            }
        } else {
            $response['msg'] = "El usuario no existe";
        }

        return response()->json($response);
    }
}
