<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Module;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function home(){
        $user = Auth::user(); 
        $modules = $user->modules;
        return view('index', compact('user', 'modules'));
    }
    public function index()
    {
        $users = User::all();

        return view('users.index', compact('users'));
    }

    public function show($id)
    {
        $user = User::findOrFail($id);
        return view('users.details_user', compact('user'));
    }

    public function store(Request $request)
{

    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'role' => 'required|string|in:admin,editor,user',
    ]);

    $username = explode('@', $request->email)[0];

    User::create([
        'name' => $request->name,
        'email' => $request->email,
        'username' => $username,
        'role' => $request->role,
        'password' => Hash::make('Mudar@123'),
    ]);

    return redirect()->route('users.index')->with('success', 'Usuário criado com sucesso!');
}


    public function edit($id)
    {
        $user = User::findOrFail($id);
        $modules = Module::all();
        return view('users.edit_user', compact('user', 'modules'));
    }
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
    
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'role' => 'required|in:admin,editor,user',
            'modules' => 'nullable|array',
        ]);
    
        $user->name = $request->input('name');
        $user->email = $request->input('email');
        $user->role = $request->input('role');

        $user->username = explode('@', $request->email)[0];
    
        if ($request->has('modules')) {
            $user->modules()->sync($request->input('modules'));
        } else {
            $user->modules()->sync([]);
        }
    
        $user->save();

        return redirect()->route('users.details', ['id' => $user->id])->with('success', 'Usuário atualizado com sucesso');
    }

    public function destroy($id){

        $user = User::findOrFail($id);

        $user->delete();

        return redirect()->route('users.index')->with('success', 'Usuário deletado com sucesso!');

    }
}
