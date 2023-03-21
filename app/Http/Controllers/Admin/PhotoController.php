<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Photo;
use App\Models\User;
use App\Models\Comment;
use App\Models\Token;
use App\Models\Notification;
use Auth;
use Image;
class PhotoController extends Controller
{
    protected $page_name = 'Post';
    public function Photo($field = null,$value = null){
        $data['page_name'] = $this->page_name;
        $data['sub_page_name'] = 'All Post';
    	
       $data['photo'] =  Photo:: 	orderBy('id', 'DESC')->withCount('comments')->withCount('likes')->with('category')->with('user')->Paginate(10);
       //echo '<pre>';
       //print_r($data['photo']->toarray());die();
       return view('admin/photo_list',$data);
    }
    
    
    public function viewphoto($id){
        $data['page_name'] = $this->page_name;
        $data['photo_detail'] = Photo::where('id',$id)->with(['comments' => function($q){
                    $q->select('description','photo_id','user_id','created_at','id');
                    $q->orderBy('id', 'desc');
                    $q->with(['user' => function($q){
                        $q->select('id','user_name','image');
                        
                        }]);
                }])->get()->first();
       // echo '<pre>';
       // print_r($data['photo_detail']->toArray());die();
        return view('admin/photo_view',$data);
    }
    public function deleteComment(Request $request){
      Comment::where('id', $request->c_id)->delete();
        $request->session()->flash('message', 'Comment delete successful!');
    }
    
    public function deletephoto(Request $request){
        Photo::where('id', $request->photo_id)->delete();
        $request->session()->flash('message', 'Post delete successful!');
    }
}
