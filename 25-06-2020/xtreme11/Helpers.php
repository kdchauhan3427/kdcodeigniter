<?php
namespace App\Helpers;
Use Config;
Use Redirect;
Use Session;
Use Input;
Use HTML;
Use URL;
Use DB;
Use Firebase;
Use Push;
Use Mail;
Use Response;
Use Image;
Use Swift_SmtpTransport;
Use Swift_Mailer;
use App\Http\Controllers\FriendController;
use App\Http\Controllers\BlockController;
use App\Http\Controllers\RegisteruserController;
use App\Http\Controllers\FeedController;
use Twilio\Rest\Client;
use App\Registerusers;
use App\Mail\SendMailable;

class Helpers
{
   public static function mailSmtpSend1($datamessage){
		$backup = Mail::getSwiftMailer();
		$transport = Swift_SmtpTransport::newInstance('smtp.gmail.com', 587, 'tls');
		$transport->setUsername('contact@Xtreme.co.in');
		$transport->setPassword('aiptrlzdfkupchge');
		$gmail = new Swift_Mailer($transport);
		Mail::setSwiftMailer($gmail);
		Mail::send('emails.commonmail', $datamessage, function ($m) use ($datamessage){
			$m->from($datamessage['fromemail'], 'select2win');
			$m->to($datamessage['email'])->subject($datamessage['subject']);
		});
	}
	public static function mailSmtpSend($datamessage){
	    
		Mail::to($datamessage['email'])->send(new SendMailable($datamessage['content'], $datamessage['subject']));

		// Mail::send('emails.commonmail', $datamessage, function ($m) use ($datamessage){
		// 	$m->from('help.xtreme11@gmail.com', 'Xtreme11 Fantasy Cricket');
		// 	$m->to($datamessage['email'])->subject($datamessage['subject']);
		// });

			// $headers = "MIME-Version: 1.0" . "\r\n";
			// $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
			// $headers .= 'From: Xtreme11';
			// mail($datamessage['email'],$datamessage['subject'],$datamessage['content'],$headers,"-f help.xtreme11@gmail.com");
	}
	public static function projectName()
    {
		 return 'Xtreme11';
    } 
    	//search by value
	public static function searchByValue($products, $field, $value){
		foreach($products as $key => $product)
	{
		if ( $product[$field] === $value )
			return $key;
	}
	return false;
	}
	public static function getmainmail()
    {
		 return 'rohit19.img@gmail.com';
    } 
    public static function getUserNameShow($name,$email){
		if($name!=""){
			return ucwords($name);
		}else{
			return $email;
		}
    }
	public static function controllerName(){
		$routeArray = app('request')->route()->getAction();
		$controllerAction = class_basename($routeArray['controller']);
		list($controller, $action) = explode('@', $controllerAction);
		return $controller;
	}
	public static function actionName(){
		$routeArray = app('request')->route()->getAction();
		$controllerAction = class_basename($routeArray['controller']);
		list($controller, $action) = explode('@', $controllerAction);
		return $action;
	}
	public static function errormessage($errors){
		$content="";
		if($errors->any()){
			$content= implode('', $errors->all('<div class="alert error-message">:message</div>'));
		}
		return $content;
	}
	public static function getSessionMessage(){
		$content="";
		if (Session::has('message')){
			$content.='<div class="alert alert-info">'.Session::get('message').'</div>';
		}
		return $content;
	}
	public static function flashMessage(){
		if (Session::has('message')){
			$class="";
			$class = Session::get('alert-class');
			$message = Session::get('message');
			$content='<div class="alert '.$class.'">'.$message.'</div>';
			return $content;
		}
		else{
			$content="";
			return $content;
		}
	}
	
	public static function sendTextSmsNew($txtmsg,$mobile){
		$apiKey = urlencode('Hw0MDG9UD0s-1ymSly3csi9n8mU2w841JNXTVHEeHt');
		$numbers = str_replace('$$',',',$mobile);

		$numbers = array($numbers);
		$sender = urlencode('XTREME');
		$message = rawurlencode($txtmsg);
	 
		$numbers = implode(',', $numbers);
	 
		// Prepare data for POST request
		$data = array('apikey' => $apiKey, 'numbers' => $numbers, "sender" => $sender, "message" => $message);
		// Send the POST request with cURL
		$ch = curl_init('https://api.textlocal.in/send/');
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($ch);
		// dd($response);
		curl_close($ch);
	}
	
	public static function sendTextSMS($txtmsg,$mobile){
		// $mobile=str_replace('$$',',',$mobile);
		// $txtmsg=rawurlencode($txtmsg);
		// $url="http://sms.imgglobalinfotech.com/api/send_http.php?authkey=edf856329b1152b03f960f3e9c5c9855&mobiles=".$mobile."&message=".$txtmsg."&sender=MIMESY&route=B";
		// $ch = curl_init();
		// curl_setopt($ch, CURLOPT_URL, $url);
		// curl_setopt($ch, CURLOPT_HEADER, 0);
		// curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		// curl_exec($ch);
		// curl_close($ch);
		require_once "vendor/twilio-php-master/Twilio/autoload.php";
		$sid = 'AC4d8b4a0a15f38fa3b300170f1c2d815c';
		$token = '00c7579b97c9c37e03b2eb5f3f54ce8c';
		// $clientvar = new Client($sid, $token);
		// $clientvar->messages->create(
			// $mobile,
			// array(
				// 'from' => '+1 716-221-8525',
				// 'body' => $txtmsg
			// )
		// );
		// 		MAIL_HOST=Goreal11.com
		// MAIL_PORT=587
		// MAIL_USERNAME=Goreal11@Goreal11.com
		// MAIL_PASSWORD=HW8Zc7Iu
		// MAIL_ENCRYPTION=tls

	}

	public static function CurrentTime(){
		date_default_timezone_set('Asia/Kolkata');
		$currentdate = date('Y-m-d h:i:s');
	}

	/* get url function to get the main url */
	public static function geturl(){
		return 'http://206.189.129.171/xtreme_admin/';
	}
	/* get the access rules for header */
	public static function accessrules(){
		header('Access-Control-Allow-Origin: *'); 
		header("Access-Control-Allow-Credentials: true");
		header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
		header('Access-Control-Max-Age: 1000');
		header('Access-Control-Allow-Headers: Authorization');
		header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');
	}
	/* to set the timezone  */
	public static function timezone(){
		date_default_timezone_set('Asia/Kolkata'); 
	}
    /* to get the status code of the api */
    public static function _getStatusCodeMessage($status)
    {
        $code = [
            200 => 'OK',
            400 => 'Bad Request',
            401 => 'Unauthorized Request',
            403 => 'Forbidden',
            404 => 'Not Found',
            500 => 'Internal Server Error',
            501 => 'Not Implemented'
        ];
        return (isset($code[$status])) ? $code[$status] : "";
    }
    /* to set the header of the api for particular status */
    public static function setHeader($status)
    {
       header('Access-Control-Allow-Origin: *'); 
       header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Access-Control-Allow-Origin, Authorization, Pragma, Expires, Cache-Control'); 
       header('Access-Control-Allow-Methods: POST, GET, OPTIONS'); 
       header('Access-Control-Allow-Credentials: true');
       $status_header = 'HTTP/1.1 ' . $status . ' ' . Helpers::_getStatusCodeMessage($status);
       header($status_header);
       
    }
    /* that function is used to check the authentication */
   public static function isAuthorize($request){
      
       if($request->header('authorization')){
            $auth_key = $request->header('authorization');
			if(isset($auth_key) && $auth_key != "") {
				$dataa = explode(" ",$auth_key);
				if(isset($dataa[1])){
					$main_key = $dataa[1];
				}else{
					$main_key = $auth_key;
				}
				$model = Registerusers::where('auth_key', $main_key)->select('id','status')->first();
				if( !empty($model) ){

					if($model->status == 'activated') {							
						return $model;
					} else {
						return false;
					}
				}
				else{
					$json['success'] = false;
					$json['msg'] = 'You cannot access this page';
		        	echo json_encode($json,401);die;
				}
			}
			else{
				//Helpers::setHeader(401);
				$json['success'] = false;
				$json['msg'] = 'You cannot access this page';
	        	echo json_encode($json,401);die;
			}
		}else{
			//Helpers::setHeader(401);
			$json['success'] = false;
			$json['msg'] = 'You cannot access this page';
			echo json_encode($json,401);die;
		}
		
	}
	public static function UploadImage($file,$thumbnailPath,$fileName){
   	$extension = $file->getClientOriginalExtension();
   	// echo $extension;die;
   	// if($extension!='jpeg'){
   	//     return true;
   	// }else{
   	//     return false;
   	// }
        $ext = array("jpg","jpeg","png", "gif", "zip", "bmp","JPG","pdf");
        if(!in_array($extension, $ext)){
        return false;
        }
        $wid=150;
        $fileName=$fileName.'.'.$extension;
           $originalImage=$file;
                $thumbnailImage = Image::make($originalImage);
                $thumbnailImage->save($thumbnailPath.$fileName);
                $destination_url = $thumbnailPath.$fileName;
           list($width,$height)=getimagesize($destination_url);
           if($width>$wid){
               $newwidth=$wid;
        $newheight=($height/$width)*$newwidth;
        $thumbnailImage->resize($newwidth,$newheight);
           }
       
       $thumbnailImage->save($thumbnailPath.$fileName);
       return $fileName;
}
	public static function imageExtension($file){
		$filename = $file->getClientOriginalName();
		$extension = $file->getClientOriginalExtension();
		$ext = array('jpg','JPG','jpeg','gif','png');
		if(!in_array($extension, $ext)){
			return false;
		}
		return true;
	}
	public static function imageSingleUploadJson($file,$destinationPath,$fileName){
		$filename = $file->getClientOriginalName();
			$extension = $file->getClientOriginalExtension();
			$ext = array("jpg","jpeg","png", "gif", "zip", "bmp","JPG","pdf");
			if(!in_array($extension, $ext)){
				$Json['success'] = false;
				$Json['msg'] = "Invalid extension of file you uploaded. You can only upload image or pdf.";
				echo json_encode($Json);die;
			}  
			$newfilename = $fileName.'.'.$extension;
			if(file_exists($destinationPath.'/'.$newfilename)){
				$info=pathinfo($newfilename);
				$imageNamee=$info['filename'].'-'.rand(100,999);
				$newfilename=$imageNamee.".".$info['extension'];
			}
			$resi = $destinationPath .'/'. $newfilename;
			$upload_success = $file->move($destinationPath, $newfilename);
			$wid = 500;
			$resizeimage=Helpers::resize_image($resi,$wid);
			$resizeimage=Helpers::compress_image($resi,100);
			
			return $newfilename;
		}
	public static function imageSingleUpload($file,$destinationPath,$fileName){
			$filename = $file->getClientOriginalName();
			$extension = $file->getClientOriginalExtension();
			$ext = array("jpg","jpeg","png", "gif", "bmp","JPG");
			
			if(!in_array($extension, $ext)){
				return false;
			}  
			$newfilename = $fileName.'.'.$extension;
			if(file_exists($destinationPath.'/'.$newfilename)){
				$info=pathinfo($newfilename);
				$imageNamee=$info['filename'].'.'.$fileName;
				$newfilename=$imageNamee.'.'.$extension;
			}
			$resi = $destinationPath .'/'. $newfilename;
			$upload_success = $file->move($destinationPath, $newfilename);
			$wid = 500;
			$resizeimage=Helpers::resize_image($resi,$wid);
			$resizeimage=Helpers::compress_image($resi,100);
			
			return $newfilename;
		}

	public static function imageUpload($file,$destinationPath,$fileName){
		$array=array();
		foreach($file as $fileimage){
				$filename = $fileimage->getClientOriginalName();
					$extension = $fileimage->getClientOriginalExtension();
					$ext = array('jpg','JPG','jpeg', 'gif', 'png');
					if(!in_array($extension, $ext)){
						return false;
					} 
					$newfilename = $fileName.'.'.$extension;
					if(file_exists($destinationPath.'/'.$newfilename)){
						$info=pathinfo($newfilename);
						$imageNamee=$info['filename'].'-'.rand(100,999);
						$newfilename=$imageNamee.".".$info['extension'];
					}
					$array[]=$newfilename;
					$upload_success = $fileimage->move($destinationPath, $newfilename);
					$resi = $destinationPath .'/'. $newfilename;
					/*$resizeimage=Helpers::resize_image($resi);
					$resizeimage=Helpers::compress_image($resi,100);*/
			 }
			  $imageNames = implode('{$}',$array);
			  return $imageNames;
	}
	public static function compress_image($destination_url, $quality) {
		$sizeee = filesize($destination_url);
		if($sizeee>1000){
			$info = getimagesize($destination_url);
			if ($info['mime'] == 'image/jpeg') $image = imagecreatefromjpeg($destination_url);
			elseif ($info['mime'] == 'image/gif') $image = imagecreatefromgif($destination_url);
			elseif ($info['mime'] == 'image/png') $image = imagecreatefrompng($destination_url);
			imagejpeg($image, $destination_url, $quality);
		}
		return $destination_url;
	}
	public static function resize_image($destination_url,$wid){
		$info = getimagesize($destination_url);
		if($info['mime'] == 'image/jpeg' || $info['mime'] == 'image/jpg')
		{
			$src = imagecreatefromjpeg($destination_url);
		}
		else if($info['mime'] == 'image/png')
		{
			$src = imagecreatefrompng($destination_url);
		}
		else
		{
			$src = imagecreatefromgif($destination_url);
		}
		list($width,$height)=getimagesize($destination_url);
		if($width>$wid){
			$newwidth=$wid;
			$newheight=($height/$width)*$newwidth;
			$tmp=imagecreatetruecolor($newwidth,$newheight);
			imagecopyresampled($tmp,$src,0,0,0,0,$newwidth,$newheight,$width,$height);
			imagejpeg($tmp,$destination_url,100);
			imagedestroy($src);
			imagedestroy($tmp);
		}
		return $destination_url;
	}
	  public static function sortBySubArrayValue(&$array, $key, $dir='asc') {
 
			$sorter=array();
			$rebuilt=array();
		 
			//make sure we start at the beginning of $array
			reset($array);
		 
			//loop through the $array and store the $key's value
			foreach($array as $ii => $value) {
			  $sorter[$ii]=$value[$key];
			}
		 
			//sort the built array of key values
			if ($dir == 'asc') asort($sorter);
			if ($dir == 'desc') arsort($sorter);
		 
			//build the returning array and add the other values associated with the key
			foreach($sorter as $ii => $value) {
			  $rebuilt[$ii]=$array[$ii];
			}
		 
			//assign the rebuilt array to $array
			$array=$rebuilt;
		}
	public static function allmatchformats(){
		$formats=array();
			$format['t10'] = 't10';
		$format['t20'] = 't20';
		$format['test'] = 'test';
		$format['one-day'] = 'one-day';
		return $format;
	}
	public static function mailheader(){
		$geturl = Helpers::geturl();
		$mail='
		';
		return $mail;
	}
	public static function mailfooter(){
		$mail='<div>
		<img src="https://ci3.googleusercontent.com/proxy/umBdvXUjq17C7Ah8QC92nPc_Zc2l9j1ldK8SxuqWO8iBHdjinWUyvS5lIZIVtJtu9797cgcP-xevQ-Z7o-qKz6ROjzjVJ7onPFpnVA4LLeMX3w-CayzC1YMDt4zCnEOlN85o8EWnpz3RSbje-NzlAloR1FAcypvp7ED9qdUJJ-IyRg_nUtGGKpYIFA32oAl8c6kOEnui6vYf6Uui1qo1Pr23uUWVCfTtaC43rwMkmL2ew-yXdbeXHdPLPjQH9l8b8NiKfAYS7MFnszxzzbXXill8l7RKu-s-X7DS_9Dk9ZdbbDiGK03CwxHRgq559Iu55Ttbo47765RqQIJ-kQMn16MYeEm-A5XIGTGmE0MXmGTyobPQL8nYoWZjDfbc5413aq0xcSY1U_wG8PXcbtQDX4ntGLglC9_nQrZn4g63PRmnZtkCi9IEjwPpsYLva9Od9SK7SkKsbpppeij4AjRbM69TyigCLSFaMI_moQn_GMQC7wdk2fS-jZ9uq65Eo1RJ4fKjuDI5MQ2c-XEbpszgG8MgFX_fCPJY1lCdMER2QxUy4P1dmUdAepys4iJ_b-8102YxVPJpz1Uz-_8X9cyBb7hxjfDfiWvPky_i401XczFTxoiwQ5I3uYSkwQ1LYNLir0eVuPf_2B8EwW78xE9oRDtl=s0-d-e1-ft#http://email.Dream11.com/wf/open?upn=5F-2BQJKy1fxWNQUL1awoE5RAuXCnn-2B3E7raKXhSDsJrAO4eo23-2FGuE9jjMJckMGOY9c01PKIUoian0V5pXFwlLfv3MfGt2-2BQ0zIU2gs8BmtBPDqZ1N1im7iQyn69dQtBemMifSCJamjygvwezy3xdXaPeSRXLRLImQ1xrRaA8wg1ZMoijvAgdfBNXJ4e9-2BuKFUHLT7b6dEfDB6ziVSAFppnm-2B155TW-2FpiiPG-2B3LwJEvlcKnkFt8ZeRZTNNwLh5q0O9RkQFBPfOsSVfdouFLVfmkCLfAvr1l6pxmZtqGrC7R7Ye-2BHaBxGdYLPRV4ded770glAyOtvpL1kzo7YR9-2Fl7MRPsqWDPUz1EKdK-2BVMdROdlh1WcicUwXj-2BoSfbFgajg-2FvhcAPCJRzn43lNOItynlQA-3D-3D" alt="" width="1" height="1" border="0" style="height:1px!important;width:1px!important;border-width:0!important;margin-top:0!important;margin-bottom:0!important;margin-right:0!important;margin-left:0!important;padding-top:0!important;padding-bottom:0!important;padding-right:0!important;padding-left:0!important" class="CToWUd"><div class="yj6qo"></div><div class="adL">
		</div></div><div class="adL">
		</div></div>';
	return $mail;
	}

	public static function mailsentFormat($email,$subject,$mailmessage){

		// Mail::to($email)->send(new SendMailable($mailmessage, $subject));
		Mail::send('emails.test', ['title'=>'Xtreme11','content'=>$mailmessage], function($message) use ($email,$subject) {
	        $message->from('help.xtreme11@gmail.com','Xtreme11');
	        $message->to($email);
	        $message->subject($subject);
	    });
		// $headers = "MIME-Version: 1.0" . "\r\n";
		// $headers.= "Content-type:text/html;charset=UTF-8" . "\r\n";
		// $headers .= 'From:  Xtreme<noreply@Xtreme.com>'. " \r\n";
		// mail($email,$subject,$mailmessage,$headers,"-f noreply@Xtreme.com");
		
	}
	
	public static function sendmultiplenotification($title,$message,$include_image,$users){
       require_once('./sendnotification/firebase.php');
        require_once('./sendnotification/push.php');
        if(!empty($users)){
           $regarray = array();
           $findappids = DB::table('androidappid')->whereIn('user_id',$users)->get();
           if(!empty($findappids)){
               foreach($findappids as $app){
                   $regarray[] = $app->appkey;
               }
           }
           $firebase = new Firebase();
        $push = new Push();
        $payload = array();
        $payload['team'] = 'India';
        $payload['score'] = '5.6';
        $push->setTitle($title);
        $push->setMessage($message);
        $push->setIsBackground(FALSE);
        $push->setNotificationType('');
        $json = $push->getPush();
        $response = $firebase->sendMultiple($regarray, $json);
        }
    }
	public static function sendnotification($title,$message,$include_image,$regId){
			require_once('./sendnotification/firebase.php');
		    require_once('./sendnotification/push.php');
			if($regId!=""){
				$findappids = DB::table('androidappid')->where('user_id',$regId)->get();
				if(!empty($findappids)){
					foreach($findappids as $app){
						$firebase = new Firebase();
						$push = new Push();
						$payload = array();
						$payload['team'] = 'India';
						$payload['score'] = '5.6';
						$push->setTitle($title);
						$push->setMessage($message);
						$push_type='individual';
						if ($include_image!="") {
							$push->setImage('http://api.androidhive.info/images/minion.jpg');
						} else {
							$push->setImage('');
						}
						$push->setIsBackground(FALSE);
						$push->setPayload($payload);
						$push->setNotificationType('');
						$json = '';
						$response = '';

						if ($push_type == 'topic') {
							$json = $push->getPush();
							$response = $firebase->sendToTopic('global', $json);
						} else if ($push_type == 'individual') {
							$json = $push->getPush();
							$response = $firebase->send($app->appkey, $json);
						}

					}
				}
			}
			
		}
		public static function sendnotification1($title,$message,$include_image,$regId){
			require_once('./sendnotification/firebase.php');
		    require_once('./sendnotification/push.php');
			if($regId!=""){
				$findappids = DB::table('androidappid')->where('userid',$regId)->get();
				if(!empty($findappids)){
					foreach($findappids as $app){
						$firebase = new Firebase();
						$push = new Push();
						$payload = array();
						$payload['team'] = 'India';
						$payload['score'] = '5.6';
						$push->setTitle($title);
						$push->setMessage($message);
						$push->setNotificationType('');
						$push_type='topic';
						if ($include_image!="") {
							$push->setImage('http://api.androidhive.info/images/minion.jpg');
						} else {
							$push->setImage('');
						}
						$push->setIsBackground(FALSE);
						$push->setPayload($payload);
						$json = '';
						$response = '';

						if ($push_type == 'topic') {
							$json = $push->getPush();
							$response = $firebase->sendToTopic('global', $json);
						} else if ($push_type == 'individual') {
							$json = $push->getPush();
							$response = $firebase->send($app->appkey, $json);
						}

					}
				}
			}
			
		}
		public static function getnewmail($email){
			if (strpos($email, '@gmail.com') !== false){
				$wordbreak = explode('@gmail.com',$email);
				$word1 = str_replace('.', '', $wordbreak[0]);
				$email = $word1.'@gmail.com';
			}
			return $email;
		}
		public static function checkEmail($email) {
		   if ( strpos($email, '@') !== false ) {
			  $split = explode('@', $email);
			  return (strpos($split['1'], '.') !== false ? true : false);
		   }
		   else {
			  return false;
		   }
		}
  
	public static function tempUserMail1($code){
	    $content ='
                <div class="m_-7802618208648170374mailer-content" style="margin:0;padding:0 15px">
                    <table width="100%" cellspacing="0" cellpadding="0" style="margin:0;padding:0">
                        <tbody><tr style="margin:0;padding:0">
                            <td style="margin:0;padding:0">
                                <br style="margin:0;padding:0">
                                <p style="margin:0;padding:0;margin-bottom:5px;color:#585858;font-weight:400;font-size:13px;line-height:1.6">Hello User,</p>
                                <br style="margin:0;padding:0">
                                        <p style="margin:0;padding:0;margin-bottom:5px;color:#585858;font-weight:400;font-size:13px;line-height:1.6">Welcome to Xtreme. To verify your email account please use this OTP. </p>
                            </td>
                        </tr>
                    </tbody></table>
                </div>
                <br style="margin:0;padding:0">
                <div class="m_-7802618208648170374order-id" style="margin:0;padding:0 15px">
                    <table width="100%" cellspacing="0" cellpadding="0" style="margin:0;padding:0">
                        <tbody>
                        <tr style="margin:0;padding:0">
                            <td width="20%" align="right" style="margin:0;padding:0;margin-bottom:15px;font-weight:bold;font-size:16px;min-width:100px;max-width:100%;display:inline-block;vertical-align:top">

                                <a style="margin:0;padding:5px 10px;color:#fff;background:#3598DC;display:inherit;text-decoration:none;text-align:center;line-height:30px;width:190px;height:30px;border-radius:3px;font-weight:600;font-size:18px;outline:0;border:0" target="_blank" >
                                   '.$code.'</a>
                            </td>
                        </tr>
                    </tbody></table>
                </div>';
        return $content;
	}
	public static function tempUserMail($code){
		$content ='<tr>
							<td align="center" valign="top" style="padding-bottom:5px;padding-left:20px;padding-right:20px;" class="mainTitle">
								<!-- Main Title Text // -->
								<h2 class="text" style="color:#000000; font-family:Poppins, Helvetica, Arial, sans-serif; font-size:28px; font-weight:500; font-style:normal; letter-spacing:normal; line-height:36px; text-transform:none; text-align:center; padding:0; margin:0">
									Hello
								</h2>
							</td>
						</tr>

						<tr>
							<td align="center" valign="top" style="padding-bottom:30px;padding-left:20px;padding-right:20px;" class="subTitle">
								<!-- Sub Title Text // -->
								<h4 class="text" style="color:#999999; font-family:Poppins, Helvetica, Arial, sans-serif; font-size:16px; font-weight:500; font-style:normal; letter-spacing:normal; line-height:24px; text-transform:none; text-align:center; padding:0; margin:0">
									Verify Your  Email Account
								</h4>
							</td>
						</tr>

						<tr>
							<td align="center" valign="top" style="padding-left:20px;padding-right:20px;" class="containtTable ui-sortable">

								<table border="0" cellpadding="0" cellspacing="0" width="100%" class="tableDescription">
									<tbody><tr>
										<td align="center" valign="top" style="padding-bottom:20px;" class="description">
											<!-- Description Text// -->
											<p class="text" style="color:#666666; font-family:Open Sans, Helvetica, Arial, sans-serif; font-size:14px; font-weight:400; font-style:normal; letter-spacing:normal; line-height:22px; text-transform:none; text-align:center; padding:0; margin:0">
												Join the best community of Fans. Come play our Fantasy Movie. Top verify your email account please use OTP given below.
											</p>
										</td>
									</tr>
								</tbody></table>

								<table border="0" cellpadding="0" cellspacing="0" width="100%" class="tableButton" style="">
									<tbody><tr>
										<td align="center" valign="top" style="padding-top:20px;padding-bottom:20px;">

											<!-- Button Table // -->
											<table align="center" border="0" cellpadding="0" cellspacing="0">
												<tbody><tr>
													<td align="center" class="ctaButton" style="background-color:#c51d23;padding-top:12px;padding-bottom:12px;padding-left:35px;padding-right:35px;border-radius:50px">
														<!-- Button Link // -->
														<a class="text" href="#" target="_blank" style="color:#FFFFFF; font-family:Poppins, Helvetica, Arial, sans-serif; font-size:13px; font-weight:600; font-style:normal;letter-spacing:1px; line-height:20px; text-transform:uppercase; text-decoration:none; display:block">
															'.$code.'
														</a>
													</td>
												</tr>
											</tbody></table>

										</td>
									</tr>
								</tbody></table>

							</td>
						</tr>';
						return $content;
	}
	public static function sendResetPasswordMail($code){
	    $imgs=asset('assets/images/xtreme.png');
		$content ='<div style="background-color:#f5f5f5;padding-top:80px">
                            <div style="margin:0 auto;max-width:600px;background:#ffffff">
                              <table role="presentation" cellpadding="0" cellspacing="0" style="font-size:0px;width:100%;background:#ffffff;border-top:3px solid #fead0d" align="center" border="0">
                                <tbody>
                                  <tr>
                                    <td style="text-align:center;vertical-align:top;font-size:0px;padding:40px 30px 30px 30px">
                                      <div aria-labelledby="mj-column-per-100" class="m_-6309021538948295627mj-column-per-100" style="vertical-align:top;display:inline-block;font-size:13px;text-align:left;width:100%">
                                        <table role="presentation" cellpadding="0" cellspacing="0" width="100%" border="0">
                                          <tbody>
                                            <tr>
                                              <td style="word-break:break-word;font-size:0px;padding:0px;padding-bottom:30px" align="center">
                                                <table role="presentation" cellpadding="0" cellspacing="0" style="border-collapse:collapse;border-spacing:0px" align="center" border="0">
                                                  <tbody>
                                                    <tr>
                                                      <td style="width:180px">
                                                        <a href="http://imgglobal.in/Xtreme" target="_blank">
                                                          Xtreme</a></td>
                                                    </tr>
													
                                                  </tbody>
                                                </table>
                                              </td>
                                            </tr>
											<tr>
                        					  <td style="word-break:break-word;font-size:0px;padding:0px" align="center">
                        						<div style="color: #55575d;font-family: Roboto,Helvetica,Arial,sans-serif;font-size: 30px;line-height: 22px;margin-bottom: 10px;font-weight: 600;">
                        						  Welcome!
                        						</div>
                        					  </td>
                        					</tr>
                        					<tr>
                        					  <td style="word-break:break-word;font-size:0px;padding:0px" align="center">
                        						<div style="color:#1a717b;font-family:Roboto,Helvetica,Arial,sans-serif;font-size:19px;line-height:22px;margin-bottom: 10px;">
                        						  <img src="'.$imgs.'">
                        						</div>
												
                        					  </td>
                        					</tr>
                        					<tr>
                        					  <td style="word-break:break-word;font-size:0px;padding:0px" align="center">
                        						<div style="color:#6c7171;font-family:Roboto,Helvetica,Arial,sans-serif;font-size:14px;line-height:22px;margin-bottom: 10px;">
                        						 You recently requested to reset your password for your account. Please use the OTP  given below to reset your password.
                        						</div>
                        					  </td>
                        					</tr>
                                            <tr>
										<td style="word-break:break-word;font-size:0px;" align="center">
											<!-- Button Link // -->
											<button  style="padding: 6px 7px;font-size: 14px;text-align: center; cursor: pointer;outline: none;color: #fff;background-color: #00BCD4;border: none;box-shadow: 0 9px #999;margin-left: 12px;">
												'.$code.'
											</button>
										</td>
										</tr>
                                            <tr>
                                              <td style="word-break:break-word;font-size:0px;padding:0px" align="center">
                                                <div style="color:#8c8c8c;font-family:Roboto,Helvetica,Arial,sans-serif;font-size:14px;line-height:22px">
                                                  &nbsp;
                                                </div>
                                              </td>
                                            </tr>
                                          </tbody>
                                        </table>
                                      </div>
                                    </td>
                                  </tr>
                                </tbody>
                              </table>
                            </div>
    
                            <div style="margin:0 auto;max-width:600px">
                              <table role="presentation" cellpadding="0" cellspacing="0" style="font-size:0px;width:100%" align="center" border="0">
                                <tbody>
                                  <tr>
                                    <td style="text-align:center;vertical-align:top;font-size:0px;padding:30px">
                                      <div aria-labelledby="mj-column-per-100" class="m_-6309021538948295627mj-column-per-100" style="vertical-align:top;display:inline-block;font-size:13px;text-align:left;width:100%">
                                        <table role="presentation" cellpadding="0" cellspacing="0" width="100%" border="0">
                                          <tbody>
                                            <tr>
                                              <td style="word-break:break-word;font-size:0px;padding:0px;padding-bottom:10px" align="center">
                                                <div style="color:#8c8c8c;font-family:Roboto,Helvetica,Arial,sans-serif;font-size:12px;line-height:22px">
                                                  <span>This email was sent to you by Xtreme.</span> <span>Please let us know if you feel that this email was sent to you by error.</span>
                                                </div>
                                              </td>
                                            </tr>
                                            <tr>
                                              <td style="word-break:break-word;font-size:0px;padding:0px;padding-bottom:15px" align="center">
                                                <div style="color:#8c8c8c;font-family:Roboto,Helvetica,Arial,sans-serif;font-size:12px;line-height:22px">
                                                  © 2019 Xtreme
                                                </div>
                                              </td>
                                            </tr>
                                            <tr>
                                              <td style="word-break:break-word;font-size:0px;padding:0px;padding-bottom:10px" align="center">
                                                <div style="color:#8c8c8c;font-family:Roboto,Helvetica,Arial,sans-serif;font-size:12px;line-height:22px">
                                                  <a href="http://Xtreme.com/" target="_blank" style="color:inherit;padding:0 7px">Privacy Policy</a>
                                                  <a href="http://Xtreme.com/" style="color:inherit;padding:0 7px" target="_blank">Sending Policy</a>
                                                  <a href="http://Xtreme.com/" style="color:inherit;padding:0 7px" target="_blank">Terms of Use</a>
                                                </div>
                                              </td>
                                            </tr>
                                            <tr>
                                              <td style="word-break:break-word;font-size:0px;padding:0px" align="center">
                                                <div style="color:#8c8c8c;font-family:Roboto,Helvetica,Arial,sans-serif;font-size:12px;line-height:22px">
                                                  <a href="https://www.facebook.com" style="text-decoration:none;color:#000;padding:0 4px" target="_blank">
                                                    <img alt="" src="https://ci6.googleusercontent.com/proxy/yv0eYtrW8iXHcr0DMdf864ByJvgCuAVd0AdPYXp7ZR-eoGfdPD9Nd0X7uMYoqREhcztv5n41TmqU5bTQwj3DSV9kqmHq7g=s0-d-e1-ft#https://app.mailjet.com/images/email/transac/fb.png" style="border:none;outline:none;text-decoration:none;height:auto" width="-22" height="auto" class="CToWUd">
                                                  </a>
                                                  <a href="https://www.twitter.com" style="text-decoration:none;color:inherit;padding:0 4px" target="_blank">
                                                    <img alt="" src="https://ci4.googleusercontent.com/proxy/oJgiLJpUuJAv6wLEBuFyotI3cUFMs1kt2_Wbld82mXq56n22lrVSfcugpBntCs5-M2NTWiUaB7W32ej9Qh20bWIDVckWag=s0-d-e1-ft#https://app.mailjet.com/images/email/transac/tw.png" style="border:none;outline:none;text-decoration:none;height:auto" width="-22" height="auto" class="CToWUd">
                                                  </a>
                                                  <a href="http://www.google.com" style="text-decoration:none;color:inherit;padding:0 4px" target="_blank">
                                                    <img alt="" src="https://ci3.googleusercontent.com/proxy/I9Gyug32nrifzDLLDgfxcqDOTPPdG0PNtq8XnuDx8GYGraaegJueeZKIgcdWjX4nE1vD_lcL13Y358T_meb28AuVFcw-Sxo=s0-d-e1-ft#https://app.mailjet.com/images/email/transac/rss.png" style="border:none;outline:none;text-decoration:none;height:auto" width="-22" height="auto" class="CToWUd">
                                                  </a>
                                                </div>
                                              </td>
                                            </tr>
                                          </tbody>
                                        </table>
                                      </div>
                                    </td>
                                  </tr>
                                </tbody>
                              </table>
                        	  <div class="yj6qo"></div><div class="adL">
                            </div>
                        	</div>
                        	<div class="adL">
                          </div>
                          </div>';
			return $content;
	}
	public static function Mailbody1($content,$email){
	    $html='';
	    $html.='
<div id=":17c" class="ii gt"><div id=":172" class="a3s aXjCH "><u></u>
          <div style="Margin:0;padding:0;min-width:100%;background-color:#f4f3ef;font-family:Arial,Helvetica;font-size:15px">
            
            <div style="display:none;font-size:1px;color:#333333;line-height:1px;max-height:0px;max-width:0px;opacity:0;overflow:hidden">
              You joined a league.
            </div>
            <center style="width:100%;table-layout:fixed">
              <div style="padding:0px" align="center">
                <div style="width:100%;background-color: #242356;padding:0px;border-bottom:1px solid #ddd;">
                  <div style="max-width:580px;padding:0px">
                    <table width="100%" style="border-spacing:0">
                      <tbody>
                        <tr>
                          <td style="padding:15px 15px 15px 20px;display: flex;align-items: center;" align="left">
                            <div style="float: left; width: 100%;">
                              <a href="http://Xtreme.com" target="_blank" style="display: flex;align-items: center;text-decoration: none;font-weight: bold;color: #fff;font-size: 16px;"><img src="'.asset('public/xtreme.png').'" alt="" style="width: 29px; margin-right: 5px;" class="CToWUd">Xtreme</a>
                            </div>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>    <div style="width:96%;background-color:#f4f3ef;padding:20px 0 0 0">
                <div style="max-width:578px;padding:0;border-radius:5px;border:1px solid #d6d6d6">
                  
                  <table align="center" style="border-spacing:0;font-family:Arial,Helvetica;color:#333333;Margin:0 auto;width:100%;max-width:578px">
                    <tbody>
                      <tr>
                        <td style="padding:0px;border-radius:5px 5px 5px 5px;background-color:#ffffff" align="center">  
                          <table width="100%" style="border-spacing:0">
                            <tbody>
                              <tr>
                                <td align="left" style="padding:20px 20px 20px 20px">
                                  <div style="font-family:Arial Black,Arial,Helvetica;font-size:18px;line-height:30px;font-weight:700">Hi '.$email.',</div>
                                </td>
                              </tr>
                              <tr>
                                <td align="left" style="padding:0px 20px 20px 20px">
                                  <div style="font-family:Arial,Helvetica;font-size:15px;line-height:22px"><p style="padding-left: 23px;"><strong> Hello </strong></p><p style="padding-left: 23px;">'.$content.'</p></div>
                                </td>
                              </tr>
                              <tr>
                                <td align="left" style="padding:0px 20px 20px 20px">
                                  <div style="font-family:Arial,Helvetica;font-size:15px;line-height:22px">Sit back, relax &amp; watch your favourite players in action! </div>
                                </td>
                              </tr>
                              <tr>
                                <td style="padding:0px 20px 20px 20px">
                                  <div style="font-family:Arial,Helvetica;font-size:15px;color:#555">Team <span class="il">Xtreme</span></div>
                                </td>
                              </tr>
                              <tr>
                                <td style="padding:0px 20px 10px 20px;font-family:Arial,Helvetica;font-size:15px;color:#262626;font-weight:normal;line-height:1.4;background:#edf6ff" align="left">
                                  
                                  <div style="display:inline-block;max-width:100px;width:100%">
                                    <table style="border-spacing:0" width="100%">
                                      <tbody>
                                        <tr>
                                          <td style="font-family:Arial,Helvetica;font-size:15px;color:#262626;font-weight:normal;line-height:1.4;padding:12px 0 0 0">
                                            <a href="http://Xtreme.com" style="text-decoration:none;color:#fff" target="_blank"><div style="font-family:Arial,Helvetica;padding:6px 10px;background:#f6af23;display:inline-block;font-size:12px;font-weight:700;color:#fff;border-radius:4px"><b>Invite Now</b></div></a>
                                          </td>
                                        </tr>
                                      </tbody>
                                    </table>
                                  </div>
                                </td>
                              </tr>
                              <tr>
                                <td style="padding:0px 30px 20px 30px;background:#e5e5e5" align="left">
                                  <table style="border-spacing:0" width="100%">
                                    <tbody>
                                      <tr>
                                        <td style="padding:30px 0px 10px 0px" align="center">
                                          <div style="display:inline-block;width:100%;max-width:180px;padding:0 0 20px 0">
                                            <a href="http://Xtreme.com/apk/Xtreme.apk" target="_blank"><img style="width:123px;outline:0" src="'.asset('public/mail2.png').'" alt="Download App" class="CToWUd"></a>
                                          </div>
                                        </td>
                                      </tr>
                                      <tr>
                                        <td style="padding:0px 20px 0px 20px;font-family:Arial,Helvetica;font-size:13px;color:#4a4a4a;font-weight:normal;line-height:1.6" align="center"><span class="il">Xtreme</span></td>
                                      </tr>
                                    </tbody>
                                  </table>
                                </td>
                              </tr>
                            </tbody>
                          </table>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                  
                </div>
              </div>
              </div>
</center></div><div class="adL">
</div></div><div class="adL">
</div></div>';
return $html;
	}
	public static function mailbody($content,$email){
	    $html='';
	    $html.='<div id=":17c" class="ii gt"><div id=":172" class="a3s aXjCH "><u></u>
          <div style="Margin:0;padding:0;min-width:100%;background-color:#f4f3ef;font-family:Arial,Helvetica;font-size:15px">
            
            <div style="display:none;font-size:1px;color:#333333;line-height:1px;max-height:0px;max-width:0px;opacity:0;overflow:hidden">
              You joined a league.
            </div>
            <center style="width:100%;table-layout:fixed">
              <div style="padding:0px" align="center">
                <div style="width:100%;background-color: #242356;padding:0px;border-bottom:1px solid #ddd;">
                  <div style="max-width:580px;padding:0px">
                    <table width="100%" style="border-spacing:0">
                      <tbody>
                        <tr>
                          <td style="padding:15px 15px 15px 20px;display: flex;align-items: center;" align="left">
                            <div style="float: left; width: 100%;">
                              <a href="http://Xtreme.com" target="_blank" style="display: flex;align-items: center;text-decoration: none;font-weight: bold;color: #fff;font-size: 16px;"><img src="'.asset('public/xtreme.png').'" alt="" style="width: 29px; margin-right: 5px;" class="CToWUd">Xtreme</a>
                            </div>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>    
                <div style="width:96%;background-color:#f4f3ef;padding:20px 0 0 0">
                <div style="max-width:578px;padding:0;border-radius:5px;border:1px solid #d6d6d6">
                  
                  <table align="center" style="border-spacing:0;font-family:Arial,Helvetica;color:#333333;Margin:0 auto;width:100%;max-width:578px">
                    <tbody>
                      <tr>
                        <td style="padding:0px;border-radius:5px 5px 5px 5px;background-color:#ffffff" align="center">  
                          <table width="100%" style="border-spacing:0">
                            <tbody>
                              <tr>
                                <td align="left" style="padding:20px 20px 20px 20px">
                                  <div style="font-family:Arial Black,Arial,Helvetica;font-size:18px;line-height:30px;font-weight:700">Hi '.$email.',</div>
                                </td>
                              </tr>
                              <tr>
                                <td align="left" style="padding:0px 20px 20px 20px">
                                  <div style="font-family:Arial,Helvetica;font-size:15px;line-height:22px"><p>'.$content.'</p></div>
                                </td>
                              </tr>
                              <tr>
                                <td align="left" style="padding:0px 20px 20px 20px">
                                  <div><a href="http://Xtreme.com" style="text-decoration:none;color:#ffffff" target="_blank" "=""><div style="font-family:Arial Black,Arial,Helvetica;padding:10px 20px;background: #232355;display:inline-block;text-transform:uppercase;font-size:13px;font-weight:700;border: 2px solid #181845;color:#ffffff;"><b>CHECK MY RANK</b></div></a></div> 
                                </td>
                              </tr>
                              <tr>
                                <td align="left" style="padding:0px 20px 20px 20px">
                                  <div style="font-family:Arial,Helvetica;font-size:15px;line-height:22px">Sit back, relax &amp; watch your favourite players in action! </div>
                                </td>
                              </tr>
                              <tr>
                                <td style="padding:0px 20px 20px 20px">
                                  <div style="font-family:Arial,Helvetica;font-size:15px;color:#555">Team <span class="il">Xtreme</span></div>
                                </td>
                              </tr>
                              <tr>
                                <td style="padding:0px 20px 10px 20px;font-family:Arial,Helvetica;font-size:15px;color:#262626;font-weight:normal;line-height:1.4;background:#edf6ff;display: flex;align-items: center;" align="left">
                                  <div style="padding:10px 0 0 0;display:inline-block;max-width:300px;width:100%;vertical-align:top;text-align:left">
                                    <table style="border-spacing:0" width="100%">
                                      <tbody>
                                        <tr>
                                          <td style="font-family:Arial,Helvetica;font-size:15px;color:#262626;font-weight:normal;line-height:1.4">
                                            <img style="width:40px" src="'.asset('public/mail3.png').'" alt="" class="CToWUd">
                                          </td>
                                          <td style="font-family:Arial,Helvetica;font-size:15px;color:#262626;font-weight:normal;line-height:1.4;padding:0px 0 0 20px">Invite your friends &amp; earn as they play!</td>
                                        </tr>
                                      </tbody>
                                    </table>
                                  </div>
                                  <div style="display:inline-block;max-width:100px;width:100%">
                                    <table style="border-spacing:0" width="100%">
                                      <tbody>
                                        <tr>
                                          <td style="font-family:Arial,Helvetica;font-size:15px;color:#262626;font-weight:normal;line-height:1.4;padding:12px 0 0 0">
                                            <a href="http://Xtreme.com" style="text-decoration:none;color:#fff" target="_blank"><div style="font-family:Arial,Helvetica;padding:6px 10px;background:#f6af23;display:inline-block;font-size:12px;font-weight:700;color:#fff;border-radius:4px"><b>Invite Now</b></div></a>
                                          </td>
                                        </tr>
                                      </tbody>
                                    </table>
                                  </div>
                                </td>
                              </tr>
                              <tr>
                                <td style="padding:0px 30px 20px 30px;background:#e5e5e5" align="left">
                                  <table style="border-spacing:0" width="100%">
                                    <tbody>
                                      <tr>
                                        <td style="padding:30px 0px 10px 0px" align="center">
                                          <div style="display:inline-block;width:100%;max-width:180px;padding:0 0 20px 0">
                                            <a href="http://Xtreme.com/apk/Xtreme.apk" target="_blank"><img style="width:123px;outline:0" src="'.asset('public/mail2.png').'" alt="Download App" class="CToWUd"></a>
                                          </div>
                                        </td>
                                      </tr>
                                      <tr>
                                        <td style="padding:0px 20px 0px 20px;font-family:Arial,Helvetica;font-size:13px;color:#4a4a4a;font-weight:normal;line-height:1.6" align="center"><span class="il">Xtreme</span></td>
                                      </tr>
                                    </tbody>
                                  </table>
                                </td>
                              </tr>
                            </tbody>
                          </table>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                  
                </div>
              </div>
              </div>
</center></div><div class="adL">
</div></div><div class="adL">
</div></div>';
return $html;
	}
	// common function to sort the teams//
	public static function multid_sort($arr, $index) {
		$b = array();
		$c = array();
		
		foreach ($arr as $key => $value) {
			$b[$key] = $value[$index];
		}
		arsort($b);
		foreach ($b as $key => $value) {
			$c[] = $arr[$key];
		}
		return $c;
	}
}


?>