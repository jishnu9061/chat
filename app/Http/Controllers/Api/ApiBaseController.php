<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller as Controller;

use App\Http\Constants\UserConstants;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Http\Controllers\Api\type;

/**
 * Created By: JISHNU T K
 * Date: 2024/05/22
 * Time: 22:19:11
 * Description: ApiBaseController.php
 */

class ApiBaseController extends Controller
{

    protected $_token;

    // public function __construct(Request $request)
    // {
    //     parent::__construct($request);
    //     $this->_token = $this->getAccessToken($request);
    // }

    protected function getCollectionValues($value)
    {
        if (! is_array($value) && ! is_null($value)) {
            $value = explode(',', str_replace(['[', ']'], '', $value));
        }
        return $value;
    }


    /**
     * Send success response
     *
     * @param array $data
     * @param string $message
     * @param type $status
     * @return \Illuminate\Http\Response
     */
    protected function sendResponse($data = [], $message = '', $status = true)
    {
    	$response = [
            'status' => $status,
            'data'    => $data,
            'message' => $message,
            'access_token' => $this->_token,
            'token_type' => 'bearer'
        ];
        return response()->json($response, 200);
    }

    /**
     * Send error response
     *
     * @param $message
     * @param array $errorTrace
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    protected function sendError($message, $errorTrace = [], $code = 200)
    {
    	$response = [
            'status' => false,
            'message' => $message,
        ];

        if (! empty($errorTrace)) {
            $response['data'] = $errorTrace;
            $errorMessage = [];
            foreach ($errorTrace as $error) {
                $errorMessage[] = $error[0] ?? '';
            }
            $response['message'] = implode(', ', $errorMessage);
        }

        return response()->json($response, $code);
    }

    /**
     *
     * @param array $data
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($data = [])
    {
        $data = [
            'status' => true,
            'access_token' => $this->generateAccessToken(),
            'token_type' => 'bearer',
            'data' => $data,
        ];
        return response()->json($data);
    }

    /**
     * Get Logged user details
     *
     * @return object
     */
    protected function getLoggedUserDetails()
    {
        return auth()->user();
    }

    /**
     * Get Logged User Id
     *
     * @return object
     */
    protected function getLoggedUserId()
    {
        return auth()->user()->id;
    }

    /**
     * Get Access token
     *
     * @param Request $request
     * @return type
     */
    protected function getAccessToken(Request $request)
    {
        $header = $request->header('Authorization');
        return (Str::startsWith($header, 'Bearer '))? Str::substr($header, 7) : '';
    }

    protected function generateAccessToken()
    {
        $user = auth()->user();
        return $user->createToken(env('API_TOKEN_NAME', 'api-name'))->accessToken;
    }

}
