<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Auth;
use Image;
Use DB;
class EmployeeController extends Controller
{

	protected $page_name = 'Users';
    public function employees(){
    	/////////
        $data['page_name'] = $this->page_name;
    	//$data['employees'] = User::paginate(5);
        $data['employees'] = User::where('id','!=' , 1)->orderby('id','DESC')->paginate(5);
       //echo '<pre>';
      // print_r($data['employees']->toArray());die();
    	return view('admin/employee_list',$data);
    }
    public function addemployee(){
    	$data['page_name'] = $this->page_name;
    	return view('admin/add_employee',$data);
    }
    public function saveemployee(Request  $request){

    	$this->validate($request,[
                'name' => 'required',
                'email' => 'required|unique:users',
                'password' => 'required',
                'phone' => 'required',
                'photo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ],[
                'name.required' => 'The name field is required.',
                'email.required' => ' The Email field is required.',
                'email.unique' => ' The Email all ready exist.',
                'password.required' => ' The Password field is required.',
                'phone.required' => 'The phone field is required.',
                'photo.required'=>'Photo must be jpeg,png,jpg,gif,svg extention.'
            ]);
        $input = $request->all();
        $input['user_type'] = 'employee';
        $input['created_by'] = Auth::user()->id; 
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);

        $profiledata['address'] = $input['address'];
        $profiledata['phone'] = $input['phone'];
        $profiledata['department'] = $input['department'];
        $profiledata['photo'] = '';
        if($request->photo){
            $imageName = time().'.'.$request->photo->extension();
            //thumb
                $img = Image::make($request->photo->path());
                $img->resize(215, 215, function ($constraint) {

                    $constraint->aspectRatio();

                })->save(public_path('employee').'/thumb_'.$imageName);
            // 
            $request->photo->move(public_path('employee'), $imageName);
            $profiledata['photo'] = $imageName;
        }    
        $user->userprofile()->create($profiledata);
        $request->session()->flash('message', 'Employee add successful!');
        return redirect('/admin/employees');
    }
    
    public function editemployee($id){
        $data['page_name'] = $this->page_name;
        $data['user_detail'] = User::where('id',$id)
                                    ->with('userprofile')
                                    ->get()->first();
        
        //echo '<pre>';
        //print_r($data['user_detail']->toArray());die();
        return view('admin/employee_edit',$data);
    }
    public function updateemployee(Request $request){
        $this->validate($request,[
                'name' => 'required',
                'email' => 'required|unique:users,email,'.$request->eid,
                'phone' => 'required',
                'photo' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ],[
                'name.required' => 'The name field is required.',
                'email.required' => ' The Email field is required.',
                'email.unique' => ' The Email all ready exist.',
                'phone.required' => 'The phone field is required.',
                'photo.required'=>'Photo must be jpeg,png,jpg,gif,svg extention.'
            ]);
        $user = User::find($request->eid);
        $user->name = $request->name;
        $user->email = $request->email;
        $user->save();

        $profiledata['phone'] = $request->phone;
        $profiledata['address'] = $request->address;
        $profiledata['department'] = $request->department;
        if($request->photo){
            $imageName = time().'.'.$request->photo->extension();
            //thumb
                $img = Image::make($request->photo->path());
                $img->resize(215, 215, function ($constraint) {

                    $constraint->aspectRatio();

                })->save(public_path('employee').'/thumb_'.$imageName);
            //
            $request->photo->move(public_path('employee'), $imageName);
            $profiledata['photo'] = $imageName;
        }
        $user->userprofile()->update($profiledata);
        $request->session()->flash('message', 'Employee Updated successful!');
        return redirect('admin/employees');
    }
    public function deleteemployee1($id){
        User::where('id', $id)->delete();
        return redirect('admin/users');
    }
	public function deleteemployee(Request $request){
		User::where('id', $request->eid)->delete();
        $request->session()->flash('message', 'Employe delete successful!');
    }
    public function userStatusChange($status ,$id){
        $user = User::find($id);
        $user->status = $status;
        $user->save();
        return redirect('admin/users');
    }
    public function notification(){
        $data['page_name'] = 'Notification';
        $data['users'] = User::where('user_type','!=','admin')->get();
        
        //echo '<pre>';
        //print_r($data['users']->toArray());die();
        return view('admin/notification',$data);
    }
    public function saveNotification(Request $request){
        if($request->photographer){
            $users = User::select('device_token')->where('user_type','photographer')->get();
            foreach ($users as $key => $value) {
                echo $value->device_token;
                $pushData = array('title'=>$request->title,'message'=>$request->message,'device_token'=>$value->device_token,'status'=>'admin');
                $this->pushNotification($pushData);
            }
        }
        if($request->buyer){
            $users = User::select('device_token')->where('user_type','user')->get();
            foreach ($users as $key => $value) {
                echo $value->device_token;
                $pushData = array('title'=>$request->title,'message'=>$request->message,'device_token'=>$value->device_token,'status'=>'admin');
                $this->pushNotification($pushData);
            }
        }
        if($request->users){
           $users = User::select('device_token')->whereIn('id',$request->users)->get();
             foreach ($users as $key => $value) {
                //echo $value->device_token;
              $pushData = array('title'=>$request->title,'message'=>$request->message,'device_token'=>$value->device_token,'status'=>'admin');
                $this->pushNotification($pushData);

            }
        }
        $request->session()->flash('message', 'Notification send successful!');
        
        return redirect('admin/notification');
    }
    public function pushNotification($data){
        
       // define('API_ACCESS_KEY','AAAAcdd5IRk:APA91bEiJJOPeUmDzeGaZas_IHLVrA_K0J-ZdZRGiV6g3-xiISaI3uKHqZ92fOANwe-zG2Dr0jwn5V2XhNMB7juATT4MM2MiK-oW9J2raJvOI4-Hw4fEsfGlVLH5dymKwuO6uimQ3c69');
        
        $fcmUrl = 'https://fcm.googleapis.com/fcm/send';
        
        $token=$data['device_token'];

        $notification = [
            'title' =>$data['title'],
            'body' => $data['message'],
            //'icon' =>'myIcon', 
            //'sound' => 'mySound'
        ];
        $extraNotificationData = [
                  "click_action" => "FLUTTER_NOTIFICATION_CLICK",
                //  "news_id" => $data['news_id'],
                  "status" => $data['status'],
                ];
        //$tokenIds = Token::pluck('device_token');
        
        //echo '<pre>';
        //print_r($tokenIds->toArray());exit;
        
        if($token){
            $fcmNotification = [
                //'registration_ids' => $tokenIds->toArray(), //multple token array
                'to'        => $token, //single token
                'notification' => $notification,
                'data' => $extraNotificationData
            ];
            //print_r($fcmNotification);exit;

            $headers = [
                'Authorization: key=AAAAcdd5IRk:APA91bEiJJOPeUmDzeGaZas_IHLVrA_K0J-ZdZRGiV6g3-xiISaI3uKHqZ92fOANwe-zG2Dr0jwn5V2XhNMB7juATT4MM2MiK-oW9J2raJvOI4-Hw4fEsfGlVLH5dymKwuO6uimQ3c69',
                'Content-Type: application/json'
            ];


            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL,$fcmUrl);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fcmNotification));
            $result = curl_exec($ch);
            curl_close($ch);
            //echo $result;
            //die();
        }
    }
	 public function storeUser(Request $request)
    {
       
        $user_id = $request->user_id;
   if(!empty($user_id)){
        $UpdateDetails = User::where('id',$user_id)->first();
        $UpdateDetails->name = $request->name;
        $UpdateDetails->email = $request->email;
        $UpdateDetails->mobileno = $request->mobileno;
        $UpdateDetails->status = $request->status;
        $user_image = $request->hidden_user_image;
       
        if($request->user_image != '')
        {
            $user_image = time() . '.' . request()->user_image->getClientOriginalExtension();

            request()->user_image->move(public_path('upload'), $user_image);
        }
        $UpdateDetails->image = $user_image;
        $UpdateDetails->updated_at = date('Y-m-s h:i:s');
        $UpdateDetails->save();   
        return response()->json(['success'=>'User saved successfully!']);
   }
   else{
            $name = $request->input('name');
            $email = $request->input('email');
            $mobileno = $request->input('mobileno');
            $status = $request->input('status');
            $created_at = date('Y-m-d h:i:s');
            $user_image = time() . '.' . request()->user_image->getClientOriginalExtension();
            request()->user_image->move(public_path('upload'), $user_image);
            $data=array('name'=>$name,"email"=>$email,"mobileno"=>$mobileno,"status"=>$status,"image"=>$user_image,"created_at"=>$created_at);
          // print_r($data);die;
            DB::table('users')->insert($data);
			//$result=DB::table('user_lists')->insertGetId($data);
          return response()->json(['success'=>'User saved successfully!']);
   }
    /*   $company   =   UserList::updateOrCreate(
                        [
                        'id' => $user_id
                        ],
                        [
                        'name' => $request->name, 
                        'email' => $request->email,
                        'mobile' => $request->mobile,
                        'description' => $request->description,
                        'description' => $request->description
                        ]);    
                            
                return Response()->json($company); */

                         
      // return Response()->json($user);
	/*  $list= DB::table('users')->count();
		   $data=array();
		   foreach($list as $key=>$value)
		   {
			   $data['name']=$value->name;
			   $data['user_type']=$value->user_type;
			   $data['email']=$value->email;
			   $data['email_verified_at']=$value->email_verified_at;
			   $data['mobileno']=$value->mobileno;
			   $data['about']=$value->about;
			   DB::table('users')->insert($data);
		   }
		   */
    }
	
    public function editUser(Request $request)
    {   
        //print_r($request->input('id'));
       // $where = array('id' => $request->id);
       $id= $request->input('id');
       $user = User::find($id);
      // print_r($user);die;
     /* $status = array("1"=>"Active", "0"=>"Inactive");
      foreach($status as $key=>$val){
           $sel = "";
           if($key == $user["status"]){
            $sel = "selected";
	       }
          $user['option'] = '<option value="'.$key.'" " " '.$sel.' >'.$val.'</option>';
          
       }
       */
        return response()->json($user);
    }
}
