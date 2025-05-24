<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Notifications\WelcomeNotification;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'rut' => ['required', 'string', 'max:20', 'unique:users'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => [
                'required',
                'string',
                'min:8',
                'max:100',
                'confirmed',
                "regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#\$%\^&\*()_+\-=\[\]{};':\\\"\\|,.<>\/?]).{8,}$/"
            ],
            'phone' => [
                'required',
                'string',
                'max:30',
                'not_regex:/^0+$/',
                'not_regex:/^(\d)\1+$/',
            ],
            'birthdate' => [
                'required',
                'date',
                'before:today',
            ],
            'career' => ['required', 'string', 'max:100'],
            'brand' => ['required', 'string', 'max:50'],
            'model' => ['required', 'string', 'max:50'],
            'color' => ['required', 'string', 'max:30'],
        ], [
            'rut.unique' => 'El RUT ya está registrado.',
            'email.unique' => 'El correo ya está registrado.',
            'password.regex' => 'La contraseña debe tener al menos 8 caracteres, una mayúscula, una minúscula, un número y un símbolo.',
            'phone.not_regex' => 'El teléfono no puede ser trivial (todos ceros o todos iguales).',
            'birthdate.before' => 'La fecha de nacimiento no puede ser futura.'
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'visitante',
            'rut' => $data['rut'],
        ]);
        $user->profile()->create([
            'phone' => $data['phone'],
            'birth_date' => $data['birthdate'],
            'career' => $data['career'],
        ]);
        $user->bikes()->create([
            'brand' => $data['brand'],
            'model' => $data['model'],
            'color' => $data['color'],
        ]);
        // Enviar correo de bienvenida
        $user->notify(new WelcomeNotification());
        return $user;
    }
}
