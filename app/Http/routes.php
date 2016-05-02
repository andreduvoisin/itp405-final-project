<?php

/*
|--------------------------------------------------------------------------
| Routes File
|--------------------------------------------------------------------------
|
| Here is where you will register all of the routes in an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| This route group applies the "web" middleware group to every route
| it contains. The "web" middleware group is defined in your HTTP
| kernel and includes session state, CSRF protection, and more.
|
*/

use App\User;
use Illuminate\Http\Request;

Route::group(['middleware' => ['web']], function () {
    Route::group(['middleware' => ['member']], function() {
        Route::get('/battlenet/{bracket}', function ($bracket) {
            $battlenet = new \App\Services\API\BattleNet();
            $leaderboard = $battlenet->getLeaderboard($bracket);
            return view('battlenet', [
                'leaderboard' => $leaderboard,
                'bracket' => $bracket
            ]);
        });

        Route::get('/wow/character/{realm}/{characterName}', function($realm, $characterName) {
            $battlenet = new \App\Services\API\BattleNet();
            $character = $battlenet->getCharacter($realm, $characterName);
            return view('character', [
                'character' => $character
            ]);
        });
    });
    
    Route::get('signup', function() {
        return view('signup');
    });

    Route::post('signup', function(Request $request) {
        $validation = User::validate($request->all());

        if ($validation->passes())
        {
            $user = new User();
            $user->name = $request->input('name');
            $user->email = $request->input('email');
            $user->password = Hash::make($request->input('password'));
            $user->save();
            
            return redirect('/wow/character/tichondrius/Veilgrin');
        }

        return redirect('signup')
            ->withInput()
            ->withErrors($validation->errors());
    });
    
    Route::get('login', function() {
        return view('login');
    });

    Route::post('login', function(Request $request) {
        $attemptIsSuccessful = Auth::attempt([
            'email' => $request->input('email'),
            'password' => $request->input('password')
        ]);

        if ($attemptIsSuccessful)
        {
            return redirect('/wow/character/tichondrius/Veilgrin');
        }

        return redirect('login')
            ->with('failedAttempt', true);
    });

    Route::get('logout', function() {
        Auth::logout();
        return redirect('login');
    });
});
