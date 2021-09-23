<?php

namespace App\Http\Controllers;

use App\Mail\VerifyEmail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use PhpParser\Node\Stmt\TryCatch;

use function GuzzleHttp\json_decode;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'refresh', 'loginMobile', 'sendEmail', 'verifyLogin']]);
    }

    public function login()
    {
        $data = request(['email_username', 'password']);
        $user = User::where('email', $data['email_username'])->orWhere('user_name', $data['email_username'])->first();
        if (!$user) {
            return response(['message' => 'Email hoặc tên đăng nhập không tồn tại'], 404);
        }
        if ($user->role_id === 4) {
            return response(['message' => 'Bạn không có quyền đăng nhập'], 403);
        }
        if (!$user->active) {
            return response(['message' => 'Tài khoản của bạn đã bị đóng'], 403);
        }
        $credentials = [
            'email' => $user->email,
            'password' => $data['password']
        ];
        if (!$token = Auth::attempt($credentials)) {
            return response()->json(['message' => 'Mật khẩu không chính xác'], 401);
        }
        $code = rand(100000,999999);
        $verify = ['token' => $token, 'code' => $code];
        $minutes = 6; // Thoi gian het han
        Cache::put($user->email, $verify, Carbon::now()->addMinutes($minutes));
        $data = ['name' => $user->name, 'code' => $code, 'minutes'=> $minutes];
        $this->sendEmail( $data, $user->email);
        return response(['messae' => 'Done'], 200);
    }

    public function loginMobile()
    {
        $data = request(['email_username', 'password']);
        $user = User::where('email', $data['email_username'])->orWhere('user_name', $data['email_username'])->first();
        if (!$user) {
            return response(['message' => 'Email hoặc tên đăng nhập không tồn tại'], 404);
        }
        $credentials = [
            'email' => $user->email,
            'password' => $data['password']
        ];
        if (!$token = Auth::attempt($credentials)) {
            return response()->json(['message' => 'Mật khẩu không chính xác'], 401);
        }
        $tokensSaved = [];
        if ($user->tokens != null) {
            $tokensSaved = json_decode($user->tokens);
        }
        $tokensSaved[] = $token;
        $user->update(['tokens' => json_encode($tokensSaved)]);
        return $this->respondWithToken($token);
    }

    public function sendEmail($data, $email){
        Mail::to($email)->send(new VerifyEmail($data));
        return response(['message' => 'Da gui'], 200);
        
    }
    public function verifyLogin(Request $request){
        $data = $request->only('email_username', 'code');
        $validator =  Validator::make($data, [
            'email_username' => 'required',
            'code' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' => __('Dữ liệu không hợp lệ'),
                'data' => [
                    $validator->errors()->all()
                ]
            ], 400);
        }
        $user =  User::where('email', $data['email_username'])->orWhere('user_name', $data['email_username'])->first();
        if(!$user){
            return response(['message' => 'Người dùng không tồn tại'], 404);
        };
        $verify = Cache::get($user->email);
        if(!$verify){
            return response(['message' => 'Đã hết thời gian xác minh!'], 422);
        }
        if($verify['code'] == $data['code']){
            $tokensSaved = [];
            if ($user->tokens != null) {
                $tokensSaved = json_decode($user->tokens);
            }
            $tokensSaved[] =$verify['token'];
            $user->update(['tokens' => json_encode($tokensSaved)]);
            return $this->respondWithToken($verify['token']);
        }else {
            return response(['message' => 'Mã xác thực không đúng'], 401);
        }

    }
    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        $user = Auth::user();
        if ($user) {
            return User::where('id', $user->id)->with('role:id,name,code,description')->first();
        }
        return [];
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $userId = Auth::user()->id;
        $user = User::where('id', $userId)->first();
        try {
            if ($user->tokens != null) {
                $token = $request->bearerToken();
                $tokensSaved = json_decode($user->tokens);
                $token_index =  array_search($token, $tokensSaved);
                unset($tokensSaved[$token_index]);
                $user->update(['tokens' => json_encode($tokensSaved)]);
            }
            Auth::logout();
            return response()->json(['message' => 'Successfully logged out']);
        } catch (\Exception $e) {
            $user->update(['tokens' => null]);
            Auth::logout();
            return response()->json(['message' => 'Successfully logged out']);
        }
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        try {
            return $this->respondWithToken(Auth::refresh());
        } catch (\Exception $e) {
            return response(['message' => 'Token không hợp lệ'], 500);
        }
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::factory()->getTTL() * 60
        ]);
    }
}
