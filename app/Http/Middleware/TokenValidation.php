<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Employee;

class TokenValidation
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {

        if (isset($request->api_token)) {
            $tokenForApi = $request->api_token;
            if($employee = Employee::where('api_token', $tokenForApi)->first()){
                $employee = Employee::where('api_token', $tokenForApi)->first();
                $request->employee = $employee;
                return $next($request);

            }else{
                $response['msg'] = "El token no es correcto";
            }

        } else {
            $response['msg'] = "El token no ha sido introducido";
        }
        return response()->json($response);
    }
}
