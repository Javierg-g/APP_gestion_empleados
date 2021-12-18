<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Employee;

class PermissionValidation
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
        if ($request->employee->work_role == "RRHH" || $request->employee->work_role == "Dirección") {
            return $next($request);
        } else {
            $response['msg'] = "El usuario actual no posee los permisos necesarios";
        }
        return response()->json($response);
    }
}
