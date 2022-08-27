<?php

namespace App\Http\Controllers\api\v1;


use App\Http\Controllers\Controller;
use App\Http\Resources\HistoryResources;
use App\Http\Resources\LoginResources;
use App\Http\Resources\v1\UserResource;
use App\Models\Token;
use App\Models\User;
use App\Models\UserLoginHistory;
use App\Repository\Eloquent\HistoriesRepository;
use App\Repository\Eloquent\UserRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{

    function GetRealIp()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP']))
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else
            $ip = $_SERVER['REMOTE_ADDR'];
        return $ip;
    }

    private $token;

    private $userRepo;

    public function __construct(UserRepository $userRepo)
    {
        $this->token = new Token();
        $this->userRepo = $userRepo;
    }

    public function register(Request $request)
    {
        $data = $request->all();

        $validator = Validator::make($request->all(), [
            'mobile' => 'required|max:11|unique:users',
        ], [
            'mobile.unique' => 'این شماره موبایل قبلا به ثبت رسیده است',
            'mobile.required' => 'شماره موبایل وارد نکرده اید',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $this->userRepo->create($data);

        if ($this->token->sendCode($request->input('mobile'))) {
            return $this->MessageResponse(['message' => 'کد تایید برای ثبت نام به شما ارسال خواهد شد', 'success' => true]);
        }
        return $this->MessageResponse(['message' => 'کد ورود ارسال نشد', 'success' => false], 422);
    }


    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mobile' => 'required|max:11|min:11',
        ], [
            'mobile.required' => 'شماره موبایل وارد نکرده اید',
            'mobile.max' => 'شماره موبایل حداکثر باید 11 کاراکتر باشد',
            'mobile.min' => 'شماره موبایل حداقل باید 11 کاراکتر باشد',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $user = $this->userRepo->findByCustomCol('mobile', $request->input('mobile'));
        if (!$user) {
            return $this->MessageResponse(['message' => 'این شماره موبایل وجود ندارد', 'success' => false], 422);
        }

        if ($this->token->sendCode($request->input('mobile'))) {
            return $this->MessageResponse(['message' => 'کد تایید برای ثبت نام به شما ارسال خواهد شد', 'success' => true]);
        }

        return $this->MessageResponse(['message' => 'کد ورود ارسال نشد', 'success' => false], 422);
    }

    public function verify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mobile' => 'required|max:11|min:11',
        ], [
            'mobile.required' => 'شماره موبایل وارد نکرده اید',
            'mobile.max' => 'شماره موبایل حداکثر باید 11 کاراکتر باشد',
            'mobile.min' => 'شماره موبایل حداقل باید 11 کاراکتر باشد',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $user = $this->userRepo->findByCustomCol('mobile', $request->input('mobile'));
        if (!$user) {
            return $this->MessageResponse(['message' => 'این شماره موبایل وجود ندارد', 'success' => false], 422);
        }

        $token = new Token();
        if ($token->verify($request->mobile, $request->code)) {
            auth()->login($user);
            $user = auth()->user();
            $tokenResult = $user->createToken('userToken');

            $ip = $this->GetRealIp();
            $user_agent = $request->header('User-Agent');

            UserLoginHistory::create(
                [
                    'ip' => $ip,
                    'agent' => $user_agent,
                    'user_id' => $user->id,
                ]
            );

            $userLoginHistory = new UserLoginHistory();
            $userLoginHistory->ip = $ip;
            $userLoginHistory->agent = $user_agent;
            $userLoginHistory->user_id = $user->id;
            $userLoginHistory->save();

            return $this->jsonResponse(new LoginResources(["message" => '', 'accessToken' => $tokenResult->accessToken, "success" => true]));
        }

        return $this->MessageResponse(['message' => 'نا موفق', 'success' => false], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return $this->MessageResponse(['message' => 'شما با موفقیت خارج شدید.', 'success' => true], 200);
    }

    public function history(Request $request)
    {
        $histories = $this->userRepo->all(['*'], ['login_history']);
        return $this->jsonResponse(new HistoryResources(["message" => '', 'histories' => $histories, "success" => true]));
    }
}
