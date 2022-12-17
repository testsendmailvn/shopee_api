<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use App\Models\ShopeeInformation;
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    
    public function index()
    {         
        
        $path = "/api/v2/shop/auth_partner"; //without the host
        $timestamp = time();
        $partner_id = env('APP_SHOPEE_PARTNER_ID');
        $partner_key =  env('APP_SHOPEE_PARTNER_KEY');
        $sign = $this->sign($partner_id, $partner_key, $path, $timestamp);
        $host = env('APP_SHOPEE_HOST');
        $path = "/api/v2/shop/auth_partner"; //without the host
        $redirect = 'http://127.0.0.1:8000';
        $param = '?partner_id=' . $partner_id . '&redirect=' . $redirect . '&timestamp=' . $timestamp . '&sign=' . $sign;
        $data['url'] = $host . $path . $param;
        $model = ShopeeInformation::get();
        $data['model'] = $model;
        return view('home', $data);
    }
 
    function sign($partner_id, $partner_key, $path, $timestamp)
    {
        $baseString = sprintf("%s%s%s", $partner_id, $path, $timestamp);
        $sign = hash_hmac('sha256', $baseString, $partner_key);
        return $sign;
    }
    function signAccessToken()
    {
    }
    public function get($data)
    {

        
    }
    public function getShop($data)
    {
        $access_token = $data['access_token'];
        $shop_id = (int)$data['shop_id'];
        $partner_id = (int)$data['partner_id'];
        $partner_key = $data['partner_key'];
        $host = "https://partner.test-stable.shopeemobile.com";
        $path = "/api/v2/shop/get_shop_info";
        $timest = time();
        $baseString = sprintf("%s%s%s%s%s", $partner_id, $path, $timest, $access_token, $shop_id);
        $sign = hash_hmac('sha256', $baseString, $partner_key);

        $data = [
            'access_token' => $access_token,
            'shop_id' => $shop_id,
            'partner_id' => $partner_id,
            'timest' => $timest,
            'sign' => $sign
        ];
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $host. $path . '?partner_id='. (int)$partner_id .'&timestamp='. (int)$timest.'&access_token='. $access_token .'&shop_id='.$shop_id.'&sign=' . $sign,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }
    public function token(Request $request)
    {      
        
        $code = $request->code;
        $shop_id = (int)$request->shop_id;
        $partner_id = (int)$request->partner_id;
        $partner_key = $request->partner_key;
        $host = 'https://partner.test-stable.shopeemobile.com';
        $path = "/api/v2/auth/token/get";
        $timest = time();
        $body = array("code" => $code,  "shop_id" => $shop_id, "partner_id" => $partner_id);
        $baseString = sprintf("%s%s%s", $partner_id, $path, $timest);
        $sign = hash_hmac('sha256', $baseString, $partner_key);
        $url = sprintf("%s%s?partner_id=%s&timestamp=%s&sign=%s", $host, $path, $partner_id, $timest, $sign);

        $c = curl_init($url);
        curl_setopt($c, CURLOPT_POST, 1);
        curl_setopt($c, CURLOPT_POSTFIELDS, json_encode($body));
        curl_setopt($c, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        $resp = curl_exec($c);
        $ret = json_decode($resp, true);
        $data = '';
        $code = false;
        $message = '';
        if (isset($ret['error']) && $ret['error'] != "") {
            $code = false;
            $message = $ret['message'];
            $data = '';
        } else {
            $accessToken = $ret["access_token"];
            $newRefreshToken = $ret["refresh_token"];
            $expire_in = $ret['expire_in'];
           
            $code = true;
            $message = 'Lấy token thành công';
            //Get shop
            $newPath = '/api/v2/shop/get_shop_info';
            $newTimes = time();
            $newSign = $this->sign($partner_id, $partner_key, $newPath, time());

            $shop_data = [
                'partner_id' => $partner_id,
                'partner_key' => $partner_key,
                'access_token' => $accessToken,
                'shop_id' => $shop_id,
                'sign' => $newSign,
                'host' => $host,
                'path' => '/api/v2/shop/get_shop_info'
            ];
            $shop = json_decode($this->getShop($shop_data));
            $shop_name = $shop -> shop_name;
            $data = [
                'accessToken' => $accessToken,
                'newRefreshToken' => $newRefreshToken,
                'expired_time' => $expire_in,
                'shop_name' => $shop_name
            ];  
            $shopee_infomation_data = [
                'partner_id' => $partner_id,
                'partner_key' => $partner_key,                
                'shop_id' => $shop_id,
                'access_token' => $data['accessToken'],
                'refresh_token' =>$data['newRefreshToken'],
                'expired_time' => $data['expired_time'],
                'shop_name' => $data['shop_name'],
                'is_active' => true,
                'is_deleted' => null
            ];
            
        // Lưu vào db:
            
            $this->shopee_infomation($shopee_infomation_data);  

        }
           


        return [
            'code' => $code,
            'message' => $message,
            'data' => $data,
            'is_active' => true
        ];
    }
    public function shopee_infomation($data){
        
        $model = ShopeeInformation::where('shop_id' , $data['shop_id']) -> first();
        if($model == null){
             $shopee = new ShopeeInformation();
             $shopee -> partner_id = $data['partner_id'];
             $shopee -> partner_key = $data['partner_key'];
             $shopee -> shop_id = $data['shop_id'];
             $shopee -> access_token = $data['access_token'];
             $shopee -> refresh_token = $data['refresh_token'];
             $shopee -> expired_time = $data['expired_time'];
             $shopee -> shop_name = $data['shop_name'];
             $shopee -> is_active = $data['is_active'];
             $shopee -> is_deleted = $data['is_deleted'];          
             $shopee -> created_at = date('Y-m-d H:i:s');
             $shopee -> updated_at = date('Y-m-d H:i:s');
             $shopee -> save();
        }  
        else {
            $model-> partner_id = $data['partner_id'];
            $model-> partner_key = $data['partner_key'];
            $model-> shop_id = $data['shop_id'];
            $model-> access_token = $data['access_token'];
            $model-> refresh_token = $data['refresh_token'];
            $model-> expired_time = $data['expired_time'];
            $model-> shop_name = $data['shop_name'];
            $model-> is_active = $data['is_active'];
            $model-> is_deleted = $data['is_deleted'];          
            $model-> created_at = date('Y-m-d H:i:s');
            $model-> updated_at = date('Y-m-d H:i:s');
            $model-> save();
        }      
    }
}
