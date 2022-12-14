<?php

namespace App\Http\Controllers;

use App\Mail\MyTestMail;
use App\Models\UserVerify;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class SampleController extends Controller
{
    function index()
    {
        return view('login');
    }

    function registration()
    {
        return view('registration');
    }

    function validate_registration(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'email' => 'required|email|unique:users',
            'first_name' => 'required',
            'last_name' => 'required',
            'phone' => 'required|min:9',
            'password' => 'required|min:6',
            'confirm_password' => 'required|min:6',
            'country' => 'required',
            'state' => 'required',
            'city' => 'required'
        ]);
        $data = $request->all();
        $user = User::create([
            'username' => strip_tags($data['username']),
            'email' => $data['email'],
            'first_name' => strip_tags($data['first_name']),
            'last_name' => strip_tags($data['last_name']),
            'phone' => strip_tags($data['phone']),
            'password' => Hash::make($data['password']),
            'country_id' => $data['country'],
            'state_id' => $data['state'],
            'city_id' => $data['city']
        ]);

        if ($user)
        {
            $details = [
                'title' => 'Mail from LaravelSite.com',
                'body' => 'Hello, ' . $data['username'] . '! This is testing mail!'
            ];

            $token = Str::random(64);
            UserVerify::create([
                'user_id' => $user->id,
                'token' => $token
            ]);

            Mail::to($data['email'])->send(new MyTestMail($details));
            Mail::send('emails.emailVerificationEmail', ['token' => $token], function($message) use($request){
                $message->to($request->email);
                $message->subject('Email Verification Mail');
            });
        }

        return redirect('login')->with('success', __('dashboard.completedRegistration'));
    }

    function validate_login(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'password' => 'required'
        ]);

        $credentials = $request->only('email', 'password');
        if (Auth::attempt($credentials) && Auth::user()->is_email_verified == 1)
        {
            if (Auth::user()->isAdmin()) {
                return redirect()->intended('dashboard')->with('success', __('dashboard.loggedAdmin'));
            } else {
                return redirect()->intended('dashboard')->with('success', __('dashboard.loggedSuccessfully'));
            }
        }

        return redirect('login')->with('success', __('dashboard.dataNotValid'));
    }

    function dashboard()
    {
        if (Auth::check())
        {
            return view('dashboard');
        }

        return redirect('login')->with('success', __('dashboard.notAllowed'));
    }

    function logout()
    {
        Session::flush();

        Auth::logout();

        return redirect('login');
    }

    function getAuthor($id)
    {
        return User::where('id', $id)->username;
    }

    function forgotPassword()
    {
        return view('email');
    }

    public function verifyAccount($token)
    {
        $verifyUser = UserVerify::where('token', $token)->first();

        $message = __('dashboard.cannotBeIdentify');

        if(!is_null($verifyUser) ){
            $user = $verifyUser->user;

            if(!$user->is_email_verified) {
                $verifyUser->user->is_email_verified = 1;
                $verifyUser->user->save();
                $message = __('dashboard.verifiedEmail');
            } else {
                $message = __('dashboard.alreadyVerifiedEmail');
            }
        }

        return redirect()->route('login')->with('message', $message);
    }

    public function cabinet()
    {
        return view('user.cabinet');
    }

    private function storeImage(Request $request)
    {
        if ($request->file != 'undefined') {
            $newImageName = uniqid() . '-userphoto' . '.' . $request->file->extension();
            return $request->file->move('images/', $newImageName);
        }
        else {
            return null;
        }
    }

    public function addPhoto(Request $request)
    {
        $request->validate([
            'file' => 'nullable|max:2048|'
        ]);
        $data = $request->all();
        User::where('id', '=', Auth::user()->id)->update([
            'image' => $this->storeImage($request) ?? null,
        ]);
        return response()->json(['success' => 'Photo uploaded successfully']);
    }

    public function changeEmail(Request $request) {
        $data = $request->all();

        if ($data['oldEmail'] === Auth::user()->email) {

            User::where('id', '=', Auth::user()->id)->update([
                'email' => $data['newEmail'],
            ]);
            return response()->json(['success' => 'Email changed successfully ']);
        }
        else {
            return response()->json(['success' => 'Something went wrong']);
        }
    }

    public function changeName(Request $request) {
        $data = $request->all();

        if ($data['oldFirstName'] === Auth::user()->first_name && $data['oldLastName'] === Auth::user()->last_name) {

            User::where('id', '=', Auth::user()->id)->update([
                'first_name' => $data['newFirstName'],
                'last_name' => $data['newLastName'],
            ]);
            return response()->json(['success' => 'Name changed successfully ']);
        }
        else {
            return response()->json(['success' => 'Something went wrong']);
        }
    }

}
