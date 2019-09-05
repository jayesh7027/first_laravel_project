<?php

/*
  Controller for User related functionality
  @author: Jayesh Prajapati
  @package: UserController
 */

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Support\Facades\Auth;
use Validator;
use Illuminate\Support\Facades\DB;
use Crypt;

class UserController extends Controller {

    public $successStatus = 200;

    /* This function to login api 
     * @param" N/A
     * @return return json response
     */

    public function login() {
        if (Auth::attempt(['email' => request('email'), 'password' => request('password')])) {
            $user = Auth::user();
            $success['token'] = $user->createToken('MyApp')->accessToken;
            return response()->json(['success' => $success], $this->successStatus);
        } else {
            return response()->json(['error' => 'Unauthorised'], 401);
        }
    }

    /* This function to Register api 
     * @param: $request, $user
     * @return return json response
     */

    public function register(Request $request, User $user) {
        //check validation
        $validator = Validator::make($request->all(), [
                    'name' => 'required',
                    'email' => 'required|email|unique:users',
                    'password' => 'required',
                    'phone' => 'nullable|regex:/^([0-9\s\-\+\(\)]*)$/|digits_between:10,10',
                    'c_password' => 'required|same:password',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }
        //get all request by post
        $input = $request->all();
        //for git password using to auth github
        $input['git_password'] = Crypt::encrypt($input['password']);
        $input['password'] = bcrypt($input['password']);
        //upload profile picture
        if ($request->hasFile('profile_pic')) {
            $allowedfileExtension = ['pdf', 'jpg', 'png'];
            $file = $request->file('profile_pic');
            $filename = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $check = in_array($extension, $allowedfileExtension);
            if ($check) {
                $input['profile_pic'] = $filename;
            }
        }
        //insert user record while register
        $response = $user->save_user($input);
        //create token
        $success['token'] = $response->createToken('MyApp')->accessToken;
        $success['name'] = $response->name;
        return response()->json(['success' => $success], $this->successStatus);
    }

    /* This function to update api
     * @param: $request, $user
     * @return return json response
     */

    public function update(Request $request, User $user) {
        //check validation
        $validator = Validator::make($request->all(), [
                    'name' => 'required',
                    'phone' => 'nullable|regex:/^([0-9\s\-\+\(\)]*)$/|digits_between:10,10',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }
        //get user id from auth
        $id = Auth::user()->id;
        //get all request by post
        $input = $request->all();
        //upload profile picture
        if ($request->hasFile('profile_pic')) {
            $allowedfileExtension = ['pdf', 'jpg', 'png'];
            $file = $request->file('profile_pic');
            $filename = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $check = in_array($extension, $allowedfileExtension);
            if ($check) {
                $input['profile_pic'] = $filename;
            }
        }
        //update user profile data
        $response = $user->save_user($input, $id);
        if ($response == 1) {
            $msg = 'Your Profile is updated successfully';
        } else {
            $msg = 'Nothing to update';
        }
        return response()->json(['success' => $msg], $this->successStatus);
    }

}
