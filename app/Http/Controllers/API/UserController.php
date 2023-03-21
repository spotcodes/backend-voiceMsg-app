<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\DownloadInstallation;
use App\Models\UserContact;
use App\Models\Feedback;
use Auth;
use PDF;
use Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Password; 
use Illuminate\Support\Facades\Mail;
use App\Mail\SendMail;
use Image;
use Twilio\Rest\Client;
class UserController extends Controller
{
    public $successStatus = 200;
  
    
    public function updateProfile(Request $request){
        $validator = Validator::make($request->all(), [ 
            'name' => 'required',
            'mobileno' => 'required',
            'user_id' => 'required|exists:users,id',
            'status_text' => 'required'
        ]);
        if($validator->fails()) { 
            $returnData['success'] = false;
            $returnData['error'] = $validator->errors()->first();
            return response()->json($returnData, 401); 
        }
        
        $image_path='';
        if($request->hasFile('image')){
            $uniqueid=uniqid();
            
            $extension=$request->file('image')->getClientOriginalExtension();
            $filename=Carbon::now()->format('Ymd').'_'.$uniqueid.'.'.$extension;
            
            $request->file('image')->move('public/uploads/files/image/', $filename);
            $image_path='public/uploads/files/image/'.$filename;
        }
        
        $userDetail = User::where('id',$request->user_id)->where('user_type','api_user')->get()->first();
        $userDetail->name = $request->name;
        $userDetail->mobileno = $request->mobileno;
        $userDetail->about = $request->about;
        $userDetail->image = $image_path;
        $userDetail->device_token = $request->device_token;
        $userDetail->device_type = $request->device_type;
        $userDetail->status_text = @$request->status_text;
        $userDetail->save();

        $returnData['success'] = true;
        $returnData['message'] = 'Update Profile successfully';
        $returnData['payload'] = $userDetail;
        return response()->json($returnData, $this->successStatus);
    }
    public function userProfile(Request $request){
        $validator = Validator::make($request->all(), [ 
            'user_id' => 'required|exists:users,id'
        ]);
        if($validator->fails()) { 
            $returnData['success'] = false;
            $returnData['error'] = $validator->errors()->first();
            return response()->json($returnData, 401); 
        }
        $user = User::where('id',$request->user_id)
                    ->where('user_type','api_user')->where('status',0)
                    ->get()->first();
        
        
        $returnData['success'] = true;
        $returnData['payload'] = $user;
        return response()->json($returnData,$this->successStatus);
    }
    public function deleteAccount(Request  $request){
        $validator = Validator::make($request->all(), [ 
            'user_id' => 'required|exists:users,id', 
        ]);
        if($validator->fails()) {
            $returnData['success'] = false;
            $returnData['error'] = $validator->errors()->first();
            return response()->json($returnData, 401); 
        }
        /*$user_id = $request->user_id;
        User::where('id', $user_id)->where('user_type','api_user')->delete();*/
        
        $userDetail = User::where('id',$request->user_id)->where('user_type','api_user')->get();
        $userDetail->status = 2;
        $userDetail->save();
        
        $userContactDetail = UserContact::where('user_id',$request->user_id)->get()->first();
        $userContactDetail->status = 2;
        $userContactDetail->save();
        
        $returnData['success'] = true;
        $returnData['message'] = 'You are delete successfully';
        $returnData['payload'] = $userDetail;
        return response()->json($returnData,$this->successStatus);
    }
    
    public function sendFeedback(Request $request){
        $validator = Validator::make($request->all(), [ 
            'contact_id' => 'required',
            'title' => 'required',
            'message' => 'required',
            'user_id' => 'required|exists:users,id'
        ]);
        if($validator->fails()) { 
            $returnData['success'] = false;
            $returnData['error'] = $validator->errors()->first();
            return response()->json($returnData, 401); 
        }
        
        $input = $request->all();
        
        $input['user_id'] = $input['user_id'];
        $input['uc_id'] = $input['contact_id'];
        $input['title'] = $input['title'];
        $input['message'] = $input['message'];
        
        $feedback = Feedback::create($input);
        
        $returnData['success'] = true;
        $returnData['message'] = 'Send successfully';
        $returnData['status'] = 0;
        $returnData['payload'] = $feedback;
            
        return response()->json($returnData,$this->successStatus);
    }
   
    
    public function userLogout(Request $request){
        $validator = Validator::make($request->all(), [ 
            'user_id' => 'required|exists:users,id',
            'mobileno' => 'required|exists:users,mobileno'
        ]);
        if($validator->fails()) { 
            $returnData['success'] = false;
            $returnData['error'] = $validator->errors()->first();
            return response()->json($returnData, 401); 
        }
        
        $userDetail = User::where('id',$request->user_id)
                    ->where('user_type','api_user')->where('mobileno',$request->mobileno)->count();
        
        $returnData['success'] = false;
        $returnData['message'] = 'Mobile number is not exits.';
            
        if($userDetail >= 1){
            $returnData['success'] = true;
            $returnData['message'] = 'Logout successfully';
        }
        return response()->json($returnData,$this->successStatus);
    }
    public function pushNotification(Request $request){
        
     // print_r($request->all());die;
        $fcmUrl = 'https://fcm.googleapis.com/fcm/send';
        
        $token=$request->device_token;

        $notification = [
            'title' =>$request->title,
            'body' => $request->message
           
        ];
       
       
        
        if($token){
            $fcmNotification = [
                //'registration_ids' => $tokenIds->toArray(), //multple token array
                'to'        => $token, //single token
                'notification' => $notification
                
            ];
            //print_r($fcmNotification);exit;

            $headers = [
                'Authorization: key=AAAAYPu4HZc:APA91bH3Yhst1zkB8t3W4IMxznbVbR4V7asW7O2w27muzhTnCE9zF2JTlR3iWXB0fRB_GkWvFPDap--EOE5x9Ppwfm11M9G5WH85325qjoIex-U6NdUub0fdAQzlqU7FRMjPezDRpO9h',
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
            echo $result;
            die();
        }
    }
	public function accountDelete(Request  $request){
		
        $validator = Validator::make($request->all(), [ 
            'id' => 'required|exists:users,id', 
        ]);
        if($validator->fails()) {
            $returnData['success'] = false;
            $returnData['error'] = $validator->errors()->first();
            return response()->json($returnData, 401); 
        }
		$user_id = $request->id;
        $id = $request->id;
        User::where('id', $id)->delete();
        $userContactDetail = UserContact::where('user_id',$user_id)->delete();
      
        
        $returnData['success'] = true;
        $returnData['message'] = 'You are delete successfully';
       // $returnData['payload'] = $userDetail;
        return response()->json($returnData,$this->successStatus);
    }		
}
