<?php
/**
 * 短信息发送
 */
namespace Hyperbolaa\Juhesms\src;

class Juhesms
{
    public static function send($tpl, $phonenum, $smsvalue)
    {
        header('content-type:text/html;charset=utf-8');

        $sendUrl           = 'http://v.juhe.cn/sms/send'; //短信接口的URL
        $smsConf           = array();
        $smsConf['key']    = '##'; //您申请的APPKEY
        $smsConf['mobile'] = $phonenum; //接受短信的用户手机号码
        switch ($tpl) {
            case 'code': //验证码
                $smsConf['tpl_id']    = 0000; //您申请的短信模板ID，根据实际情况修改
                $smsConf['tpl_value'] = '#name#=' . $phonenum . '&#code#=' . $smsvalue; //您设置的模板变量，根据实际情况修改
                break;
            case 'succ': //通过
                $smsConf['tpl_id']    = 1111;
                $arr                  = explode("&&&", $smsvalue);
                $smsConf['tpl_value'] = '#app#=' . $arr[0] . '&#code#=' . $arr[1];
                break;
            case 'fail': //拒绝
                $smsConf['tpl_id']    = 2222;
                $smsConf['tpl_value'] = '#app#=' . $smsvalue;
                break;
        }

        $content = Juhesms::juhecurl($sendUrl, $smsConf, 1); //请求发送短信

        if ($content) {
            $result     = json_decode($content, true);
            $error_code = $result['error_code'];
            if ($error_code == 0) {
                //状态为0，说明短信发送成功
                //echo "短信发送成功,短信ID：".$result['result']['sid'];exit;
                return 1;
            } else {
                //状态非0，说明失败
                //echo  "短信发送失败(".$error_code.")：".$result['reason'];exit;
                return -2;
            }
        } else {
            //返回内容异常，以下可根据业务逻辑自行修改
            //echo "请求发送短信失败";
            return -1;
        }
    }

    /**
     * 请求接口返回内容
     * @param  string $url [请求的URL地址]
     * @param  string $params [请求的参数]
     * @param  int $ipost [是否采用POST形式]
     * @return  string
     */
    public static function juhecurl($url, $params = false, $ispost = 0)
    {
        $httpInfo = array();
        $ch       = curl_init();

        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.22 (KHTML, like Gecko) Chrome/25.0.1364.172 Safari/537.22');
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($ispost) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            curl_setopt($ch, CURLOPT_URL, $url);
        } else {
            if ($params) {
                curl_setopt($ch, CURLOPT_URL, $url . '?' . $params);
            } else {
                curl_setopt($ch, CURLOPT_URL, $url);
            }
        }
        $response = curl_exec($ch);
        if ($response === false) {
            //echo "cURL Error: " . curl_error($ch);
            return false;
        }
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $httpInfo = array_merge($httpInfo, curl_getinfo($ch));
        curl_close($ch);
        return $response;
    }
}
