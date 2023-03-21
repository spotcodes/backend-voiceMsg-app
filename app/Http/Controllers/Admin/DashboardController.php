<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Photo;
use Auth;
Use DB;
class DashboardController extends Controller
{
	protected $page_name = 'Dashboard';
    public function dashboard(){
    	$page_name = $this->page_name;
        $users= User::all();
        $currentMonth = date('m');
        $current_users = DB::table("users")
                   ->whereRaw('MONTH(created_at) = ?',[$currentMonth])
                   ->get();
        $activeUsers = User::where('status','1')->get();
       return view('admin.dashboard',compact('users','current_users','activeUsers','page_name'));
       // return view('admin/dashboard',$data);
    }
    public  function logout(){
    	Auth::logout();
	  	return redirect('/admin');
    }
    public function changePassword(){
		$data['page_name'] = 'Change Password';
        $data['sub_page_name'] = 'Change Password';
    	
		return view('admin.changepassword',$data);
	}
	public function updatePassword(Request $request){
		$this->validate($request,[
                'password' => 'min:6|required_with:cpassword|same:cpassword',
				'cpassword' => 'min:6'
            ],[
                'password.required' => 'The password field is required.',
                'cpassword.required' => 'The confirm password field is required.',
            ]);

		$userDetail = User::where('user_type','admin_user')->get()->first();
		$userDetail->password = bcrypt($request->password);
		$userDetail->save();
        $request->session()->flash('message', 'News Updated successful!');
        return redirect('admin/dashboard');
		//echo '<pre>';
		//print_r($newsDetail->toArray());die();
    }
}
