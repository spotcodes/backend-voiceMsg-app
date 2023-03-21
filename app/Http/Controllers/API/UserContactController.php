<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserContact;
use App\Models\ChatHistory;
use Auth;
use Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Password; 
use Illuminate\Support\Facades\Mail;
use Illuminate\Database\Query\Builder;
use Illuminate\Validation\Rule;
use App\Mail\SendMail;
use Image;
use App\File;

class UserContactController extends Controller
{
    public $successStatus = 200;
    
    
    
   
	
	 public function userContactList(Request $request){
        

        $validator = Validator::make($request->all(), [ 

            'user_id' => 'required',

        ]);

        if($validator->fails()) { 

            $returnData['success'] = false;

            $returnData['error'] = $validator->errors()->first();

            return response()->json($returnData, 401); 

        }

        $user = UserContact::where('user_id',$request->user_id)

                    ->where('status', '<>', 2)

                    ->orderBy('name', 'ASC')

                    ->get();

                   
                   // print_r($user);die;
                    $i=0;
                    foreach($user as $key=>$user1)
                    {
            
                     $returnData[$i]['uc_id'] = $user1['uc_id'];
                     $returnData[$i]['user_id'] = $user1['user_id'];
                     $returnData[$i]['name'] = $user1['name'];
                     $returnData[$i]['device_token'] = $user1['device_token'];
                     $returnData[$i]['device_type'] = $user1['device_type'];
                     $returnData[$i]['app_id'] = $user1['app_id'];
                     $returnData[$i]['image'] = $user1['image'];
                     $returnData[$i]['mobileno'] = $user1['mobileno'];
                     $returnData[$i]['reason'] = $user1['reason'];
                     $returnData[$i]['status'] = $user1['status'];
                     $returnData[$i]['created_at'] = $user1['created_at'];
                     $returnData[$i]['updated_at'] = $user1['updated_at'];
                     $i++;
                    }
                   //print_r($returnData);die;
                    $returnData1['success'] = true;
                    $returnData1['payload'] = $returnData;
                    return response()->json($returnData1,$this->successStatus);
    }
    
    public function updateContactStatus(Request  $request){
        $validator = Validator::make($request->all(), [ 
            'user_id' => 'required',
            'status' => 'required',
            'contact_id' => 'required|exists:users_contact,uc_id', 
        ]);
        if($validator->fails()) {
            $returnData['success'] = false;
            $returnData['error'] = $validator->errors()->first();
            return response()->json($returnData, 401); 
        }
        
        $userContactDetail = UserContact::where('uc_id',$request->contact_id)->where('user_id',$request->user_id)->get()->first();
        
        //$quries = DB::getQueryLog();
        //dd($quries);
        
        $userContactDetail->reason = $request->reason;
        $userContactDetail->status = $request->status;
        $userContactDetail->save();
        
        $returnData['success'] = true;
        $returnData['message'] = 'Update successfully';
        $returnData['payload'] = $userContactDetail;
        return response()->json($returnData,$this->successStatus);
    }
    
   
    
    public function sendVoiceMessage(Request $request){
        $validator = Validator::make($request->all(), [ 
            'contact_id' => 'required',
            'document_file' =>'nullable|file|mimes:audio/mpeg,mpga,mp3',
            'user_id' => 'required|exists:users,id'
        ]);
        if($validator->fails()) { 
            $returnData['success'] = false;
            $returnData['error'] = $validator->errors()->first();
            return response()->json($returnData, 401); 
        }
        
        $audio_path='';
        if($request->hasFile('document_file')){
            $uniqueid=uniqid();
            
            $extension=$request->file('document_file')->getClientOriginalExtension();
            $filename=Carbon::now()->format('Ymd').'_'.$uniqueid.'.'.$extension;
            
            $request->file('document_file')->move('public/uploads/files/audio/', $filename);
            $audio_path=asset('public/uploads/files/audio/'.$filename);
        }
        
        $ChatHistory = array();
        $input = $request->all();
        
        $contactIdArr = explode(',',$request->contact_id);
        if(!empty($contactIdArr)){
            foreach($contactIdArr as $contact) {
                
                $chatData = array();
                $chatData['user_id'] = $input['user_id'];
                $chatData['uc_id'] = $contact;
                $chatData['document_file'] = $audio_path;
                $chatData['message'] = @$input['message'];
                $chatData['status'] = @$input['status'];
        
                $ChatHistory = ChatHistory::create($chatData);
            }    
        }
        
        $userChat = ChatHistory::where('user_id',$input['user_id'])
                    //->orderBy('uc_id', 'ASC')->orderBy('created_at', 'ASC')
					 ->orderBy('chat_history_id', 'DESC')
                    ->get();
        
        $returnData['success'] = true;
        $returnData['message'] = 'Send successfully';
        $returnData['payload'] = $userChat;
            
        return response()->json($returnData,$this->successStatus);
    }
    
    public function chatHistoryList(Request $request){
        $validator = Validator::make($request->all(), [ 
            'user_id' => 'required',
        ]);
        if($validator->fails()) { 
            $returnData['success'] = false;
            $returnData['error'] = $validator->errors()->first();
            return response()->json($returnData, 401); 
        }
        
        $query = ChatHistory::where('user_id',$request->user_id)
                    ->orderBy('uc_id', 'ASC')->orderBy('created_at', 'ASC');
                    
        if (isset($request->status)) {
            $query->where('status', $request->status);
        }
        
        $results_data = $query->get();
        
        $returnData['success'] = true;
        $returnData['payload'] = $results_data;
        return response()->json($returnData,$this->successStatus);
    }
    
    public function deleteUserFromChatHistory(Request  $request){
        $validator = Validator::make($request->all(), [ 
            'user_id' => 'required|exists:users,id',
            'status' => 'required',
            'contact_id' => 'required', 
        ]);
        if($validator->fails()) {
            $returnData['success'] = false;
            $returnData['error'] = $validator->errors()->first();
            return response()->json($returnData, 401); 
        }
        
        ChatHistory::where('user_id',$request->user_id)
                    ->where('uc_id',$request->contact_id)
                    ->update(array(
                         'status'=>$request->status,
                        ));

        $getDetails = ChatHistory::where('uc_id',$request->contact_id)->where('user_id',$request->user_id)->get();
        
        //$quries = DB::getQueryLog();
        //dd($quries);
        
        $returnData['success'] = true;
        $returnData['message'] = 'Update successfully';
        $returnData['payload'] = $getDetails;
        return response()->json($returnData,$this->successStatus);
    }
    
    public function updateChatHistoryStatus(Request  $request){
        $validator = Validator::make($request->all(), [ 
            'user_id' => 'required|exists:users,id',
            'chat_history_id' => 'required|exists:chat_history,chat_history_id',
            'status' => 'required',
        ]);
        if($validator->fails()) {
            $returnData['success'] = false;
            $returnData['error'] = $validator->errors()->first();
            return response()->json($returnData, 401); 
        }
        
        ChatHistory::where('chat_history_id',$request->chat_history_id)
                    ->where('user_id',$request->user_id)
                    ->update(array(
                         'status'=>$request->status,
                        ));

        $getDetails = ChatHistory::where('chat_history_id',$request->chat_history_id)->where('user_id',$request->user_id)->get();
        
        //$quries = DB::getQueryLog();
        //dd($quries);
        
        $returnData['success'] = true;
        $returnData['message'] = 'Update successfully';
        $returnData['payload'] = $getDetails;
        return response()->json($returnData,$this->successStatus);
    }
    
    
    
    
}
