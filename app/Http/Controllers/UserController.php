<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function search(Request $request)
{
    $query = $request->input('query');
    $users = User::where('name', 'like', "%{$query}%")->get();
    return response()->json($users);
}
}
