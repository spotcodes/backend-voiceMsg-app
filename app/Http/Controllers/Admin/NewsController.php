<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\News;
use App\Models\User;
use App\Models\Token;
use App\Models\Notification;
use Auth;
use Image;
class NewsController extends Controller
{
    protected $page_name = 'News';
    public function news($field = null,$value = null){
        $data['page_name'] = $this->page_name;
        $data['sub_page_name'] = 'All News';
    	
       $data['news'] =  News:: 	orderBy('id', 'DESC')->withCount('comments')->withCount('likes')->get();

       //echo '<pre>';
       //print_r($data);die();
        return view('admin/news_list',$data);
    }
    public function addnews(){
    	$data['page_name'] = $this->page_name;
    	return view('admin/add_news',$data);
    }
    public function savenews(Request  $request){
    	$this->validate($request,[
                'title' => 'required',
                'description' => 'required',
            ],[
                'title.required' => 'The Title field is required.',
                'title.description' => 'The description field is required.',
            ]);
        $input = $request->all();
        $data = new News();
        $data->title = $input['title'];
		$data->description = $input['description']; 
		$data->news_type = $input['news_type'];
		$data->news_data ='';
        if($data->news_type=='image'){
            
            if($request->news_image){
            $imageName = time().'.'.$request->news_image->extension();
            $request->news_image->move(public_path('news'), $imageName);
            $data->news_data = asset('news').'/'.$imageName;
        }
        }else{
            $data->news_data = $input['news_video'];
            if($input['news_video']){
                $index = strpos($input['news_video'], "?v=");
 
                // if not found, search with an &v
                if($index === FALSE){
                   return redirect()->back() ->withInput()->withErrors(['news_video' => 'Your yutube url is wrong!']);
                }

                $video_id = explode("?v=", $input['news_video']);
                $video_id = $video_id[1];
                $thumbnail="http://img.youtube.com/vi/".$video_id."/mqdefault.jpg";
                $data->video_image = $thumbnail;
            }
        }

        //echo '<pre>';
        //print_r($data->toArray());die();

        $data->save();

        $Ndata = new Notification();
        $Ndata->title = $input['title'];
        $Ndata->description = $input['description'];
        $Ndata->type = 'news';
        $Ndata->news_id = $data->id;
        $Ndata->save();

        $pushData = array('title'=>$data->title,'description'=>$input['description'],'news_id'=>$data->id);
        $this->pushNotification($pushData);

    	$request->session()->flash('message', 'News add successful!');
        return redirect('admin/addnews');
    }
    public function editnews($id){
        $data['page_name'] = $this->page_name;
        $data['news_detail'] = News::find($id);
        return view('admin/news_edit',$data);
    }
    public function updatenews(Request $request){
        $this->validate($request,[
                'title' => 'required',
                'description' => 'required',
            ],[
                'title.required' => 'The Title field is required.',
                'title.description' => 'The description field is required.',
            ]);
        $newsDetail = News::find($request->newsid);
        $newsDetail->title = $request->title;
        $newsDetail->description = $request->description;
        $newsDetail->news_type = $request->news_type;
        if($newsDetail->news_type=='image'){
            if($request->news_image){
                $imageName = time().'.'.$request->news_image->extension();
                $request->news_image->move(public_path('news'), $imageName);
                $newsDetail->news_data = asset('news').'/'.$imageName;
            }
        }else{
            $newsDetail->news_data = $request->news_video;
            if($request->news_video){
                $index = strpos($request->news_video, "?v=");
 
                // if not found, search with an &v
                if($index === FALSE){
                   return redirect()->back() ->withInput()->withErrors(['news_video' => 'Your yutube url is wrong!']);
                }
                $video_id = explode("?v=", $request->news_video);
                $video_id = $video_id[1];
                $thumbnail="http://img.youtube.com/vi/".$video_id."/mqdefault.jpg";
                $newsDetail->video_image = $thumbnail;
            }
        }
        $newsDetail->save();
        $request->session()->flash('message', 'News Updated successful!');
        return redirect('admin/news');
    }
    public function deletenews(Request $request){
        News::where('id', $request->news_id)->delete();
        $request->session()->flash('message', 'News delete successful!');
    }

    public function pushNotification($data){
        
        //define('API_ACCESS_KEY','AAAAqUKQKrM:APA91bHFCmdQl0aObMUHp8E_rUV3OYv31bsmDT8XQzr3c5aJ0Wavy5wwxYNOi1lX0PLREMe57Hdpcp8QIWir6Lss2FU0uRPzr-qyFRm3yJn7GXSTK9f1G9dln6I4QgH4pNJdvvbG1Mle');
        
        $fcmUrl = 'https://fcm.googleapis.com/fcm/send';
        
        $token='cB_s04skSXukUCozIhKon0:APA91bF4yLsy9M5psUj5q8imAGvaZKqd1dyJ60XIhBoXEZl2lnsSXPTsKaAtJ1TuR4R5piz_-5Om1P18eOqcfdLvpqOHRTKTvr0s6zpkN4PYlcPUQyGJLA6uAzjLBH1GvAnBEjbBwd2L';

    $notification = [
            'title' =>$data['title'],
            'body' => $data['description'],
            //'icon' =>'myIcon', 
            //'sound' => 'mySound'
        ];
        $extraNotificationData = [
                  "click_action" => "FLUTTER_NOTIFICATION_CLICK",
                  "news_id" => $data['news_id'],
                ];
        $tokenIds = Token::pluck('device_token');
        
        //echo '<pre>';
        //print_r($tokenIds->toArray());exit;
        if(sizeof($tokenIds)>0){
            $fcmNotification = [
                'registration_ids' => $tokenIds->toArray(), //multple token array
                //'to'        => $token, //single token
                'notification' => $notification,
                'data' => $extraNotificationData
            ];
            //print_r($fcmNotification);exit;

            $headers = [
                'Authorization: key=' . API_ACCESS_KEY,
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
}
