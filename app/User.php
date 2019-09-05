<?php

/*
  Controller for User related functionality
  @author: Jayesh Prajapati
  @package: User model
 */

namespace App;

use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable {

    use HasApiTokens,
        Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'phone', 'profile_pic', 'password', 'git_password'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /* This function to save user data in database
     * @param: input request
     * @return: object
     */

    public function save_user($input = '', $id = '') {
        if (!empty($input)) {
            if (!empty($id)) {
                //update user recod
                return DB::table('users')->where('id', $id)->update($input);
            } else {
                //insert user recod
                return User::create($input);
            }
        }
    }

}
