<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Users;
use App\Models\Booking;
use App\Models\Facility;
use App\Models\FacilityItem;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function logAdmin()
    {
        return view('content.index');
    }

    public function index()
    {
        $user = Auth::user();
        
        // Get upcoming bookings - either all for admin or user-specific
        $upcomingBookings = Booking::with(['facilityItem', 'user'])
            ->when($user->role != 'admin' && $user->role != 'headmaster', function($query) use ($user) {
                return $query->where('user_id', $user->id);
            })
            ->where('status', 'approved')
            ->where('end_datetime', '>=', Carbon::now())
            ->orderBy('start_datetime')
            ->take(3)
            ->get();
            
        // Get all bookings for calendar (filtered by user role)
        $calendarBookings = Booking::with(['facilityItem'])
            ->when($user->role != 'admin' && $user->role != 'headmaster', function($query) use ($user) {
                return $query->where('user_id', $user->id);
            })
            ->whereIn('status', ['approved'])
            ->where('end_datetime', '>=', Carbon::now()->subDays(30))
            ->where('start_datetime', '<=', Carbon::now()->addDays(30))
            ->get()
            ->map(function($booking) {
                return [
                    'id' => $booking->id,
                    'title' => $booking->facilityItem->item_code . ' - ' . $booking->purpose,
                    'start' => $booking->start_datetime->format('Y-m-d\TH:i:s'),
                    'end' => $booking->end_datetime->format('Y-m-d\TH:i:s'),
                    'color' => $this->getStatusColor($booking->status),
                    // 'color' => $booking->status === 'approved' ? '#7AA36C' : '#E9B872', // Green for approved, pasta yellow for pending
                    'url' => route('admin.dashboard', $booking->id),
                ];
            });
        
        // Get stats for admins and headmasters
        $stats = [];
        if (in_array($user->role, ['admin', 'headmaster'])) {
            $stats = [
                'total_facilities' => FacilityItem::count(),
                // 'available_items' => FacilityItem::where('status', 'available')->count(),
                'pending_bookings' => Booking::where('status', 'pending')->count(),
                'active_bookings' => Booking::where('status', 'approved')
                    ->where('start_datetime', '>=', Carbon::now())
                    ->where('end_datetime', '>=', Carbon::now())
                    ->count(),
            ];
        }
        
        // Calculate usage percentages of facilities for admins
        // $facilityUsage = [];
        // if ($user->role === 'admin') {
        //     $facilities = Facility::withCount(['items as total_items', 'items as used_items' => function($query) {
        //         $query->where('status', '!=', 'available');
        //     }])->get();
            
        //     foreach ($facilities as $facility) {
        //         $percentage = $facility->total_items > 0 
        //             ? round(($facility->used_items / $facility->total_items) * 100) 
        //             : 0;
                    
        //         $facilityUsage[] = [
        //             'name' => $facility->name,
        //             'percentage' => $percentage,
        //             'used' => $facility->used_items,
        //             'total' => $facility->total_items,
        //         ];
        //     }
        // }
        
        return view('admin.dashboard', compact(
            'upcomingBookings', 
            'calendarBookings', 
            'stats', 
            // 'facilityUsage'
        ));
    }

    private function getStatusColor($status)
    {
        $colors = [
            'pending' => '#FBBC05',    // Warning yellow
            'approved' => '#34A853',   // Success green
            'rejected' => '#EA4335',   // Danger red
            'completed' => '#1A73E8',  // Primary blue
            'cancelled' => '#5F6368',  // Medium gray
        ];
        
        return $colors[$status] ?? '#1A73E8';
    }

    // public function listUsers()
    // {
    //     $users = Users::where('is_active', 1)
    //                 ->orderBy('name')
    //                 ->paginate(10);
    //     return view('admin.users-index', compact('users'));
    // }
    public function listUsers(Request $request)
    {
        $query = Users::where('is_active', 1);

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', '%' . $search . '%');
        }

        if ($request->has('role') && $request->role != '') {
            $query->where('role', $request->role);
        }

        $users = $query->orderBy('name')->paginate(10)->withQueryString();
        return view('admin.users-index', compact('users'));
    }


    public function listDeletedUsers(Request $request)
    {
        $query = Users::where('is_active', 0);

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', '%' . $search . '%');
        }

        if ($request->has('role') && $request->role != '') {
            $query->where('role', $request->role);
        }

        $users = $query->orderBy('name')->paginate(10)->withQueryString();
        return view('admin.deleted-users', compact('users'));
    }


    public function createUser()
    {
        return view('admin.users-create');
    }

    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,technician,user,headmaster',
            'phone' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        $user = new Users();
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->password = Hash::make($validated['password']);
        $user->role = $validated['role'];
        $user->phone = $validated['phone'];
        $user->is_active = $request->has('is_active') ? 1 : 0;
        $user->save();

        return redirect()->route('users.list')
            ->with('success', 'User created successfully.');
    }

    public function editUser(Users $user)
    {
        return view('admin.users-edit', compact('user'));
    }

    public function updateUser(Request $request, Users $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|in:admin,technician,user,headmaster',
            'phone' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }
        $user->role = $validated['role'];
        $user->phone = $validated['phone'];
        $user->is_active = $request->has('is_active') ? 1 : 0;
        $user->save();

        return redirect()->route('users.list')
            ->with('success', 'User updated successfully.');
    }

    public function destroyUser(Users $user)
    {
        // Soft delete by setting is_active to false
        $user->is_active = false;
        $user->save();
        
        // Optionally, if you want true deletion:
        // $user->delete();

        return redirect()->route('users.list')
            ->with('success', 'User deactivated successfully.');
    }

    public function restoreUser($id)
    {
        $user = Users::findOrFail($id);
        $user->is_active = true;
        $user->save();
        
        return redirect()->route('users.list')
            ->with('success', 'User reactivated successfully.');
    }
}
