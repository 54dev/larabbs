<?php

namespace App\Http\Controllers\Api;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Overtrue\EasySms\EasySms;
use App\Http\Requests\Api\VerificationCodeRequest;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class VerificationCodesController extends Controller
{
    public function store(VerificationCodeRequest $request, EasySms $easySms)
    {
        $captchaData = \Cache::get($request->captcha_key);

        if(!$captchaData){
            abort(403,'图片验证码已失效');
        }
        if(!hash_equals($captchaData['code'], $request->captcha_code)){
            \Cache::forget($request->captcha_key);
            throw new AuthenticationException('验证码错误');
        }

        $phone = $captchaData['phone'];

        $code = str_pad(random_int(1,9999), 4, 0, STR_PAD_LEFT);

//        try{
//            $result = $easySms->send($phone, [
//                'template' => config('easysms.gateways.aliyun.templates.register'),
//                'data' => ['code' =>$code]
//            ]);
//        } catch(\Overtrue\EasySms\Exceptions\NoGatewayAvailableException $exception) {
//            $message = $exception->getException('aliyun')->getMessage();
//            abort(500, $message?:'error');
//        }
        Log::info('phone:'.$phone.',code:'.$code);
        $key = 'verificationCode_'.Str::random(15);
        $expiredAt = now()->addMinute(5);
        \Cache::put($key, ['phone' => $phone, 'code' => $code], $expiredAt);

        return response()->json([
            'key' => $key,
            'expired_at' => $expiredAt->toDateTimeString(),
        ])->setStatusCode(201);
    }
}
