<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use \App\Libraries\Meli;

class ItemsController extends Controller
{
    public function index()
    {
        $meli = new Meli(env('MELI_APP_ID', ''), env('MELI_SECRET_KEY', ''), session('access_token'), session('refresh_token'));

        $me = $meli->get('users/me', ['access_token' => session('access_token')]);

        $ids = $meli->get('/users/'.$me['body']->id.'/items/search', ['access_token' => session('access_token'), 'attributes' => 'results']);

        $items = $meli->get('/items', ['access_token' => session('access_token'), 'ids' => implode(',', $ids['body']->results)]);

        return $items['body'];
    }

    public function login(Request $request)
    {
        $meli = new Meli(env('MELI_APP_ID', ''), env('MELI_SECRET_KEY', ''), session('access_token'), session('refresh_token'));

        if($request->get('code') || session('access_token')) {
            // If code exist and session is empty
            if($_GET['code'] && !(session('access_token'))) {
                // If the code was in get parameter we authorize
                $user = $meli->authorize($_GET['code'], 'http://localhost/geek/public/login');

                // Now we create the sessions with the authenticated user
                session(['access_token' => $user['body']->access_token]);
                session(['expires_in' => time() + $user['body']->expires_in]);
                session(['refresh_token' => $user['body']->refresh_token]);
            } else {
                // We can check if the access token in invalid checking the time
                if(session('expires_in') < time()) {
                    try {
                        // Make the refresh proccess
                        $refresh = $meli->refreshAccessToken();
                        // Now we create the sessions with the new parameters
                        session(['access_token' => $refresh['body']->access_token]);
                        session(['expires_in' => time() + $refresh['body']->expires_in]);
                        session(['refresh_token' => $refresh['body']->refresh_token]);
                    } catch (\Exception $e) {
                        echo "Exception: ",  $e->getMessage(), "\n";
                    }
                }
            }

            return redirect('/');

        } else {
            echo '<a href="' . $meli->getAuthUrl('http://localhost/geek/public/login', Meli::$AUTH_URL['MLA']) . '">Login</a>';
        }
    }
}
