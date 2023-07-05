<?php

namespace App\Http\Controllers;

use JWTAuth;
use Validator;
use App\Models\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;
use App\Contracts\HALResourcesInterface;
use Illuminate\Support\Facades\Route;

class JWTAuthController extends Controller implements HALResourcesInterface {

    public function register(Request $request) {
        // Validate data
        $data = $request->only('name', 'email', 'password');
        $validator = Validator::make($data, [
                    'name' => 'required|string',
                    'email' => 'required|email|unique:users',
                    'password' => 'required|string|min:6|max:50'
        ]);

        // Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], Response::HTTP_BAD_REQUEST);
        }

        // Request is valid, create new user
        $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => bcrypt($request->password)
        ]);

        // User created, return success response
        return response()->json([
                    'success' => true,
                    'message' => 'User created successfully',
                    'data' => $user
                        ], Response::HTTP_OK);
    }

    public function login(Request $request) {
        $credentials = $request->only('email', 'password');

        // Valid credential
        $validator = Validator::make($credentials, [
                    'email' => 'required|email',
                    'password' => 'required|string|min:6|max:50'
        ]);

        // Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], Response::HTTP_BAD_REQUEST);
        }

        // Request is validated
        // Crean token
        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json([
                            'success' => false,
                            'message' => 'Login credentials are invalid.',
                                ], Response::HTTP_BAD_REQUEST);
            }
        } catch (JWTException $e) {
            return $credentials;
            return response()->json([
                        'success' => false,
                        'message' => 'Could not create token.',
                            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // Token created, return with success response and jwt token
        return response()->json([
                    'success' => true,
                    'token' => $token,
        ]);
    }

    public function logout(Request $request) {
        // Valid credential
        $validator = Validator::make($request->only('token'), [
                    'token' => 'required'
        ]);

        // Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], Response::HTTP_BAD_REQUEST);
        }

        // Request is validated, do logout        
        try {
            JWTAuth::invalidate($request->token);

            return response()->json([
                        'success' => true,
                        'message' => 'User has been logged out'
            ]);
        } catch (JWTException $exception) {
            return response()->json([
                        'success' => false,
                        'message' => 'Sorry, user cannot be logged out'
                            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get HAL resources for this controller
     * 
     * @return type
     */
    public function getHALResources() {
        $routes = Route::getRoutes();

        $links = [
            'self' => [
                'href' => url(Route::current()->uri)
            ],
            'register' => [
                'href' => url($routes->getByName('JWTAuthController.register')->uri)
            ],
            'login' => [
                'href' => url($routes->getByName('JWTAuthController.login')->uri)
            ],
            'logout' => [
                'href' => url($routes->getByName('JWTAuthController.logout')->uri)
            ]
        ];

        // Remove any links which are a duplicate of 'self' so the same uri is not listed twice.
        $links = array_filter($links, function ($v, $k) use ($links) {
            return $v['href'] !== $links['self']['href'] || $k === 'self';
        }, ARRAY_FILTER_USE_BOTH);

        return [
            '_links' => $links
        ];
    }
}
