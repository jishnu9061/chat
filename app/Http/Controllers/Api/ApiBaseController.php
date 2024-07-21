<?php
/**
 * Created By: JISHNU T K
 * Date: 2024/05/22
 * Time: 22:19:11
 * Description: ApiBaseController.php
 */
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller as Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Http\Controllers\Api\type;
use App\Traits\ApiResponseTrait;

class ApiBaseController extends Controller
{
    use ApiResponseTrait;

    protected function getCollectionValues($value)
    {
        if (! is_array($value) && ! is_null($value)) {
            $value = explode(',', str_replace(['[', ']'], '', $value));
        }
        return $value;
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
