<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');
class APIController extends CI_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->library('paypal');
        $this->load->library('twoCheckout_Lib');
        $this->load->library('vouguepay');
    }
    //================[[This function to check the auth]]==========================
    public function isAuthorize(){
        
        if($this->input->get_request_header('Auth')){
            $auth_key = $this->input->get_request_header('Auth');
            if(isset($auth_key) && $auth_key != "") {
                $dataa = explode(" ",$auth_key);
                if(isset($dataa[1])){
                    $main_key = $dataa[1];
                }else{
                    $main_key = $auth_key;
                }
                $model = $this->db->get_where('user',['auth_key'=>$main_key])->row();
                // echo '<pre>';print_r($model);die;
                if(!empty($model)){
                    return $model;
                }
                else{
                    $json['status'] = 0;
                    $json['msg'] = 'Login Again';
                    echo json_encode($json);die;
                }
            }
            else{
                $json['success'] = false;
                $json['msg'] = 'You cannot access this page';
                echo json_encode($json);die;
            }
        }else{
            $json['status'] = 0;
            $json['msg'] = 'You cannot access this page';
            echo json_encode(array($json));die;
        }
    }
    //================[[This function to check password and send otp to user ]]====
    function insertAppId($id,$appid){
        $appdata['user_id'] = $id;
        $appdata['appkey'] = $appid;
        $findexist = $this->db->get_where('androidappid',['user_id'=>$id,'appkey'=>$appid])->row();
        // echo '<pre>';var_dump(empty($findexist));die;
        if(empty($findexist)){
            $this->db->insert('androidappid',$appdata);
            // print_r($this->db->error());die;
        }
    }
    public function userlogin(){
        header('Content-Type: application/json');
        if($this->input->method()=='post'){
            $email = $this->input->post('email');
            $password = $this->input->post('password');
            if(!empty($email) && !empty($password)){
                $userdata = $this->db->where('email',$email)->or_where('phone',$email)->where('password',sha1($password))->get('user')->row();
                // echo "<pre>";print_r($userdata);die;
                if(!empty($userdata)){
                    $json['status'] = 1;
                    $json['auth_key'] = $userdata->auth_key;
                    $this->insertAppId($userdata->user_id,$this->input->post('appid'));
                    $json['msg'] = 'Login successfully';
                    echo json_encode($json);die;
                }else{
                    $json['status'] = 0;
                    $json['msg'] = 'Invalid credentials';
                    echo json_encode($json);die;
                }
            }else{
                $json['status'] = 0;
                $json['msg'] = 'Invalid credentials';
                echo json_encode($json);die;
            }
        }else{
            $json['status'] = 0;
            $json['msg'] = 'You are not authorized to access';
            return json_encode($json);die;
        }
    }
    function logoutuser(){
        header('Content-Type: application/json');
        $user = $this->isAuthorize();
        $user_id=$user->user_id;
        if(isset($_POST['appid'])){
            $appid = $appdata['appid'] = $this->input->post('appid');
            $findexist = $this->db->get_where('androidappid',['user_id'=>$user_id,'appkey'=>$appid])->row();
            if(!empty($findexist)){
                $this->db->where('id',$findexist->id)->delete('androidappid');
            }
        }
        $msgg['status'] = true;
        echo json_encode($msgg);die;
    }
    //================[[This function to verify to otp]]===========================
    public function checkloginotp(){
        header('Content-Type: application/json');
        if($this->input->method()=='post'){
            $mobile = $this->input->post('mobile');
            $otp = $this->input->post('otp');
            if(!empty($mobile) && !empty($otp)){
                $userdata = $this->db->get_where('user',['phone'=>$mobile,'otp'=>$otp])->row();
                // echo "<pre>";print_r($userdata);die;
                if(!empty($userdata)){
                    $d = $this->db->where('user_id',$userdata->user_id)->update('user',['otp'=>'']);
                    // echo $this->db->last_query();die;
                    $json['status'] = 1;
                    $json['auth_key'] = $userdata->auth_key;
                    $json['msg'] = 'Login Successfull';
                    echo json_encode($json);die;
                }else{
                    $json['status'] = 0;
                    $json['msg'] = 'Invalid Otp';
                    echo json_encode($json);die;
                }
            }else{
                $json['status'] = 0;
                $json['msg'] = 'Please Enter Otp';
                echo json_encode($json);die;
            }
        }else{
            $json['status'] = 0;
            $json['msg'] = 'You are not authorized to access';
            return json_encode($json);die;
        }
    }

    //============user Registration================================================
    function registration_refer_amount($id,$count,$insertid){
        // echo 'entereddasf';die;
        // return $_SESSION['first_refer'];
        $d = $this->db->get_where('user',array('user_id'=>$id))->row();
        
        $referdata = array();
        $transaction['buyer_id'] = $insertid;
        $transaction['bonus_for'] = 'signup';
        $transaction['user_id'] = $id;
        if(!empty($d->referid)){
            $count++;
            if($count==1){
                $transaction['refer_amt'] = 50;
                $transaction['level'] = 'Level 1';
                $amt = base64_decode($d->wallet)+50;
                $referdata['wallet'] = base64_encode($amt);    
            }else if($count==2){
                $transaction['refer_amt'] = 30;
                $transaction['level'] = 'Level 2';
                $amt = base64_decode($d->wallet)+30;
                $referdata['wallet'] = base64_encode($amt);    
            }else if($count==3){
                $transaction['refer_amt'] = 20;
                $transaction['level'] = 'Level 3';
                $amt = base64_decode($d->wallet)+20;
                $referdata['wallet'] = base64_encode($amt);     
            }else{
                return $count;
            }    
            $this->db->insert('bonus_transaction',$transaction);   
            $insert_id = $this->db->insert_id();     
            $this->db->where('user_id', $id);
            $this->db->update('user', $referdata);
            // return $d->referid;
            $this->registration_refer_amount($d->referid,$count,$insertid);
        }else{
            return $count;
        }
    }
    public function verifyOtp(){
        header('Content-Type: application/json');
        if($this->input->method()=='post'){
            if(!empty($this->input->post())){
                
                $exists = $this->db->where('phone', $this->input->post('mobile'))->where('otp', $this->input->post('otp'))->get('tempuser')->row();
                // echo '<pre>';var_dump($exists);die;
                if(!empty($exists)){
                    $data['username']      = $exists->name;
                    $data['email']         = $exists->email;
                    $data['phone']         = $exists->phone;
                    $data['password']      = sha1($exists->password);
                    $data['auth_key']      = md5($exists->email);
                    $data['package_info']  = '[]';
                    $data['product_upload']= (int)$this->db->get_where('package', array('package_id' => 1))->row()->upload_amount;
                    $data['creation_date'] = time();
                    $data['refercode'] = 'SMT'.rand(1000,999999);
                    $data['wallet'] = base64_encode(100);
                    if($exists->refercode!=''){
                        $d = $this->db->get_where('user',array('refercode'=>$exists->refercode))->row();
                        if(!empty($d)){
                            $data['referid'] = $d->user_id;
                            $referedwallet = base64_decode($d->wallet);
                        }else{
                            $json['status'] = 0;
                            $json['msg'] = 'Invalid Refercode';
                            echo json_encode($json);die;
                        }
                    }
                    $this->db->insert('user',$data);
                    $insert_id = $this->db->insert_id();
                    // echo $insert_id;die;
                    $transaction = array(
                                    'buyer_id' => $insert_id,
                                    'bonus_for' => 'signup',
                                    'user_id' => $insert_id,
                                    'level' => 'self',
                                    'refer_amt'=>100
                                );
                    // echo "<pre>";print_r($transaction);die;
                    $this->db->insert('bonus_transaction',$transaction);
                    if(array_key_exists('referid', $data)){

                        $sms = $this->registration_refer_amount($data['referid'],$count=0,$insert_id); 
                        // echo "<pre>";print_r($sms);die;    
                        $msg = 'done';
                    }
                    //  $rds = $this->account_opening('user', $data['email'], $data['password']);
                    // echo '<pre>';print_r($rds);die;
                    if($this->account_opening('user', $data['email'], $exists->password) == false){
                        $msg['email'] = 'done_but_not_sent';
                    }else{
                        $msg['email'] = 'done_and_sent';
                    }
                    $this->db->where('id',$exists->id)->delete('tempuser');
                    $json['status'] = 1;
                    $json['auth_key'] = $data['auth_key'];
                    $json['msg'] = 'OTP Verified, Login to your account';
                    echo json_encode($json);die; 
                }else{
                    $json['status'] = 0;
                    $json['msg'] = 'invalid OTP';
                    echo json_encode($json);die;
                }
                
            }else{
                $json['status'] = 0;
                $json['msg'] = 'all fields are required';
                echo json_encode($json);die;
            }
        }else{
            $json['status'] = 0;
            $json['msg'] = 'You are not authorized to access';
            return json_encode($json);die;
        }
        
    }
    public  function tempuserregistration(){
        header('Content-Type: application/json');
        if($this->input->method()=='post'){
            if(!empty($this->input->post())){
                foreach ($this->input->post() as $key => $value) {
                    if($value=='' && $key!='refercode'){
                        $json['status'] = 0;
                        $json['msg'] = ucwords($key).' field is Required';
                        echo json_encode($json);die;
                    }
                    if($key=='phone' && (strlen($value)>10 || strlen($value)<10)){
                        $json['status'] = 0;
                        $json['msg'] = 'Mobile number should be 10 digits only';
                        echo json_encode($json);die;
                    }
                }
                $exists = $this->db->where('email', $this->input->post('email'))->where('phone', $this->input->post('phone'))->get('user')->row();
                if(!empty($exists)){
                    if($this->input->post('email')==$exists->email){
                        $json['msg'] = 'Email already exist, Try Again with different email.';
                    }elseif($this->input->post('phone')==$exists->phone){
                        $json['msg'] = 'Mobile number already exist, Try Again with different Number.';
                    }
                    $json['status'] = 0;
                    
                    echo json_encode($json);die;
                }else{
                    $temp = $this->db->where('email', $this->input->post('email'))->where('phone', $this->input->post('phone'))->get('tempuser')->row();
                    if(!empty($temp)){
                        // $input['otp'] = rand(0000,9999);
                        $input['name'] = $this->input->post('name');
                        if(array_key_exists('refercode', $this->input->post())){
                            $input['refercode'] = !empty($this->input->post('refercode'))?$this->input->post('refercode'):'';
                        }
                        $input['password'] = $this->input->post('password');
                        $input['otp'] = '1234';
                        $this->db->where('id',$temp->id)->update('tempuser',$input);
                        $json['status'] = 1;
                        $json['msg'] = 'Otp Sent on your mobile number';
                        echo json_encode($json);die;
                    }else{
                        $input['email'] = $this->input->post('email');
                        $input['phone'] = $this->input->post('phone');
                        $input['name'] = $this->input->post('name');
                        $input['password'] = $this->input->post('password');
                        if(array_key_exists('refercode', $this->input->post())){
                            $input['refercode'] = !empty($this->input->post('refercode'))?$this->input->post('refercode'):'';
                        }
                        $input['otp'] = '1234';
                        // echo '<pre>';var_dump($this->input->post());die;
                        $this->db->insert('tempuser',$input);
                        $json['status'] = 1;
                        $json['msg'] = 'Otp Sent on your mobile number';
                        echo json_encode($json);die;
                    }
                }
                
            }else{
                $json['status'] = 0;
                $json['msg'] = 'all fields are required';
                echo json_encode($json);die;
            }
        }else{
            $json['status'] = 0;
            $json['msg'] = 'You are not authorized to access';
            return json_encode($json);die;
        }
    }
    function account_opening($account_type = '', $email = '', $pass = '')
    {
        //$this->load->database();
        $from_name  = $this->db->get_where('general_settings',array('type' => 'system_name'))->row()->value;
        $protocol = $this->db->get_where('general_settings', array('type' => 'mail_status'))->row()->value;
        if($protocol == 'smtp'){
            $from = $this->db->get_where('general_settings',array('type' => 'smtp_user'))->row()->value;
        }
        else if($protocol == 'mail'){
            $from = $this->db->get_where('general_settings', array('type' => 'system_email'))->row()->value;
        }
        
        $to   = $email;
        $query = $this->db->get_where($account_type, array('email' => $email));
        
        if ($query->num_rows() > 0) {
            
            if($account_type == 'user'){
                $to_name    = $query->row()->username;
                // $url         = "<a href='".base_url()."home/login_set/login'>".base_url()."home/login_set/login</a>";
                $sub        = $this->db->get_where('email_template', array('email_template_id' => 5))->row()->subject;
                $email_body      = $this->db->get_where('email_template', array('email_template_id' => 5))->row()->body;
            }
            
            $email_body      = str_replace('[[to]]',$to_name,$email_body);
            $email_body      = str_replace('[[sitename]]',$from_name,$email_body);
            $email_body      = str_replace('[[account_type]]',$account_type,$email_body);
            $email_body      = str_replace('[[email]]',$to,$email_body);
            $email_body      = str_replace('[[password]]',$pass,$email_body);
            // $email_body      = str_replace('[[url]]',$url,$email_body);
            $email_body      = str_replace('[[from]]',$from_name,$email_body);
            +
            $background = $this->db->get_where('ui_settings',array('type' => 'email_theme_style'))->row()->value;
            if($background !== 'style_1'){
                $final_email = $this->db->get_where('ui_settings',array('type' => 'email_theme_'.$background))->row()->value;
                if($background == 'style_4'){
                    $home_top_logo = $this->db->get_where('ui_settings',array('type' => 'home_top_logo'))->row()->value;
                    $logo =base_url().'uploads/logo_image/logo_'.$home_top_logo.'.png';
                    $final_email = str_replace('[[logo]]',$logo,$final_email);
                }
                $final_email = str_replace('[[body]]',$email_body,$final_email);
                $send_mail  = $this->do_email($from,$from_name,$to, $sub, $final_email);
            }else{
                $send_mail  = $this->do_email($from,$from_name,$to, $sub, $email_body);
            }
            return $send_mail;
        }
        else {
            return false;
        }
    }
    /***custom email sender****/
    
    function do_email($from = '', $from_name = '', $to = '', $sub ='', $msg ='')
    {   
        $this->load->library('email');
        $this->email->set_newline("\r\n");
        $this->email->from($from, $from_name);
        $this->email->to($to);        
        $this->email->subject($sub);
        $this->email->message($msg);
        
        if($this->email->send()){
            return true;
        }else{
            echo $this->email->print_debugger();
            return false;
        }
        //echo $this->email->print_debugger();
    }
    //============End of user registration=========================================
    function dashboard(){
        header('Content-Type: application/json');
       
        $user = $this->isAuthorize();
        $ids = json_decode($this->db->get_where('user',array('user_id'=>$user->user_id))->row()->wishlist,true);
        $app_banners = $this->db->get('app_banners')->result();
        $json['status'] = 1;
        $ab=0;
        foreach ($app_banners as $key => $value) {
            $json['app_banners'][$ab]['title'] = $value->banner_title;
            $json['app_banners'][$ab]['subtitle'] = $value->banner_subtitle;
            $json['app_banners'][$ab]['image'] = base_url().'uploads/app_banners/'.$value->banner_img;
            $ab++;
        }
        $categories=$this->db->select(['category_id','category_name','banner'])->get('category')->result();
        $cat = 0;
        foreach ($categories as $key => $value) {
            $json['categories'][$cat]['category_id'] = $value->category_id; 
            $json['categories'][$cat]['category_name'] = $value->category_name; 
            $json['categories'][$cat]['banner'] = base_url().'uploads/category_image/'.$value->banner; 
            $cat++;
        }
        $products = $this->db->get('product')->result();
        $pro = 0;$deal=0;
        foreach ($products as $key => $value) {
            $json['products'][$pro]['product_id'] = $value->product_id;
            $json['products'][$pro]['wished'] = (in_array($value->product_id, $ids))?true:false;
            $json['products'][$pro]['category_name'] = $this->crud_model->get_type_name_by_id('category',$value->category,'category_name');
            $json['products'][$pro]['title'] = $value->title;
            $json['products'][$pro]['sale_price'] = round($value->sale_price);
            $json['products'][$pro]['purchase_price'] = round($value->purchase_price);
            $json['products'][$pro]['shipping_cost'] = round($value->shipping_cost);
            $json['products'][$pro]['tag'] = $value->tag;
            $json['products'][$pro]['deal'] =($value->deal=='ok')?1:0;
            $json['products'][$pro]['current_stock'] = (!empty($value->current_stock))?$value->current_stock:0;
            currency($this->wallet_model->user_balance($row['user_id']),'def');
            $json['products'][$pro]['discount'] = ($value->discount_type=='percent')?(($value->discount!='')?$value->discount.'%':'0%'):currency($value->discount);
            $json['products'][$pro]['tax'] = ($value->tax_type=='percent')?(($value->tax!='')?$value->tax.'%':'0%'):currency($value->tax);
            $st = 'D:\xamp\htdocs\anc\uploads\product_image\product_';
            $proImg = base_url().'uploads/product_image/product_';
            $pimg1 =$value->product_id.'_1.jpg'; 
            $pimg2 =$value->product_id.'_2.jpg'; 
            $pimg3 =$value->product_id.'_1_thumb.jpg'; 
            $pimg4 =$value->product_id.'_2.jpg'; 
            $pimg5 =$value->product_id.'_2_thumb.jpg';
            $json['products'][$pro]['product_image'] = (file_exists($st.$pimg1))?$proImg.$pimg1:((file_exists($st.$pimg2))?$proImg.$pimg2:((file_exists($st.$pimg3))?$proImg.$pimg3:((file_exists($st.$pimg4))?$proImg.$pimg4:((file_exists($st.$pimg5))?$proImg.$pimg5:base_url().'uploads/product_image/default.png'))));
            //=================[[for today's deal array]]=========================
            if($value->deal=='ok'){
                $json['todays_deal'][$deal]['product_id'] = $value->product_id;
                $json['todays_deal'][$deal]['wished'] = (in_array($value->product_id, $ids))?true:false;
                $json['todays_deal'][$deal]['category_name'] = $this->crud_model->get_type_name_by_id('category',$value->category,'category_name');
                $json['todays_deal'][$deal]['title'] = $value->title;
                $json['todays_deal'][$deal]['sale_price'] = round($value->sale_price);
                $json['todays_deal'][$deal]['purchase_price'] = round($value->purchase_price);
                $json['todays_deal'][$deal]['shipping_cost'] = $value->shipping_cost;
                $json['todays_deal'][$deal]['tag'] = $value->tag;
                $json['todays_deal'][$deal]['deal'] =($value->deal=='ok')?1:0;
                $json['todays_deal'][$deal]['current_stock'] = (!empty($value->current_stock))?$value->current_stock:0;
                $json['todays_deal'][$deal]['discount'] = ($value->discount_type=='percent')?(($value->discount!='')?$value->discount.'%':'0%'):currency($value->discount);
                $json['todays_deal'][$deal]['tax'] = ($value->tax_type=='percent')?(($value->tax!='')?$value->tax.'%':'0%'):currency($value->tax);
                $json['todays_deal'][$deal]['product_image'] = (file_exists($st.$pimg1))?$proImg.$pimg1:((file_exists($st.$pimg2))?$proImg.$pimg2:((file_exists($st.$pimg3))?$proImg.$pimg3:((file_exists($st.$pimg4))?$proImg.$pimg4:((file_exists($st.$pimg5))?$proImg.$pimg5:base_url().'uploads/product_image/default.png'))));
                $deal++;
            }
            //=================[[for today's deal array]]=========================
            $pro++;
        }
        echo json_encode(array($json));die;
    }
    
    function product_details(){
        header('Content-Type: application/json');
            $user = $this->isAuthorize();
            $id = $this->input->get('pid');
            $products = $this->db->get_where('product',['product_id'=>$id])->row();
            $ids = json_decode($this->db->get_where('user',array('user_id'=>$user->user_id))->row()->wishlist,true);
            if(!empty($products)){
                $json['status'] = 1;
                $json['product_id'] = $products->product_id;
                $json['wished'] = (in_array($products->product_id, $ids))?true:false;
                $json['category_name'] = $this->crud_model->get_type_name_by_id('category',$products->category,'category_name');
                $json['title'] = $products->title;
                $json['sale_price'] = round($products->sale_price);
                $json['purchase_price'] = round($products->purchase_price);
                $json['shipping_cost'] = round($products->shipping_cost);
                $json['tag'] = $products->tag;
                $json['deal'] =($products->deal=='ok')?1:0;
                $json['current_stock'] = (!empty($products->current_stock))?$products->current_stock:0;
                round($this->wallet_model->user_balance($row['user_id']),'def');
                $json['discount'] = ($products->discount_type=='percent')?(($products->discount!='')?$products->discount.'%':'0%'):currency($products->discount);
                $json['tax'] = ($products->tax_type=='percent')?(($products->tax!='')?$products->tax.'%':'0%'):currency($products->tax);
                $images = $this->crud_model->file_view('product',$products->product_id,'','','thumb','src','multi','all');
                if($images){
                    foreach ($images as $row1){
                        $json['product_images'][] = $row1;
                    }
                }

                $p = $this->db->select(['product_details','sale_id'])->get_where('sale',['buyer'=>$user->user_id])->result();
                $json["purchased_prviously"] = 0;

                foreach ($p as $key => $value) {
                    // echo '<pre>';print_r($value);
                    if($value->sale_id==77){
                        // echo '<pre>';print_r($value);
                        for($i=0;$i<count(json_decode($value->product_details));$i++){
                            // $json['purchased_prviously'] = (json_decode($value->product_details)[$i]->product_id==$id)?1:0;
                            $j['pid'][] = json_decode($value->product_details)[$i]->product_id;
                        }
                        $json['purchased_prviously'] = (in_array($id,$j['pid']))?1:0;
                        // echo '<hello>';die;
                    }
                    
                }
                $ps = $this->crud_model->get_additional_fields($products->product_id);
                if(!empty($ps)){
                    foreach($ps as $row1){
                        $json[$row1['name']] = $row1['value'];
                    }
                }
                // echo '<pre>';print_r($ps);die;
            }else{
                $json['status'] = 0;
                $json['msg'] = 'Product Not available';
            }
        echo json_encode($json);die;
    }
    //=============[[function to show user's bonus transaction]]===================
    function myBonusTransaction(){
        header('Content-Type: application/json');
        $user = $this->isAuthorize();
        $bonus_transaction = $this->db->get_where('bonus_transaction',['user_id'=>$user->user_id])->result();
        if(!empty($bonus_transaction)){
            $json['status'] = 1;$i=0;   
            foreach ($bonus_transaction as $key => $value) {
                $json['bonus_transaction'][$i]['reason'] = ucwords($value->bonus_for);
                $json['bonus_transaction'][$i]['refer_amt'] = currency($value->refer_amt);
                $json['bonus_transaction'][$i]['created_at'] = $value->created_at;
                $json['bonus_transaction'][$i]['bonus_from'] =($value->buyer_id!=$user->user_id)?$this->db->get_where('user',['user_id'=>$value->buyer_id])->row()->username:'';
                $json['bonus_transaction'][$i]['percentage'] = $value->percent.'%';
                $json['bonus_transaction'][$i]['bonus_for'] = $value->bonus_for;
                $i++;
            }
        }else{
            $json['status'] = 0;
            $json['msg'] = 'NO transaction available for right Now';
        }
        echo json_encode($json);die;
    }
    //=============[[Function to get wishlist]]==============================
    function myWhishList(){
        header('Content-Type: application/json');
        $user = $this->isAuthorize();
        $ids = json_decode($this->db->get_where('user',array('user_id'=>$user->user_id))->row()->wishlist,true);
        $json = array();
        if(!empty($ids)){
            $i=0;$d = array();
            foreach ($ids as $key => $value) {
                $products = $this->db->get_where('product',['product_id'=>$value])->row(); 
                $d['product_id'] = $products->product_id;
                $d['title'] = $products->title;
                $d['purchase_price'] = currency($products->purchase_price);
                $d['shipping_cost'] = $products->shipping_cost;
                $d['tag'] = $products->tag;
                $d['current_stock'] = (!empty($products->current_stock))?'Available':'Not Available';
                $st = 'D:\xamp\htdocs\anc\uploads\product_image\product_';
                $proImg = base_url().'uploads/product_image/product_';
                $pimg1 =$products->product_id.'_1.jpg'; 
                $pimg2 =$products->product_id.'_2.jpg'; 
                $pimg3 =$products->product_id.'_1_thumb.jpg'; 
                $pimg4 =$products->product_id.'_2.jpg'; 
                $pimg5 =$products->product_id.'_2_thumb.jpg';
                $d['product_image'] = (file_exists($st.$pimg1))?$proImg.$pimg1:((file_exists($st.$pimg2))?$proImg.$pimg2:((file_exists($st.$pimg3))?$proImg.$pimg3:((file_exists($st.$pimg4))?$proImg.$pimg4:((file_exists($st.$pimg5))?$proImg.$pimg5:base_url().'uploads/product_image/default.png'))));
               // echo '<pre>';print_r($products);die;
               $json[] = $d;
                $i++;
            }
        }
        echo json_encode($json);die;
        // echo '<pre>';print_r($ids);die;
    }
    function removeWhishList(){
        header('Content-Type: application/json');
        if($this->input->method()=='post'){
            $user = $this->isAuthorize();
            $pid = $this->input->post('pid');
            $ids = json_decode($this->db->get_where('user',array('user_id'=>$user->user_id))->row()->wishlist,true);
            if(!empty($ids)){
                $json['status'] = 1;$i=0;$rds = array();
                foreach ($ids as $key => $value) {
                    if($value!=$pid){
                        $rds[] = $value;
                    }
                }
                $dds = json_encode($rds); 
                if(count($ids)!=count($rds)){
                    $rd = $this->db->where('user_id',$user->user_id)->update('user',['wishlist'=>$dds]);
                    $json['status'] = 1;
                    $json['msg'] = 'Product has been removed from Wishlist';
                }else{
                    $json['status'] = 0;
                    $json['msg'] = 'Product not available in the Wishlist';
                }
            }else{
                $json['status'] = 0;
                $json['msg'] = 'Your wishlist is empty...';
            }
            // echo '<pre>';print_r($dds);die;
            // $ids = json_encode($this->db->where(array('user_id'=>$user->user_id))->update(''));
        }else{
            $json['status'] = 0;
            $json['msg'] = 'you are not authorized to access';
        }
        echo json_encode($json);die;
    }
    function addtowishlist(){
       header('Content-Type: application/json');
        if($this->input->method()=='post'){
            $user = $this->isAuthorize();
            $pid = $this->input->post('pid');
            $ids = json_decode($this->db->get_where('user',array('user_id'=>$user->user_id))->row()->wishlist,true);
                $i=0;
                $rds = count($ids);
                if(in_array($pid, $ids)){
                    $k =array_search($pid, $ids);
                    unset($ids[$k]);
                    $ids = array_values(array_filter($ids));
                    $dds1 = json_encode($ids); 
                    $this->db->where('user_id',$user->user_id)->update('user',['wishlist'=>$dds1]);
                    // echo '<pre>';print_r($ids);die;
                    $json['status'] = 1;
                    $json['msg'] = 'Product removed from Wishlist';
                    echo json_encode($json);die;
                }else{
                    $ids[] = $pid;
                }
                // echo $rds.'<br>'.count($ids).in_array($pid,$ids);die;
                $dds = json_encode($ids); 
                if(count($ids)>$rds){
                    $rd = $this->db->where('user_id',$user->user_id)->update('user',['wishlist'=>$dds]);
                    $json['status'] = 1;
                    $json['msg'] = 'Product has been added to Wishlist';
                }else{
                    $json['status'] = 0;
                    $json['msg'] = 'Product not available in the Wishlist';
                }
            
        }else{
            $json['status'] = 0;
            $json['msg'] = 'you are not authorized to access';
        }
        echo json_encode($json);die; 
    }
    function addtocart(){
       header('Content-Type: application/json');
        if($this->input->method()=='post'){
            $user = $this->isAuthorize();
            $pid = $this->input->post('pid');
            $product = $this->db->get_where('product',['product_id'=>$pid])->row();
            $cart = array();
            // echo '<pre>';print_r($product);die;
            if(!empty($product)){
                if($product->current_stock!=0){

                    $cart_items = json_decode($this->db->get_where('user',['user_id'=>$user->user_id])->row()->cart,true);
                    // echo '<pre>';print_r($cart_items);die;
                    if(empty($cart_items)){
                        $cart_contents['product_id'] = $pid;
                        $cart_contents['name'] = $product->title; 
                        $cart_contents['qty'] = 1;
                        $cart_contents['category_name'] = $this->crud_model->get_type_name_by_id('category',$product->category,'category_name');
                        $cart_contents['sale_price'] = round($product->sale_price); 
                        $cart_contents['purchase_price'] = round($product->purchase_price); 
                        $cart_contents['shipping'] = round($product->shipping_cost); 
                        $cart_contents['discount'] = ($product->discount_type=='amount')?$product->discount:($product->discount*$product->purchase_price)/100; 
                        $cart_contents['coupon'] = 0; 
                        $cart_contents['image'] = $this->crud_model->file_view('product',$pid,'','','thumb','src','multi','all');
                        $cart_contents['subtotal'] = round($product->purchase_price * 1); 
                        $cart[] = $cart_contents;
                        $dds = json_encode($cart);
                        $this->db->where('user_id',$user->user_id)->update('user',['cart'=>$dds]);
                        $json['status']= 1;
                        $json['msg'] = 'successfully added to Cart';
                        // echo '<pre>';print_r($dds);die;

                    }else{
                        $ct = array();
                        foreach ($cart_items as $key => $value) {
                            if($value['product_id']==$pid){
                                $json['status'] = 0;
                                $json['msg'] = 'Product already available in the cart';
                                echo json_encode($json);die;
                            }
                            $ct[] = $value;
                        }
                        $cart_contents['product_id'] = $pid;
                        $cart_contents['name'] = $product->title; 
                        $cart_contents['qty'] = 1;
                        $cart_contents['category_name'] = $this->crud_model->get_type_name_by_id('category',$product->category,'category_name'); 
                        $cart_contents['sale_price'] = round($product->sale_price); 
                        $cart_contents['purchase_price'] = round($product->purchase_price); 
                        $cart_contents['coupon'] = 0; 
                        $cart_contents['shipping'] = $product->shipping_cost; 
                        $cart_contents['discount'] = ($product->discount_type=='amount')?$product->discount:($product->discount*$product->purchase_price)/100; 
                        $cart_contents['image'] = $this->crud_model->file_view('product',$pid,'','','thumb','src','multi','all');
                        $cart_contents['subtotal'] = round($product->purchase_price * 1); 
                        $ct[] = $cart_contents;
                        $ctencode = json_encode($ct);
                        $this->db->where('user_id',$user->user_id)->update('user',['cart'=>$ctencode]);
                        $json['status']= 1;
                        $json['msg'] = 'successfully added to Cart';
                    }
                    // echo empty($cart_items);
                    // echo '<pre>';print($cart_items);die;
                }else{
                    $json['status'] = 0;
                    $json['msg'] = 'Product Out of stock';
                }
            }else{
                $json['status'] = 0;
                $json['msg'] = 'Product not available for now';
            }
        }else{
            $json['status'] = 0;
            $json['msg'] = 'you are not authorized to access';
        }
        echo json_encode($json);die; 
    }
    function removeFromCart(){
        header('Content-Type: application/json');
        if($this->input->method()=='post'){
            $user = $this->isAuthorize();
            $pid = $this->input->post('pid');
            $product = $this->db->get_where('product',['product_id'=>$pid])->row();
            if(!empty($product)){
                $cart_items = json_decode($this->db->get_where('user',['user_id'=>$user->user_id])->row()->cart,true);
                // echo '<pre>';print_r($cart_items);die;
                if(!empty($cart_items)){
                    $ct = array();
                    foreach ($cart_items as $key => $value) {
                        if($value['product_id']==$pid){
                            unset($cart_items[$key]);
                            continue;
                        }
                        $ct[] = $value;
                    }
                    $ctencode = json_encode($ct);
                    $this->db->where('user_id',$user->user_id)->update('user',['cart'=>$ctencode]);
                    $json['status']= 1;
                    $json['msg'] = 'Removed from the Cart';
                    // echo '<pre>';print_r($ct);die;
                }
            }else{
                $json['status'] = 0;
                $json['msg'] = 'Product not available';
            }
        }else{
            $json['status'] = 0;
            $json['msg'] = 'you are not authorized to access';
        }
        echo json_encode($json);die; 
    }
    function viewCart(){
        header('Content-Type: application/json');
        $user = $this->isAuthorize();
        $cart_data = json_decode($user->cart,true);
        // echo json_encode($cart_data);die;
        if(!empty($cart_data)){
            $json['status'] = 1;$i=0;$grand_total=0;$total_shipping = 0;$total_discount =0;
            foreach ($cart_data as $key => $value) {
                $json['cart'][$i]['product_id'] = $value['product_id'];
                $json['cart'][$i]['name'] = $value['name'];
                $json['cart'][$i]['qty'] = $value['qty'];
                $json['cart'][$i]['category'] = (!empty($value['category_name']))?$value['category_name']:''; 
                $json['cart'][$i]['sale_price'] = round($value['sale_price']); 
                $json['cart'][$i]['purchase_price'] = round($value['purchase_price']); 
                $json['cart'][$i]['shipping'] = round($value['shipping']);
                $json['cart'][$i]['discount'] = round($value['discount']);
                $json['cart'][$i]['coupon'] = $value['coupon'];
                $json['cart'][$i]['image'] = implode(',',$value['image']);
                $json['cart'][$i]['subtotal'] = ($json['cart'][$i]['purchase_price']*$value['qty'])+$json['cart'][$i]['shipping'];
                $grand_total += ($value['purchase_price'] * $value['qty']);
                $total_shipping += ($value['shipping'] * $value['qty']);
                $total_discount += ($value['discount'] * $value['qty']);
                $i++;
            }
            $json['total_amount'] = round($grand_total);
            $json['total_shipping'] = round($total_shipping);
            $json['total_discount'] = round($total_discount);
            $json['grand_total'] = round($grand_total) + round($total_shipping);
        }else{
            $json['status'] = 0;
            $json['msg'] = 'Cart is empty';
        }
        echo json_encode(array($json));die;
        // echo '<pre>';print_r($cart_data);die;
    }

    function changeProductQuantity(){
        header('Content-Type: application/json');
        if($this->input->method()=='post'){
            $user = $this->isAuthorize();
            $pid = $this->input->post('pid');
            $qty = $this->input->post('qty');
            $type = $this->input->post('type');
            $cart_items = json_decode($this->db->get_where('user',['user_id'=>$user->user_id])->row()->cart,true);
            if(!empty($cart_items)){
                $ct = array();
                foreach ($cart_items as $key => $value) {
                    if($pid==$value['product_id']){
                        if($type=='add'){
                            $value['qty'] = $value['qty']+$qty;
                        }
                        if($type=="minus"){
                            $value['qty'] = $value['qty']-$qty;
                        }
                        $value['subtotal'] = currency(($value['price'] * $value['qty'])+$value['shipping']);
                    }
                    $ct[] = $value;
                }
                $dts = json_encode($ct);
                $this->db->where('user_id',$user->user_id)->update('user',['cart'=>$dts]);
                $json['status'] = 1;
                $json['status'] = 'Cart Updated';
                // echo '<pre>';print_r($dts);die;
            }
        }else{
            $json['status'] = 0;
            $json['msg'] = 'You are not authorized to access';
        }
        echo json_encode($json);die;
    }
    //=============[[End of cart functions]]=====================================
    //=============[[Checkout 1]]=====================================
    function checkout1(){
        header('Content-Type: application/json');
        $user = $this->isAuthorize();
        $pid = $this->input->get('pid');
        // echo $pid;die;
        if($pid!=''){
            $products = $this->db->get_where('product',['product_id'=>$pid])->row();
            // echo '<pre>';print_r($products);die;
            $i=0;$grand_total=0;$total_shipping = 0;$total_discount =0;
            if(!empty((array)$products)){
                $json['order_id'] = rand(0000,9999).time();
                $product['product_id'] =$json['product_id'] = $products->product_id;
                $product['name'] =$json['name'] = $products->title;
                $product['qty'] =$json['qty'] = 1;
                $product['category_name'] =$json['category_name'] = $this->crud_model->get_type_name_by_id('category',$products->category,'category_name');
                $product['sale_price'] =$json['sale_price'] = round($products->sale_price); 
                $product['purchase_price'] =$json['purchase_price'] = round($products->purchase_price); 
                $product['shipping'] =$json['shipping'] = round($products->shipping_cost); 
                $product['discount'] =$json['discount'] = ($products->discount_type=='amount')?$products->discount:($products->discount*$products->purchase_price)/100; 
                $product['coupon'] =$json['coupon'] = 0; 
                $product['image'] =$json['image'] = implode(',',$this->crud_model->file_view('product',$pid,'','','thumb','src','multi','all'));
                $product['subtotal'] =$json['subtotal'] = round($products->purchase_price * 1); 

                $d['total_amount'] = $json['total_amount'] = round($json['subtotal']);
                $d['total_shipping'] = $json['total_shipping'] = round($json['shipping']);
                $d['total_discount'] = $json['total_discount'] = round($json['discount']);
                $d['grand_total'] = $json['grand_total'] = round($json['total_amount']) + round($json['total_shipping']);

                $h['sale_code'] = $json['order_id'];
                $h['buyer'] = $user->user_id;
                $h['product_details'] = json_encode(array($product));
                $h['payment_details'] = json_encode($d);
                $h['shipping'] = $json['total_shipping'];
                $h['grand_total'] = $json['grand_total'];
                $this->db->insert('sale',$h);
            }else{
                $json['status'] = 0;
                $json['msg'] = 'Product not available';
            }
        }else{
            $cart_data = json_decode($user->cart,true);
            // echo json_encode($cart_data);die;
            if(!empty($cart_data)){
                $json['status'] = 1;
                $i=0;$grand_total=0;$total_shipping = 0;$total_discount =0;
                foreach ($cart_data as $key => $value) {
                    // echo json_encode($value);die;
                    $json['cart_items'][$i]['product_id'] = $value['product_id'];
                    $json['cart_items'][$i]['name'] = $value['name'];
                    $json['cart_items'][$i]['qty'] = $value['qty'];
                    $json['cart_items'][$i]['category'] = (!empty($value['category_name']))?$value['category_name']:''; 
                    $json['cart_items'][$i]['sale_price'] = round($value['sale_price']); 
                    $json['cart_items'][$i]['purchase_price'] = round($value['purchase_price']); 
                    $json['cart_items'][$i]['shipping'] = round($value['shipping']);
                    $json['cart_items'][$i]['discount'] = round($value['discount']);
                    $json['cart_items'][$i]['coupon'] = $value['coupon'];
                    $json['cart_items'][$i]['image'] = implode(',',$value['image']);
                    $json['cart_items'][$i]['subtotal'] = ($json['cart_items'][$i]['purchase_price']*$value['qty'])+$json['cart_items'][$i]['shipping'];
                    $grand_total += ($value['purchase_price'] * $value['qty']);
                    $total_shipping += ($value['shipping'] * $value['qty']);
                    $total_discount += ($value['discount'] * $value['qty']);
                    $i++;
                }
                
                $json['order_id'] = rand(0000,9999).time();
                $d['total_amount'] = $json['total_amount'] = round($grand_total);
                $d['total_shipping'] = $json['total_shipping'] = round($total_shipping);
                $d['total_discount'] = $json['total_discount'] = round($total_discount);
                $d['grand_total'] = $json['grand_total'] = round($grand_total) + round($total_shipping);
                // $d['payment_details'] = json_encode($json['cart_items']);

                $h['sale_code'] = $json['order_id'];
                $h['buyer'] = $user->user_id;
                $h['product_details'] = json_encode($json['cart_items']);
                $h['payment_details'] = json_encode($d);
                $h['shipping'] = $json['total_shipping'];
                $h['grand_total'] = $json['grand_total'];

                $this->db->insert('sale',$h);
                // echo json_encode($this->db->error());die;
            }else{
                $json['status'] = 0;
                $json['msg'] = 'Cart is empty';
            }
        }
        
        echo json_encode(array($json));die;
    }
    function finalcheckout(){
        header('Content-Type: applicat/ion/json');
        if($this->input->method()=='post'){
            $user = $this->isAuthorize();
            $orderid = $this->input->post('orderid');
            $orderaddress = $this->input->post('addr_id')-1;
            $payment_type = $this->input->post('payment_type');
            $addrs = explode('$-$', $this->db->get_where('user',['user_id'=>$user->user_id])->row()->addresses);
            // $shipping = json_decode($addr[$orderaddress])->full_address;
            // echo '<pre>';print_r($addrs[$orderaddress]);die;
            $val = json_decode($addrs[$orderaddress]);
            // echo '<pre>';print_r($val);die;
            $full_address = $val->full_name.', Mobile Number: '.$val->mobile_number.', '.$val->house_no.', '.$val->street.', '.$val->landmark.', '.$val->city.', '.$val->state.', Pin Code: - '.$val->pin_code;
            $d['payment_status'] = json_encode([array('admin'=>'','status'=>'due')]);
            $d['delivery_status'] = json_encode([array('admin'=>'','status'=>'pending','comment'=>'','delivery_time'=>time())]);
            $d['sale_datetime'] = time();
            $d['shipping_address'] = $addrs[$orderaddress];
            $d['payment_type'] = $payment_type;
            // echo $d['payment_status'];
            $this->db->where('sale_code',$orderid)->update('sale',$d);
            $this->db->where('user_id',$user->user_id)->update('user',['cart'=>null]);
            $json['stauts'] = 1;
            $json['msg'] = 'Order has been placed';
            // echo $full_address;die;
        }else{
            $json['status'] = 0;
            $json['msg'] = 'You are not authorized to access';
        }
        echo json_encode($json);die;
    }
    //=============[[End of Checkout 1]]=====================================
    //=============[[My orders api]]=============================================
    function myOrders(){
        header('Content-Type: application/json');
        $user = $this->isAuthorize();
        $sale_data = $this->db->get_where('sale',['buyer'=>$user->user_id])->result();
        // echo '<pre>';print_r($sale_data);die;
        if(!empty($sale_data)){
            $json['status'] = 0;$i=0;$c = array();
            foreach ($sale_data as $key => $value) {
                if($value->sale_id==82){
                    $product_details = json_decode($value->product_details,true);
                    $json['itemqty'] = count($product_details);
                    $json['orderid'] = $value->sale_code;
                    $json['totalprice'] = $value->grand_total;
                    $shipping = json_decode($value->shipping_address);
                    $json['shipping_address'] = $shipping->full_name.', '.$shipping->house_no.', '.$shipping->street.', '.$shipping->landmark.', '.$shipping->city.', '.$shipping->state.' - '.$shipping->pin_code;
                    $json['placed_date'] = date('d M, Y',$value->sale_datetime);
                    // echo '<pre>';print_r($value);die;
                    foreach ($product_details as $key1 => $value1) {
                        // $value1['order_id'] = $value->sale_code;
                        $shipping = json_decode($value->shipping_address);
                        // $value1['shipping_phone'] = $shipping->mobile_number;
                        // $value1['alternate_mobile'] = ($shipping->alternate_mobile!='')?$shipping->alternate_mobile:'';
                        // $value1['payment_type'] = str_replace('_', ' ', ucwords($value->payment_type));
                        // $value1['purchase_date'] = date('d M, Y',$value->sale_datetime);
                        // $value1['payment_status'] = json_decode($value->payment_status)[0]->status;
                        // $value1['order_status'] = ($value->order_status==0)?'pending':(($value->order_status==1)?'Delivered':(($value->order_status==2)?'On Delivery':'Cancelled'));
                        $value1['image'] = explode(',',$value1['image'])[0];
                        unset($value1['category_name'],$value1['shipping'],$value1['discount'],$value1['coupon']);
                        $c[] = $value1;
                    }
                }
            }
            $json['status'] = 1;
            $json['items'] = $c;
            // echo '<pre>';print_r($c);die;
        }else{
            $json['status'] = 0;
            $json['msg'] = 'No order available';
        }
        echo json_encode($json);die;
    }
    //=============================================================================
    function userProfile(){
        header('Content-Type: application/json');
        $user = $this->isAuthorize();
        $json['status'] = 1;
        $json['username'] = (!empty($user->username))?$user->username:'';
        // $json['surname'] = (!empty($user->surname))?$user->surname:'';
        $json['email'] = (!empty($user->email))?$user->email:'';
        $json['phone'] = $user->phone;
        $json['gender'] = ucwords($user->gender);
        $json['refercode'] = $user->refercode;
        $userdata = explode('$-$', $user->addresses);
        // if(!empty($user->addresses)){
            //     $i =1;
            //     $json['status'] = 1;
            //     foreach ($userdata as $key => $value) {
            //         $val = json_decode($value);
            //         $json['addresses'][$i] = $val->full_name.', Mobile Number: '.$val->mobile_number.', '.$val->house_no.', '.$val->street.', '.$val->landmark.', '.$val->city.', '.$val->state.', Pin Code: - '.$val->pin_code;
            //         $i++;
            //     }
            // }else{
            //     $json['addresses'] = $user->addresses;
        // }
        
        $json['profile_photo'] = (!empty($user->profile_photo))?base_url().'uploads/user_image/'.$user->profile_photo:base_url().'uploads/user_image/default.jpg';
        $json['default_address'] = (!empty($user->default_address))?$user->default_address:'';
        // $json['city'] = $user->city;
        // $json['zip'] = $user->zip;
        // $json['fb_id'] = $user->fb_id;
        // $json['g_id'] = $user->g_id;
        $json['registered_at'] = $user->created_at;
    
        $json['total_orders'] =count($this->db->get_where('sale',['buyer'=>$user->user_id])->result_array());
        // echo $this->db->error();die;
        $json['wishlist'] = count(json_decode($user->wishlist,true));
        $json['cart'] = count(json_decode($user->cart,true));
        // $json['last_login'] = $user->last_login;
        // $json['country'] = $user->country;
        // $json['state'] = $user->state;
        $json['wallet'] = currency(base64_decode($user->wallet));
        echo json_encode($json);die;
        // echo '<pre>';print_r($user);die;
    }
    
    function edituserprofile(){
        header('Content-Type: application/json');
        $user = $this->isAuthorize();
        $json = array();
        $data['username'] = $this->input->post('fullname');
        $data['email'] = $this->input->post('email');
        $data['gender'] = strtolower($this->input->post('gender'));
        $data['phone'] = $this->input->post('phone');
        
        $d = $this->db->where('user_id',$user->user_id)->update('user',$data);
        if($d){
            $json['status'] = 1;
            $json['msg'] = 'Profile Updated';
        }else{
            $json['status'] = 0;
            $json['msg'] = 'Something worng';
        }
        echo json_encode($json);die;
    }

    function uploaduserimage(){
        header('Content-Type: application/json');
        if($this->input->method()=='post'){
            $user = $this->isAuthorize();
            $json = array();
            if(isset($_FILES['file'])){
                $image['profile_photo'] = 'user_image_'.$user->user_id.rand(0000,9999).'.jpg';
                $preImage = !empty($user->profile_photo)?$user->profile_photo:'';
                $imdb = $this->db->where('user_id',$user->user_id)->update('user',$image);
                if($imdb){
                    $im = move_uploaded_file($_FILES['file']['tmp_name'],'uploads/user_image/'.$image['profile_photo']);
                    echo '<pre>';var_dump($im);die;
                    if($im){
                        if($preImage!=''){
                            $path= $_SERVER["DOCUMENT_ROOT"].'/saymart/uploads/user_image/'.$preImage;
                            @unlink($path);
                        }
                    }
                    $json['status'] = 1;
                    $json['msg'] = 'Profile image updated successfully';
                }
            }
        }else{
            $json['status'] = 0;
            $json['msg'] = 'You are not authorized to access';
        }
        echo json_encode($json);die;
    }
    function change_password(){
        header('Content-Type: application/json');
        if($this->input->method()=='post'){
            $user = $this->isAuthorize();
            $json = array();
            $current = $this->input->post('current');
            $new = $this->input->post('new');            
            if (sha1($current)==$user->password) {
                if($current!=$new){
                    $this->db->where('user_id',$user->user_id)->update('user',['password'=>sha1($new)]);
                    $json['status'] = 1;
                    $json['msg'] = 'your password has been changed successfully.';
                }else{
                    $json['status'] = 0;
                    $json['msg'] = 'New password should not be as previous';
                }
            }else{
                $json['status'] = 0;
                $json['msg'] = 'you current password in not correct ';
            }
        }else{
            $json['status'] = 0;
            $json['msg'] = 'You are not authorized to access';
        }
        echo json_encode($json);die;
    }
    function forget_pass(){
        header('Content-Type: application/json');
        if($this->input->method()=='post'){
            $json = array();
            $phone = $this->input->post('phone');
            $data = $this->db->get_where('user',['phone'=>$phone])->row();         
            if (!empty($data)) {
                $otp = 1234;
                $this->db->where('user_id',$data->user_id)->update('user',['otp'=>$otp]);
                $json['status'] = 1;
                $json['msg'] = 'OTP sent to your mobile number';
            }else{
                $json['status'] = 0;
                $json['msg'] = 'This mobile number is not registered with us';
            }
        }else{
            $json['status'] = 0;
            $json['msg'] = 'You are not authorized to access';
        }
        echo json_encode($json);die;
    }
    function resendtoresetotp(){
        header('Content-Type: application/json');
        if($this->input->method()=='post'){
            $json = array();
            $phone = $this->input->post('phone');
            $data = $this->db->get_where('user',['phone'=>$phone])->row();
                $otp = 1234;
                $this->db->where('user_id',$data->user_id)->update('user',['otp'=>$otp]);
                $json['status'] = 1;
                $json['msg'] = 'OTP re-sent to your mobile number';
            
        }else{
            $json['status'] = 0;
            $json['msg'] = 'You are not authorized to access';
        }
        echo json_encode($json);die;
    }
    function matchcodetoresetpass(){
        header('Content-Type: application/json');
        if($this->input->method()=='post'){
            $json = array();
            $phone = $this->input->post('phone');
            $otp = $this->input->post('otp');
            $newpass = $this->input->post('password');
            if(empty($this->input->post('otp'))){
                $json['status'] = 0;
                $json['msg'] = 'Enter otp here';
                echo json_encode($json);die;
            }
            if(empty($this->input->post('password'))){
                $json['status'] = 0;
                $json['msg'] = 'Enter password to reset';
                echo json_encode($json);die;
            }
            $data = $this->db->get_where('user',['phone'=>$phone,'otp'=>$otp])->row();         
            if (!empty($data)) {
                $this->db->where('user_id',$data->user_id)->update('user',['otp'=>NULL,'password'=>sha1($newpass)]);
                $json['status'] = 1;
                $json['msg'] = 'Password Reset successfully';
            }else{
                $json['status'] = 0;
                $json['msg'] = 'Invalid OTP Entered';
            }
        }else{
            $json['status'] = 0;
            $json['msg'] = 'You are not authorized to access';
        }
        echo json_encode($json);die;
    }
    function addnewAddress(){
        header('Content-Type: application/json');
        $user = $this->isAuthorize();
        $json = array();
        if($this->input->method()=='post'){
            if($this->input->post('id')!=''){
                $id = $this->input->post('id')-1;
                $all_addr = explode('$-$', $this->db->get_where('user',['user_id'=>$user->user_id])->row()->addresses);
                // echo $this->db->get_where('user',['user_id'=>$user->user_id])->row()->addresses;
                $addr = json_decode($all_addr[$id],true);
                $addr['full_name'] = $this->input->post('full_name');
                $addr['address_type'] = $this->input->post('type');       //work or home
                $addr['mobile_number'] = $this->input->post('mobile_number');
                $addr['alternate_mobile'] = ($this->input->post('alternate_mobile')!='')?$this->input->post('alternate_mobile'):'';
                $addr['pin_code'] = $this->input->post('pin_code');
                $addr['house_no'] = $this->input->post('house_no');
                $addr['street'] = $this->input->post('street');
                $addr['landmark'] = ($this->input->post('landmark')!='')?$this->input->post('landmark'):'';
                $addr['city'] = $this->input->post('city');
                $addr['state'] = $this->input->post('state');
                $all_addr[$id] = json_encode($addr);
                $d['addresses'] = implode('$-$',$all_addr);
                $this->db->where('user_id',$user->user_id)->update('user',$d);
            }else{
                $data['full_name'] = $this->input->post('full_name');
                $data['address_type'] = $this->input->post('type');       //work or home
                $data['mobile_number'] = $this->input->post('mobile_number');
                $data['alternate_mobile'] = ($this->input->post('alternate_mobile')!='')?$this->input->post('alternate_mobile'):'';
                $data['pin_code'] = $this->input->post('pin_code');
                $data['house_no'] = $this->input->post('house_no');
                $data['street'] = $this->input->post('street');
                $data['landmark'] = ($this->input->post('landmark')!='')?$this->input->post('landmark'):'';
                $data['city'] = $this->input->post('city');
                $data['state'] = $this->input->post('state');
                $addresses = $this->db->get_where('user',['user_id'=>$user->user_id])->row()->addresses;
               // echo '<pre>';print_r($addresses);die;
                $add = json_encode($data);
                $d = array();
                if(!empty($addresses)){
                    $d['addresses'] = $addresses.'$-$'.$add;
                }else{
                    $d['addresses'] = $add;
                }
                $this->db->where('user_id',$user->user_id)->update('user',$d); 
            }
            
            $json['status'] = 1;
            $json['msg'] = 'Your address added successfully';
        }else{
            $json['status'] = 0;
            $json['msg'] = 'You are not authorized to access';
        }
        echo json_encode($json);die;
    }
    function myaddresses(){
        header('Content-Type: application/json');
        $user = $this->isAuthorize();
        $json = array();
        // echo '<pre>';var_dump($this->db->get_where('user',['user_id'=>$user->user_id])->row());die;
        // if($this->input->method()=='post'){
            $userdata = explode('$-$', $this->db->get_where('user',['user_id'=>$user->user_id])->row()->addresses);
            // echo json_encode(json_decode($this->db->get_where('user',['user_id'=>$user->user_id])->row()->addresses,true));die;
            if(!empty($this->db->get_where('user',['user_id'=>$user->user_id])->row()->addresses)){
                $i =1;$d = array();
                foreach ($userdata as $key => $value) {
                    $val = json_decode($value);
                    // echo json_encode($val);die;
                    $d['id'] = $i;
                    $d['full_address'] = $val->full_name.', Mobile Number: '.$val->mobile_number.', '.$val->house_no.', '.$val->street.', '.$val->landmark.', '.$val->city.', '.$val->state.', Pin Code: - '.$val->pin_code;
                    $d['full_name'] = $val->full_name;
                    $d['mobile_number'] = $val->mobile_number;
                    $d['alternate_mobile'] = $val->alternate_mobile;
                    $d['address_type'] = $val->address_type;
                    $d['pin_code'] = $val->pin_code;
                    $d['house_no'] = $val->house_no;
                    $d['street'] = $val->street;
                    $d['landmark'] = $val->landmark;
                    $d['city'] = $val->city;
                    $d['state'] = $val->state;
                    $json[] =$d;
                    $i++;
                }
            }
            
            echo json_encode($json);die;
        // }   

    }
    function removeAddress(){
        header('Content-Type: application/json');
        $user = $this->isAuthorize();
        $json = array();
        if($this->input->method()=='post'){
            $index = $this->input->post('index')-1;
            $userdata = explode('$-$', $this->db->get_where('user',['user_id'=>$user->user_id])->row()->addresses);
            unset($userdata[$index]);
            $input['addresses'] = implode('$-$', $userdata);
            $this->db->where('user_id',$user->user_id)->update('user',$input);
            $json['status'] = 1;
            $json['msg'] = 'Address Removed';
        }else{
            $json['status'] = 0;
            $json['msg'] = 'You are not authorized to access';
        }
        echo json_encode($json);die;
    }
    function defaultAddress(){
        header('Content-Type: application/json');
        $user = $this->isAuthorize();
        $json = array();
        if($this->input->method()=='post'){
            $index = $this->input->post('index')-1;
            $userdata = explode('$-$', $this->db->get_where('user',['user_id'=>$user->user_id])->row()->addresses);
            $val = json_decode($userdata[$index]);
            $input['default_address'] = $val->full_name.', <br>Mobile Number: '.$val->mobile_number.', <br>'.$val->house_no.', '.$val->street.', '.$val->landmark.', '.$val->city.', '.$val->state.', Pin Code: - '.$val->pin_code;
            // echo '<pre>';print_r($input['default_address']);die;
            $this->db->where('user_id',$user->user_id)->update('user',$input);
            $json['status'] = 1;
            $json['msg'] = 'Primary Address Updated';
        }else{
            $json['status'] = 0;
            $json['msg'] = 'You are not authorized to access';
        }
        echo json_encode($json);die;
    }
    function get_all_categories(){
        header('Content-Type: application/json');
        $user = $this->isAuthorize();
        $categories=$this->db->get('category')->result();
        if(!empty((array)$categories)){
            $cat = 0;
            foreach ($categories as $key => $value) {
                $json['categories'][$cat]['category_id'] = $value->category_id; 
                $json['categories'][$cat]['category_name'] = $value->category_name; 
                $json['categories'][$cat]['image'] = base_url().'uploads/category_image/'.$value->banner; 
                $cat++;
            }
        }else{
            $json['status'] = 0;
            $json['msg'] = 'No Category Available';
        }
        echo json_encode($json);die;
    }
    function get_subcategories(){
        header('Content-Type: application/json');
        $user = $this->isAuthorize();
        $json = array();
        if($this->input->method()=='post'){
            $cat = $this->input->post('category');
            $sub = $this->db->get_where('sub_category',['category'=>$cat])->result();
            // echo json_encode($sub);die;
            if(!empty((array)$sub)){
                $s = 0;
                $json['status'] = 1;
                foreach ($sub as $key => $value){
                    $json['subcategory'][$s]['id'] = $value->sub_category_id;
                    $json['subcategory'][$s]['name'] = $value->sub_category_name;
                    $json['subcategory'][$s]['category'] = $value->category;
                    $json['subcategory'][$s]['category_name'] = $this->crud_model->get_type_name_by_id('category',$value->category,'category_name');
                    
                    $s++;
                }
            }else{
                $json['status'] = 0;
                $json['msg'] = "No Sub Category Available for now";
            }
        }else{
            $json['status'] = 0;
            $json['msg'] = "You are not authorized to access";
        }
        echo json_encode($json);die;
    }
    function productsByCategory(){
        header('Content-Type: application/json');
        $user = $this->isAuthorize();
        $json = array();
        $scat = $this->input->get('cat');
        $type = $this->input->get('type');
        $products = '';$brands = '';
        if($type == 'sub_category'){
            $products = $this->db->get_where('product',['sub_category'=>$scat])->result();
            $brands = json_decode($this->crud_model->get_type_name_by_id('sub_category',$scat,'brand'));
        }
        if($type == 'category'){
            $products = $this->db->get_where('product',['category'=>$scat])->result();
        }
        if($type != 'category' && $type != 'sub_category'){
            $products = $this->db->get_where('product',['category'=>0])->result();
        }
        
        // echo json_encode($brands);die;
        if(!empty((array)$products)){
            $s = 0;
            $pro = 0;
            
            $ids = json_decode($this->db->get_where('user',array('user_id'=>$user->user_id))->row()->wishlist,true);
            foreach ($products as $key => $value) {
                $d['product_id'] = $value->product_id;
                $d['wished'] = (in_array($products->product_id, $ids))?true:false;
                $d['category_name'] = $this->crud_model->get_type_name_by_id('category',$value->category,'category_name');
                $d['sub_category_name'] = $this->crud_model->get_type_name_by_id('sub_category',$value->sub_category,'sub_category_name');
                $d['title'] = $value->title;
                $d['sale_price'] = round($value->sale_price);
                $d['purchase_price'] = round($value->purchase_price);
                $d['shipping_cost'] = $value->shipping_cost;
                $d['tag'] = $value->tag;
                $d['deal'] =($value->deal=='ok')?1:0;
                $d['current_stock'] = (!empty($value->current_stock))?$value->current_stock:0;
                round($this->wallet_model->user_balance($row['user_id']),'def');
                $d['discount'] = ($value->discount_type=='percent')?(($value->discount!='')?$value->discount.'%':'0%'):round($value->discount);
                $d['tax'] = ($value->tax_type=='percent')?(($value->tax!='')?$value->tax.'%':'0%'):round($value->tax);
                $images = $this->crud_model->file_view('product',$value->product_id,'','','thumb','src','multi','all');
                if($images){
                    foreach ($images as $row1){
                        $ds[] = $row1;
                    }
                    $d['product_images'] = implode(',',$ds);
                } 
                $json[] = $d;
                $pro++;
            }
            // if(!empty($brands)){
            //     $b =0;
            //     foreach ($brands as $key1 => $value1) {
            //         $json['brands'][$b] = base_url().'uploads/brand_image/'.$this->crud_model->get_type_name_by_id('brand',$value1,'logo');
            //         $b++;
            //     }
            // }else{
            //     $json['brands'] = [];
            // }
        }else{
            $json['status'] = 0;
            $json['msg'] = "Products not available for this ".translate($type)." now";
        }
        
        echo json_encode($json);die;
    }
    function orderDetail(){
        header('Content-Type: application/json');
        $user = $this->isAuthorize();
        $json = array();
        if($this->input->method()=='post'){
            $oid = $this->input->post('orderid');
            $sale_data = $this->db->get_where('sale',['buyer'=>$user->user_id,'sale_code'=>$oid])->row();
            // echo json_encode($sale_data);die;
            if(!empty($sale_data)){
                $json['status'] = 1;
                $json['order_no'] = $sale_data->sale_code;
                $json['shipping'] = ($sale_data->shipping==0)?'Free Delivery':'Delivery Charge : '.round($sale_data->shipping);
                $json['payment_type'] = $sale_data->payment_type;
                $json['payment_status'] = json_decode($sale_data->payment_status)[0]->status;
                if($json['payment_status']!="due"){
                    $json['payment_date'] = date('d M, Y', json_decode($sale_data->delivery_status)[0]->delivery_time);
                }
                $json['order_amount'] = round($sale_data->grand_total);
                $json['delivery_status'] = json_decode($sale_data->delivery_status)[0]->status;
                $product_details = json_decode($sale_data->product_details,true);
                $c = array();
                // echo '<pre>';print_r($product_details);die;
                foreach($product_details as $key1 => $value1) {
                    unset($value1['rowid']);
                    $c[] = $value1;
                }
                unset($c['rowid']);
                $json['product_details'] = $c;
                $ship = json_decode($sale_data->shipping_address);
                $json['shipping_address'] = $ship->firstname.' '.$ship->lastname.'<br>'.$ship->address1.', '.$ship->address2.', '.$ship->zip;
                $json['shipping_contact'] = $ship->phone;
                $json['shipping_email'] = $ship->email;
            }else{
                $json['status'] = 0;
                $json['msg'] = "Order Data not available";
            }
        }else{
            $json['status'] = 0;
            $json['msg'] = 'You are not authorized to access';
        }
        echo json_encode($json);die;
    }
    function dealproducts(){
        header('Content-Type: application/json');
        $user = $this->isAuthorize();
        $json = array();
        $products = '';$brands = '';
            $products = $this->db->get_where('product',['deal'=>'ok'])->result();
        
        
        // echo json_encode($products);die;
        if(!empty((array)$products)){
            $s = 0;
            $pro = 0;
            
            $ids = json_decode($this->db->get_where('user',array('user_id'=>$user->user_id))->row()->wishlist,true);
            foreach ($products as $key => $value) {
                $d['product_id'] = $value->product_id;
                $d['wished'] = (in_array($products->product_id, $ids))?true:false;
                $d['category_name'] = $this->crud_model->get_type_name_by_id('category',$value->category,'category_name');
                $d['sub_category_name'] = $this->crud_model->get_type_name_by_id('sub_category',$value->sub_category,'sub_category_name');
                $d['title'] = $value->title;
                $d['sale_price'] = round($value->sale_price);
                $d['purchase_price'] = round($value->purchase_price);
                $d['shipping_cost'] = $value->shipping_cost;
                $d['tag'] = $value->tag;
                $d['deal'] =($value->deal=='ok')?1:0;
                $d['current_stock'] = (!empty($value->current_stock))?$value->current_stock:0;
                round($this->wallet_model->user_balance($row['user_id']),'def');
                $d['discount'] = ($value->discount_type=='percent')?(($value->discount!='')?$value->discount.'%':'0%'):round($value->discount);
                $d['tax'] = ($value->tax_type=='percent')?(($value->tax!='')?$value->tax.'%':'0%'):round($value->tax);
                $images = $this->crud_model->file_view('product',$value->product_id,'','','thumb','src','multi','all');
                if($images){
                    foreach ($images as $row1){
                        $ds[] = $row1;
                    }
                    $d['product_images'] = implode(',',$ds);
                } 
                $json[] = $d;
                $pro++;
            }
            // if(!empty($brands)){
            //     $b =0;
            //     foreach ($brands as $key1 => $value1) {
            //         $json['brands'][$b] = base_url().'uploads/brand_image/'.$this->crud_model->get_type_name_by_id('brand',$value1,'logo');
            //         $b++;
            //     }
            // }else{
            //     $json['brands'] = [];
            // }
        }else{
            $json['status'] = 0;
            $json['msg'] = "Products not available for this ".translate($type)." now";
        }
        
        echo json_encode($json);die;
    }
    function allproducts(){
        header('Content-Type: application/json');
        $user = $this->isAuthorize();
        $json = array();
        $products = '';$brands = '';
            $products = $this->db->get('product')->result();
        
        
        // echo json_encode($products);die;
        if(!empty((array)$products)){
            $s = 0;
            $pro = 0;
            
            $ids = json_decode($this->db->get_where('user',array('user_id'=>$user->user_id))->row()->wishlist,true);
            foreach ($products as $key => $value) {
                $d['product_id'] = $value->product_id;
                $d['wished'] = (in_array($products->product_id, $ids))?true:false;
                $d['category_name'] = $this->crud_model->get_type_name_by_id('category',$value->category,'category_name');
                $d['sub_category_name'] = $this->crud_model->get_type_name_by_id('sub_category',$value->sub_category,'sub_category_name');
                $d['title'] = $value->title;
                $d['sale_price'] = round($value->sale_price);
                $d['purchase_price'] = round($value->purchase_price);
                $d['shipping_cost'] = $value->shipping_cost;
                $d['tag'] = $value->tag;
                $d['deal'] =($value->deal=='ok')?1:0;
                $d['current_stock'] = (!empty($value->current_stock))?$value->current_stock:0;
                round($this->wallet_model->user_balance($row['user_id']),'def');
                $d['discount'] = ($value->discount_type=='percent')?(($value->discount!='')?$value->discount.'%':'0%'):round($value->discount);
                $d['tax'] = ($value->tax_type=='percent')?(($value->tax!='')?$value->tax.'%':'0%'):round($value->tax);
                $images = $this->crud_model->file_view('product',$value->product_id,'','','thumb','src','multi','all');
                if($images){
                    foreach ($images as $row1){
                        $ds[] = $row1;
                    }
                    $d['product_images'] = implode(',',$ds);
                } 
                $json[] = $d;
                $pro++;
            }
            // if(!empty($brands)){
            //     $b =0;
            //     foreach ($brands as $key1 => $value1) {
            //         $json['brands'][$b] = base_url().'uploads/brand_image/'.$this->crud_model->get_type_name_by_id('brand',$value1,'logo');
            //         $b++;
            //     }
            // }else{
            //     $json['brands'] = [];
            // }
        }else{
            $json['status'] = 0;
            $json['msg'] = "Products not available for this ".translate($type)." now";
        }
        echo json_encode($json);die;
    }
    function getapidata(){
        header('Content-Type: application/json');
        $data= json_decode(base64_decode($this->input->get('data')));
        $url = $data->url;
        $postString = $data->postString;
        $auth = $data->auth;
        // echo '<pre>';print_r($data->url);die;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,
                    $postString);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded','Auth:Bearer '.$auth));
        // receive server response ...
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $server_output = curl_exec ($ch);
        // echo '<pre>';print($server_output);die;
        curl_close ($ch);
        echo $server_output;die;
    }
    function searchall(){
        header('Content-Type: application/json');
        $user = $this->isAuthorize();
        $key = $this->input->get('key');
        $json = array();
        $products = $this->db->like('title',$key,'both')->select(['product_id','title'])->get('product')->result();
        if(!empty((array)$products)){
            foreach ($products as $key1 => $value) {
                // $d['id'] =$p['id'] = $value->product_id;
                $d['title'] =$p['title'] = $value->title;
                // $result = post('http://example.com', array('foo' => 'bar','name' => 'Wayne'));
                // $p['input'] = base_url().'APIController/getapidata?data';
                // $p['hit_url'] = base_url().'APIController/product_details?pid='.$value->product_id;
                $data['url'] = base_url().'APIController/product_details?pid='.$value->product_id;
                $data['postString'] = '';
                $data['auth'] = $user->auth_key;
                // echo json_encode($data);
                $p['hit_url'] = base_url().'APIController/getapidata?data='.base64_encode(json_encode($data));
                $json[] = $p;
            }
        }
        $cats = $this->db->like('category_name',$key,'both')->select(['category_id','category_name'])->get('category')->result();
        // echo json_encode($cats);die;
        if(!empty((array)$cats)){
            foreach ($cats as $key1 => $value) {
                // $d['id'] =$p['id']    = $value->category_id;
                $d['title'] =$p['title'] = $value->category_name;
                // $result = post('http://example.com', array('foo' => 'bar','name' => 'Wayne'));
                // $p['input'] = base_url().'APIController/getapidata?data';
                $data['url'] = base_url().'APIController/productsByCategory';
                $data['postString'] = 'cat='.$value->category_id.'&type=category';
                $data['auth'] = $user->auth_key;
                // echo json_encode($data);
                $p['hit_url'] = base_url().'APIController/getapidata?data='.base64_encode(json_encode($data));
                $json[] = $p;
            }

        }
        echo json_encode($json);die;
    }
}