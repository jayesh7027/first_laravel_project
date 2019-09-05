<?php

/*
  Controller for User Github related functionality
  @author: Jayesh Prajapati
  @package: GithubController
 */

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\App;
use Crypt;

class GithubController extends Controller {

    public $successStatus = 200;
    private $client;
    private $username;

    public function __construct(\Github\Client $client) {
        $this->client = $client;
        $this->username = '';
    }

    /* This function to get all information of the current login user who with github
     * @param: N/A
     * @return: if success then response other wise error
     */

    public function index() {
        try {
            //call github authentication
            $this->authenticate();

            $user = $this->client->api('current_user')->show();
            return response()->json(['success' => $user], $this->successStatus);
        } catch (\RuntimeException $e) {
            $this->handleAPIException($e);
        }
    }

    /* This function to get all repositories from github who current login
     * @param: N/A
     * @return: if success then response other wise error
     */

    public function repositories() {
        try {
            //call github authentication
            $this->authenticate();
            //get all repos
            $repos = $this->client->api('current_user')->repositories();
            $reposArr = array();
            foreach ($repos as $key => $repo) {
                //get all repos contents (means all files)
                $result = $this->client->api('repo')->contents()->show($this->username, $repo['name'], '.');
                foreach ($result as $k => $item) {
                    $reposArr['repos'][$key]['repo_name'] = $repo['name'];
                    //currently i am only display files not directory
                    $type = 'Directory';
                    if (isset($item['type']) && $item['type'] == 'file') {
                        $type = 'File';
                    }
                    $reposArr['repos'][$key]['content_data'][$k]['name'] = $item['name'];
                    $reposArr['repos'][$key]['content_data'][$k]['path'] = $item['path'];
                    $reposArr['repos'][$key]['content_data'][$k]['type'] = $type;
                }
            }
            return response()->json(['success' => $reposArr], $this->successStatus);
        } catch (\RuntimeException $e) {
            $this->handleAPIException($e);
        }
    }

    /* This function to get edit particular repo file(not a directory) from github who current login
     * @param: N/A
     * @return: if success then response other wise error
     */

    public function edit() {
        $repo = Input::get('repo');
        $path = Input::get('path');
        try {
            //call github authentication
            $this->authenticate();

            $file = $this->client->api('repo')->contents()->show($this->username, $repo, $path);
            $content = base64_decode($file['content']);
            $commitMessage = "Updated file " . $file['name'];

            $editArr = [
                'commitMessage' => $commitMessage,
                'file' => $file,
                'path' => $path,
                'repo' => $repo,
                'content' => $content,
            ];
            return response()->json(['success' => $editArr], $this->successStatus);
        } catch (\RuntimeException $e) {
            $this->handleAPIException($e);
        }
    }

    /* This function to get all commit of the particular repo
     * @param: N/A
     * @return: if success then response other wise error
     */

    public function commits() {
        $repo = Input::get('repo');
        $path = Input::get('path');
        try {
            //call github authentication
            $this->authenticate();

            $commits = $this->client->api('repo')->commits()->all($this->username, $repo, ['path' => $path]);
            return response()->json(['success' => $commits], $this->successStatus);
        } catch (\RuntimeException $e) {
            $this->handleAPIException($e);
        }
    }

    /* This function to handle API error include code and message
     * @param: $e
     * @return: error code with errormessage
     */

    public function handleAPIException($e) {
        dd($e->getCode() . ' - ' . $e->getMessage());
    }

    /* This function to authenticate github credentional
     * @param: N/A
     * @return: N/A
     */

    public function authenticate() {
        $email = Auth::user()->email;
        $password = Crypt::decrypt(Auth::user()->git_password);
        $this->username = Auth::user()->name;
        $this->client->authenticate($email, $password, $this->client::AUTH_HTTP_PASSWORD);
    }

}
