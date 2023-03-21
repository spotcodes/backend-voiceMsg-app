<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\User;
use App\Models\Token;
use App\Models\Notification;
use Auth;
use Image;
class CategoryController extends Controller
{
    protected $page_name = 'Category';
    public function category($field = null,$value = null){
        $data['page_name'] = $this->page_name;
        $data['sub_page_name'] = 'All Category';
    	
       $data['category'] =  Category:: 	orderBy('id', 'DESC')->Paginate(10);

       //echo '<pre>';
       //print_r($data['category']->toarray());die();
        return view('admin/category_list',$data);
    }
    public function addcategory(){
    	$data['page_name'] = $this->page_name;
    	return view('admin/category_add',$data);
    }
    public function savecategory(Request  $request){
    	$this->validate($request,[
                'title' => 'required',
            ],[
                'title.required' => 'The Title field is required.',
            ]);
        $input = $request->all();
        $data = new Category();
        $data->name = $input['title'];
		$data->image ='';
            
        if($request->image){
            $imageName = time().'.'.$request->image->extension();
            $request->image->move(public_path('category'), $imageName);
            $data->image = asset('category').'/'.$imageName;
        }

        
        $data->save();

        
        
    	$request->session()->flash('message', 'Category add successful!');
        return redirect('admin/addcategory');
    }
    public function editcategory($id){
        $data['page_name'] = $this->page_name;
        $data['category_detail'] = Category::find($id);
        return view('admin/category_edit',$data);
    }
    public function updatecategory(Request $request){
        $this->validate($request,[
                'title' => 'required',
            ],[
                'title.required' => 'The Title field is required.',
            ]);
        $categoryDetail = Category::find($request->categoryid);
        $categoryDetail->name = $request->title;
            if($request->image){
                $imageName = time().'.'.$request->image->extension();
                $request->image->move(public_path('category'), $imageName);
                $categoryDetail->image = asset('category').'/'.$imageName;
            }
        
        $categoryDetail->save();
        $request->session()->flash('message', 'Category Updated successful!');
        return redirect('admin/category');
    }
    public function deletecategory(Request $request){
        Category::where('id', $request->category_id)->delete();
        $request->session()->flash('message', 'Category delete successful!');
    }
}
