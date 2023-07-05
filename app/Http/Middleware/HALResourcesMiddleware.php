<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Route;
use App\Contracts\HALResourcesInterface;
use Illuminate\Http\JsonResponse;

/**
 * The purpose of this middleware is to automatically prepend any HAL Resource data
 * defined within a controller (via HALResourcesInterface) to any JSON response it generates.
 */
class HALResourcesMiddleware {

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response {
        $ret = $next($request);

        // If a JSON response has been generated and the current route's controller 
        // implements HALResourcesInterface, then prepend the controller's HAL Resource
        // data to the beginning its output JSON data.
        // Also updates the Content-Type from application/json to application/hal+json.
        if ($ret instanceof JSonResponse) {            
            if (Route::current()->controller instanceof HALResourcesInterface) {
                $halResources = Route::current()->controller->getHALResources();

                // Merge hypermedia with result
                $originalStatus = $ret->status();
                $originalContent = $ret->getOriginalContent();

                $newContent = array_merge($halResources, $originalContent);

                $ret = response()->json($newContent, $originalStatus,
                        ['Content-Type', 'application/hal+json']);
            }
        }

        return $ret;
    }
}
