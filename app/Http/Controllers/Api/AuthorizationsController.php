<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Auth\AuthenticationException;
use App\Models\User;
use App\Http\Requests\Api\SocialAuthorizationRequest;
use App\Http\Requests\Api\AuthorizationRequest;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Api\WeappAuthorizationRequest;

class AuthorizationsController extends Controller
{
    public function socialStore($type, SocialAuthorizationRequest $request)
    {
        $driver = \Socialite::driver($type);

        try{
            if($code = $request->code){
                $response = $driver->getAccessTokenResponse($code);
                $token = Arr::get($response, 'access_token');
            }else{
                $token = $request->access_token;

                if($type == 'weixin'){
                    $driver->setOpenId($request->openid);
                }
            }

            $oauthUser = $driver->userFromToken($token);
        } catch (\Exception $e) {
            throw new AuthenticationException('参数错误，未获取到用户信息');
        }

        switch ($type){
            case 'weixin':
                $unionid = $oauthUser->offsetExists('unionid')? $oauthUser->offsetGet('unionid'):null;
                if($unionid){
                    $user = User::where('weixin_unionid', $unionid)->first();
                }else{
                    $user = User::where('weixin_openid', $oauthUser->getId())->first();
                }

                if(!$user){
                    $user = User::create([
                        'name' => $oauthUser->getNickname(),
                        'avatar' => $oauthUser->getAvatar(),
                        'weixin_openid' => $oauthUser->getId(),
                        'weixin_unionid' => $unionid,
                    ]);
                }

                break;

        }
        $token = auth('api')->login($user);

        return $this->responseWithToken($token)->setStatusCode(201);
    }

    public function store(AuthorizationRequest $request)
    {
        $username = $request->username;

        filter_var($username, FILTER_VALIDATE_EMAIL) ?
            $credentials['email'] = $username :
            $credentials['phone'] = $username;
        $credentials['password'] = $request->password;

        if(!$token = \Auth::guard('api')->attempt($credentials)){
            throw new AuthenticationException(trans('auth.failed'));
        }

        return $this->responseWithToken($token)->setStatusCode(201);
    }

    protected function responseWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ]);
    }

    public function update()
    {
        $token = auth('api')->refresh();
        return $this->responseWithToken($token);
    }

    public function destroy()
    {
        auth('api')->logout();
        return response(null,204);
    }
    /*
     * https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx3d11fc1005398e96&redirect_uri=http://larabbs.test&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect
     */
    public function weappStore(WeappAuthorizationRequest $request)
    {
        $code = $request->code;

        $miniProgram = \EasyWeChat::miniProgram();
        $data = $miniProgram->auth->session($code);

        if(isset($data['errcode'])){
            throw new AuthenticationException('code 不正确');
        }

        $user = User::where('weapp_openid', $data['openid'])->first();

        $attributes['weixin_session_key'] = $data['session_key'];

        if(!$user){
            if(!$request->username){
                throw new AuthenticationException('用户不存在');
            }

            $username = $request->username;

            filter_var($user, FILTER_VALIDATE_EMAIL)?
                $credentials['email'] = $username:
                $credentials['phone'] = $username;

            $credentials['password'] = $request->password;

            if(!auth('api')->once($credentials)){
                throw new AuthenticationException('用户名或者密码错误');
            }

            $user = auth('api')->getUser();
            $attributes['weapp_openid'] = $data['openid'];
        }

        $user->update($attributes);

        $token =  auth('api')->login($user);

        return $this->responseWithToken($token)->setStatusCode(201);
    }
}
