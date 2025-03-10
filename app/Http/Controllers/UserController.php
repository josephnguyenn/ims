<?php  
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    // ✅ Allow both Admin & Staff to view users
    public function index()
    {
        return response()->json(User::all(), 200);
    }

    // ✅ Only Admins can create new users
    public function store(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'required|in:admin,staff'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => $request->role
        ]);

        return response()->json(['message' => 'User created successfully', 'user' => $user], 201);
    }

    // ✅ Only Admins can delete users
    public function destroy($id)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Access denied'], 403);
        }

        User::destroy($id);
        return response()->json(['message' => 'User deleted successfully']);
    }
}
?>