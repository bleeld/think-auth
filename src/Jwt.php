<?php
declare (strict_types = 1);
namespace think;

use think\jwt\driver\JWE;
use think\jwt\driver\Key;

class Jwt
{
    //  定义变量
    private static $key;           //  密匙
    private static $expire_time;  //  过期时间
    private static $method;        //  需要加/解密的方法

    //  生成token
    /**
     * @method createToken 生成token
     * @param mixed|null $data 需要加密的数据，可以是字符串、数组、对象
     * @param int|null $expTime 过期时间的净值，比如一天后过期，该值为： 86400
     * 
     * @return array 返回加密后的数组
     */
    public static function createToken(mixed $data = null, int $expTime = null)
    {
        $time = time(); //当前时间
        if (is_null($expTime)) {
            $expTime = self::getExpireTime();   //  过期时间,这里设置2个小时
        }
        $payload = [
            'iss' => 'http://www.buddha.com',  // 签发者 可选
            'iat' => $time, //签发时间
            'nbf' => $time , //(Not Before)：某个时间点后才能访问，比如设置time+30，表示当前时间30秒后才能使用
            'exp' => $time + $expTime, 
            'data' => $data, //自定义信息，不要定义敏感信息
        ];
        $token = JWE::encode($payload, self::getKey(), self::getMethod()); //签发token
        $payload['exp'] += (int) $expTime * 11;
        $accessToken = JWE::encode($payload, self::getKey(), self::getMethod()); //签发token
        $data = ['code'=>200, 'mgs'=>'success', 'data'=>['token'=>$token, 'access_token'=>$accessToken]];
        return $data;
    }

    //  验证token
    public static function verifyToken(string $token)
    {
        $result = ['code'=>400, 'mgs'=>'failure', 'data'=>[]];
        try {
            JWE::$leeway = 60;//当前时间减去60，把时间留点余地
            $decoded = JWE::decode($token, new Key(self::getKey(),  self::getMethod())); //HS256方式，这里要和签发的时候对应
            $result = ['code'=>200, 'mgs'=>'success', 'data'=>(array) $decoded->data];
        } catch(\think\jwt\driver\SignatureInvalidException $e) {  //签名不正确
            $result['data'] = $e->getMessage();
        }catch(\think\jwt\driver\BeforeValidException $e) {  // 签名在某个时间点之后才能用
            $result['data'] = $e->getMessage();
        }catch(\think\jwt\driver\ExpiredException $e) {  // token过期
            $result['data'] = $e->getMessage();
        }catch(\Exception $e) {  //其他错误
            $result['data'] = $e->getMessage();
        } finally{
            return $result;
        }
    }

    public static function authorizations($data)
    {
        $time = time();  // 当前时间
        // 自定义信息
        $payload = [
            'iss' => 'http://www.buddha.com',  // 签发者 可选
            'iat' => $time, //签发时间
            'nbf' => $time , //(Not Before)：某个时间点后才能访问，比如设置time+30，表示当前时间30秒后才能使用
            'exp' => $time + self::getExpireTime(), //过期时间,这里设置2个小时
            'data' => $data, //自定义信息，不要定义敏感信息
        ];
        $access_token = $payload;
        $access_token['scopes'] = 'role_access';  // token标识，请求接口的token
        $access_token['exp'] = $time + self::getExpireTime();  // access_token过期时间,这里设置2个小时
    
        $refresh_token = $payload;
        $refresh_token['scopes'] = 'role_refresh';  // token标识，刷新access_token
        $refresh_token['exp'] = $time + (86400 * 30);  // access_token过期时间,这里设置30天
        return [
            'access_token' => JWE::encode($access_token, self::getKey(), self::getMethod()),
            'refresh_token' => JWE::encode($refresh_token, self::getKey(), self::getMethod()),
            'token_type' => 'bearer'  // token_type：表示令牌类型，该值大小写不敏感，这里用bearer
        ];
    }

    //  key 管理
	private static function getKey()
    {
		if(isset(self::$key) && self::$key)
        {
			return self::$key;
		}
		self::setKey();
		return self::$key;
	}
	private static function setKey(string $key = null)
    {
        self::$key = (isset($key) && !$key) ? $key : config('jwt.key');
	}

    //  expire_time 管理
	private static function getExpireTime()
    {
		if(isset(self::$expire_time) && self::$expire_time)
        {
			return self::$expire_time;
		}
		self::setExpireTime();
		return self::$expire_time;
	}
	private static function setExpireTime(int $expire_time = null)
    {
        self::$expire_time = (isset($expire_time) && !$expire_time) ? $expire_time : config('jwt.expire_time');
	}

    //  expire_time 管理
	private static function getMethod()
    {
		if(isset(self::$method) && self::$method)
        {
			return self::$method;
		}
		self::setMethod();
		return self::$method;
	}
	private static function setMethod($method = null)
    {
        self::$method = (isset($method) && !$method) ? $method : config('jwt.method');
	}


}
