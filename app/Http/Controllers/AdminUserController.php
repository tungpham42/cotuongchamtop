<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $query = $this->filteredQuery($request)
            ->withSum('karmaLogs as gems_sum', 'amount');

        $users = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    /**
     * Export name + email of users as CSV.
     * Respects the same search / role filters as the index page.
     */
    public function export(Request $request): StreamedResponse
    {
        $query = $this->filteredQuery($request)
            ->select(['id', 'name', 'email'])
            ->orderBy('id');

        $filename = 'users-' . now()->format('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');

            // UTF-8 BOM so Excel displays Vietnamese characters correctly
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, ['Name', 'Email']);

            // chunkById keeps memory flat for large user tables
            $query->chunkById(1000, function ($users) use ($out) {
                foreach ($users as $user) {
                    fputcsv($out, [
                        $this->csvSafe($user->name),
                        $this->csvSafe($user->email),
                    ]);
                }
            });

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Shared search / role filters used by index() and export().
     */
    private function filteredQuery(Request $request)
    {
        $query = User::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('is_admin', $request->role === 'admin');
        }

        return $query;
    }

    /**
     * Prevent CSV/formula injection: a name like "=HYPERLINK(...)" would be
     * executed by Excel/Sheets. Prefix risky leading characters with a quote.
     */
    private function csvSafe(?string $value): string
    {
        $value = (string) $value;

        if ($value !== '' && in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
            return "'" . $value;
        }

        return $value;
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'              => 'required|string|max:255',
            'email'             => 'required|string|email|max:255|unique:users',
            'password'          => 'required|string|min:8',
            'elo'               => 'nullable',
            'is_admin'          => 'boolean',
            'subscription_plan' => 'nullable|string',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_admin'] = $request->has('is_admin');

        User::create($validated);

        return redirect()->route('admin.users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        $user->loadSum('karmaLogs as gems_sum', 'amount');

        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'              => 'required|string|max:255',
            'email'             => "required|string|email|max:255|unique:users,email,{$user->id}",
            'elo'               => 'required',
            'subscription_plan' => 'nullable|string',
        ]);

        if ($request->filled('password')) {
            $request->validate(['password' => 'min:8']);
            $validated['password'] = Hash::make($request->password);
        }

        $user->is_admin = $request->has('is_admin');
        $user->update($validated);

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own admin account.');
        }

        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully.');
    }
}