<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Validator;
class AdminController extends Controller
{
	public function __construct()
	{
		$this->middleware(function ($request, $next) {
	        if (Auth::check()) {
	        	return redirect('/admin/dashboard');
	        }
			else{
				return $next($request);
			}
		});
	    
	}
	public function index(){
		return view('admin.index');
    }
    public function login(Request $request){
    	// validate the info, create rules for the inputs
		$rules = array(
    		'email'    => 'required|email', // make sure the email is an actual email
    		'password' => 'required|min:3' // password can only be alphanumeric and has to be greater than 3 characters
		);

		// run the validation rules on the inputs from the form
		$validator = Validator::make($request->all(), $rules);

		// if the validator fails, redirect back to the form
		if ($validator->fails()) {
		    return redirect('/admin')
		        ->withErrors($validator) // send back all errors to the login form
		        ->withInput($request->except('password')); // send back the input (not the password) so that we can repopulate the form
		} else {
			// create our user data for the authentication
		    $userdata = array(
		        'email'     => $request->input('email'),
		        'password'  => $request->input('password')
		    );
		    if (Auth::attempt($userdata)) {
		    	return redirect('/admin/dashboard');
		    } else {
		    	$validator->getMessageBag()->add('credential', 'Email or Password wrong!');   
		    	return redirect('/admin')->withErrors($validator)
		        ->withInput($request->except('password'));
		    }

		}

	}
	public function logininsert(Request $request){
    	
		$id=1;
		
		$password=$request->post('password');
		//$sr = DB::table('users') ->where('id', $id) ->limit(1) ->update( [ 'password' => $password]);
		$UpdateDetails = User::where('id',$id)->first();
        $UpdateDetails->password =  Hash::make($password);
        $UpdateDetails->save();
		dd($UpdateDetails);

	}
	
}	
