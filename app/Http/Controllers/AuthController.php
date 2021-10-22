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
        set_time_limit(0);
        $data = request(['email_username', 'password']);
        $user = User::where('email', $data['email_username'])->orWhere('user_name', $data['email_username'])->first();
        if (!$user) {
            return response(['message' => 'この情報は存在しません'], 404);
        }
        if ($user->role_id === 4) {
            return response(['message' => 'ログインする権限がありません'], 403);
        }
        if (!$user->active) {
            return response(['message' => 'アカウントがロックされました'], 403);
        }
        $credentials = [
            'email' => $user->email,
            'password' => $data['password']
        ];
        if (!$token = Auth::attempt($credentials)) {
            return response()->json(['message' => '間違ったパスワード'], 401);
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
            return response()->json(['message' => '間違ったパスワード'], 401);
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
                'message' => __('入力した内容に不備があります。入力項目を確認してください。'),
                'data' => [
                    $validator->errors()->all()
                ]
            ], 400);
        }
        
        $user =  User::where('email', $data['email_username'])->orWhere('user_name', $data['email_username'])->first();
        if(!$user){
            return response(['message' => 'このアカウントは存在していません。'], 404);
        };
        $verify = Cache::get($user->email);
        if(!$verify){
            return response(['message' => '認証コードの有効期限が過ぎています。再ログインしてください。!'], 422);
        }
        
        if($verify['code'] == $data['code']){
            
            $tokensSaved = [];
            if ($user->tokens != null) {
                $tokensSaved = $user->tokens;
            }

            // if ($user->tokens != null) {
            //     $tokensSaved = json_decode($user->tokens);
            // }
            // $tokensSaved[] =$verify['token'];
            // $user->update(['tokens' => json_encode($tokensSaved)]);
            
            $user->update(['tokens' => $tokensSaved]);
            return $this->respondWithToken($verify['token']);
        }else {
            return response(['message' => '認証コードは正しくありません。'], 401);
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
            // neu truoc do 1h ma khong dung den

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
