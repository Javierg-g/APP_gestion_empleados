<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;


class EmployeesController extends Controller
{
    public function register(Request $req)
    {

        $password = $req->password;

        $valido = true;

        if($password){
            if(!preg_match(/[a-z]{6,}/, $password])){
                $valido = false;
            }

        }else{
            $valido = false;

        }

        $usuario->password = Hash::make($req->password);



        /*
        $respuesta = ["Status" => 1, "msg"];

        $datos = $req->getContent();

        $datos = json_decode($datos);

        //Validar datos
        $usuario = new Employee();

        $usuario->nombre = $datos->nombre;
        $usuario->foto = $datos->foto;
        $usuario->email = $datos->email;
        $usuario->contraseña = $datos->contraseña;
        $usuario->activado = $datos->activado = 1;

        //Escribir en BBDD
        try {
            $usuario->save();
            $respuesta['msg'] = "Usuario guardado con id" . $usuario->id;
        } catch (\Exception $e) {
            $respuesta['status'] = 0;
            $respuesta['msg'] = "Se ha producido un error" . $e->getMessage();
        }

        return response()->json($respuesta);
    }

    public function desactivar($id)
    {
        $respuesta = ["Status" => 1, "msg" => ""];
        $usuario = Usuario::find($id);

        if ($usuario && $usuario->activado == 1) {

            try {
                $usuario->activado = 0;
                $usuario->save();
                $respuesta['msg'] = "Usuario desactivado";
            } catch (\Exception $e) {
                $respuesta['status'] = 0;
                $respuesta['msg'] = "Se ha producido un error" . $e->getMessage();
            }
        } else if (!$usuario->activado == 1) {
            $respuesta["msg"] = "El usuario ya estaba desactivado";
            $respuesta["status"] = 0;
        } else {
            $respuesta["msg"] = "Usuario no econtrada";
            $respuesta["status"] = 0;
        }

        return response()->json($respuesta);*/
    }


    public function login(Request $req){
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
    }
}