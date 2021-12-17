<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;



class EmployeesController extends Controller
{
    public function register(Request $req)
    {
        $response = ["status" => 1, "msg" => ""];

        $validator = Validator::make(json_decode($req->getContent(), true), [

            "name" => 'required|max:50',
            "email" => 'required','email','unique:App\Models\Employee,email','max:30',
            "password" => 'required','regex:/(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[^A-Za-z0-9]).{6,}/',
            "work_role" => 'required|in:DIRECCIÓN,RRHH,EMPLEADO',
            "salary" => 'required','numeric',
            "bio" => 'required|max:150',

        ]);

        if ($validator->fails()) {

            return response()->json("");
          
        }

    }

    /*public function passwordRecovery(Request $req){
        //Obtener el email y validar

        //Al encontrar al usuario
        $employee->apit_token = null;

        $password = md5("newPass");
        $employee->password = Hash::make($password);
    }*/


    /*public function login(Request $req){
        //Buscar email
        $email = $req->email;
        //Validar

        //Encontrar usuario
        $employee = Employee::where('email',$email)->first();
        //Pasar la validacion

        //Comprobar la contraseña
        if(Hash::check($req->password, $employee->passsword)){
            //Generar api token
            do{
                $token  =Hash::make($usuario->id.now());
                //md5()
            }while(Employee::where('api_token',$token)->first());

            //Guardar token en usuario
            $employee->api_token = $token;
            $employee->save();

            //Devolver respuesta con el token
            return response()->json();

        }else{
            

        }
    }*/
}