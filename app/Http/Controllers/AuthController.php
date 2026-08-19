<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\StudentRequirement;
use App\Models\SchoolSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class AuthController extends Controller
{
    public function showLogin($role)
    {
        $pages = [
            'administrator' => 'Auth/AdministratorLogin',
            'teacher' => 'Auth/TeacherLogin',
            'student' => 'Auth/StudentLogin'
        ];

        return Inertia::render($pages[$role] ?? 'Auth/Login');
    }

    public function showRegister()
    {
        return Inertia::render('Auth/register', [
            'currentSchoolYear' => SchoolSetting::currentSchoolYear(),
        ]);
    }

    public function login(Request $request)
    {
        $role = $request->input('role');

        // Different validation based on role
        if ($role === 'student') {
            $credentials = $request->validate([
                'lrn' => 'required|string|size:12',
                'password' => 'required',
                'role' => 'required|in:student'
            ]);

            // Find user by LRN
            $user = User::where('lrn', $credentials['lrn'])
                        ->where('role', 'student')
                        ->first();

            if ($user && Hash::check($credentials['password'], $user->password)) {
                Auth::login($user, $request->remember);
                $request->session()->regenerate();
                return redirect()->intended('/dashboard/student');
            }

            return back()->withErrors([
                'lrn' => 'The provided LRN or password is incorrect.',
            ])->onlyInput('lrn');
        } else {
            // Admin and Teacher login with email
            $credentials = $request->validate([
                'email' => 'required|email',
                'password' => 'required',
                'role' => 'required|in:administrator,teacher'
            ]);

            if (Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password'], 'role' => $credentials['role']], $request->remember)) {
                $request->session()->regenerate();

                return match($credentials['role']) {
                    'administrator' => redirect()->intended('/dashboard/administrator'),
                    'teacher' => redirect()->intended('/dashboard/teacher'),
                    default => redirect()->intended('/dashboard')
                };
            }

            return back()->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ])->onlyInput('email');
        }
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'suffix' => 'nullable|string|max:10',
            'email' => 'required|string|email|max:255|unique:users',
            'phone_no' => 'nullable|string|max:30',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:Male,Female,male,female',
            'province' => 'nullable|string|max:255',
            'municipality' => 'nullable|string|max:255',
            'barangay' => 'nullable|string|max:255',
            'lrn' => 'nullable|string|max:20',
            'previous_gwa' => 'nullable|string|max:10',
            'guardian_full_name' => 'nullable|string|max:255',
            'guardian_contact_no' => 'nullable|string|max:30',
            'guardian_relationship' => 'nullable|string|max:255',
            'father_name' => 'nullable|string|max:255',
            'father_occupation' => 'nullable|string|max:255',
            'father_contact' => 'nullable|string|max:30',
            'mother_name' => 'nullable|string|max:255',
            'mother_occupation' => 'nullable|string|max:255',
            'mother_contact' => 'nullable|string|max:30',
            'blood_type' => 'nullable|string|max:10',
            'allergies' => 'nullable|string|max:1000',
            'medical_conditions' => 'nullable|string|max:1000',
            'emergency_contact_person' => 'nullable|string|max:255',
            'emergency_contact_number' => 'nullable|string|max:30',
            'year_level_applying' => 'nullable|string|max:50',
            'preferred_strand' => 'nullable|string|max:100',
            'previous_school' => 'nullable|string|max:255',
            'school_year_applying' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $gender = $validated['gender'] ?? null;
        if (is_string($gender)) {
            $gender = ucfirst(strtolower($gender));
        }

        $user = new User();
        $user->forceFill([
            'first_name' => $validated['first_name'],
            'middle_name' => $this->nullableString($request, $validated, 'middle_name'),
            'last_name' => $validated['last_name'],
            'suffix' => $this->nullableString($request, $validated, 'suffix'),
            'email' => $validated['email'],
            'phone_no' => $this->nullableString($request, $validated, 'phone_no'),
            'date_of_birth' => $validated['date_of_birth'] ?? $request->input('date_of_birth'),
            'gender' => $gender,
            'province' => $this->nullableString($request, $validated, 'province'),
            'municipality' => $this->nullableString($request, $validated, 'municipality'),
            'barangay' => $this->nullableString($request, $validated, 'barangay'),
            'lrn' => $this->nullableString($request, $validated, 'lrn'),
            'previous_gwa' => $this->nullableString($request, $validated, 'previous_gwa'),
            'guardian_full_name' => $this->nullableString($request, $validated, 'guardian_full_name'),
            'guardian_contact_no' => $this->nullableString($request, $validated, 'guardian_contact_no'),
            'guardian_relationship' => $this->nullableString($request, $validated, 'guardian_relationship'),
            'father_name' => $this->nullableString($request, $validated, 'father_name'),
            'father_occupation' => $this->nullableString($request, $validated, 'father_occupation'),
            'father_contact' => $this->nullableString($request, $validated, 'father_contact'),
            'mother_name' => $this->nullableString($request, $validated, 'mother_name'),
            'mother_occupation' => $this->nullableString($request, $validated, 'mother_occupation'),
            'mother_contact' => $this->nullableString($request, $validated, 'mother_contact'),
            'blood_type' => $this->nullableString($request, $validated, 'blood_type'),
            'allergies' => $this->nullableString($request, $validated, 'allergies'),
            'medical_conditions' => $this->nullableString($request, $validated, 'medical_conditions'),
            'emergency_contact_person' => $this->nullableString($request, $validated, 'emergency_contact_person'),
            'emergency_contact_number' => $this->nullableString($request, $validated, 'emergency_contact_number'),
            'year_level_applying' => $this->nullableString($request, $validated, 'year_level_applying'),
            'preferred_strand' => $this->nullableString($request, $validated, 'preferred_strand'),
            'previous_school' => $this->nullableString($request, $validated, 'previous_school'),
            'school_year_applying' => $this->nullableString($request, $validated, 'school_year_applying'),
            'password' => $validated['password'],
            'role' => 'student',
            'admission_status' => 'pending',
        ])->save();

        $this->storeAdmissionDocuments($request, $user);

        Auth::login($user);

        return redirect('/dashboard/student')->with('success', 'Your admission application has been submitted and is pending registrar approval.');
    }

    private function nullableString(Request $request, array $validated, string $key): ?string
    {
        $value = $request->input($key, $validated[$key] ?? null);

        if (is_string($value)) {
            $value = trim($value);
        }

        return $value === '' || $value === null ? null : (string) $value;
    }

    private function storeAdmissionDocuments(Request $request, User $user): void
    {
        $documentKeys = [
            'form_137',
            'picture_2x2',
            'medical_certificate',
            'birth_certificate',
            'good_moral_certificate',
        ];

        foreach ($documentKeys as $key) {
            if (! $request->hasFile($key)) {
                continue;
            }

            $file = $request->file($key);
            $path = $file->store('requirements/' . $user->id, 'public');

            StudentRequirement::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'requirement_type' => $key,
                ],
                [
                    'file_path' => $path,
                    'original_filename' => $file->getClientOriginalName(),
                    'status' => 'submitted',
                ]
            );
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'You have been logged out successfully.');
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'suffix' => 'nullable|string|max:10',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone_no' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:Male,Female',
            'lrn' => 'nullable|string|max:20',
            'previous_gwa' => 'nullable|string|max:10',
            'guardian_full_name' => 'nullable|string|max:255',
            'guardian_contact_no' => 'nullable|string|max:20',
        ]);

        $user->update($validated);

        return back()->with('success', 'Profile updated successfully!');
    }

    public function updateProfilePhoto(Request $request)
    {
        $request->validate([
            'profile_photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = Auth::user();

        // Delete old photo if exists
        if ($user->profile_photo) {
            Storage::disk('public')->delete($user->profile_photo);
        }

        // Store new photo
        $path = $request->file('profile_photo')->store('profile-photos', 'public');

        $user->update(['profile_photo' => $path]);

        return back()->with('success', 'Profile photo updated successfully!');
    }

    public function removeProfilePhoto()
    {
        $user = Auth::user();

        if ($user->profile_photo) {
            Storage::disk('public')->delete($user->profile_photo);
            $user->update(['profile_photo' => null]);
        }

        return back()->with('success', 'Profile photo removed successfully!');
    }

    public function uploadRequirement(Request $request)
    {
        // Define file type requirements
        $fileTypeRequirements = [
            1 => 'pdf',           // Form 137 - PDF only
            2 => 'jpeg,png,jpg',  // 2x2 Picture - Images only
            3 => 'pdf',           // Birth Certificate - PDF only
            4 => 'pdf'            // Good Moral Certificate - PDF only
        ];

        $requirementId = $request->requirement_id;
        $allowedMimes = $fileTypeRequirements[$requirementId] ?? 'pdf';

        $request->validate([
            'requirement_id' => 'required|integer|in:1,2,3,4',
            'file' => 'required|file|mimes:' . $allowedMimes . '|max:5120',
        ], [
            'file.mimes' => 'Invalid file type for this requirement. Please check the accepted file format.',
        ]);

        $user = Auth::user();
        $file = $request->file('file');
        
        // Define requirement types
        $requirementTypes = [
            1 => 'form_137',
            2 => 'picture_2x2',
            3 => 'birth_certificate',
            4 => 'good_moral_certificate'
        ];

        $requirementType = $requirementTypes[$request->requirement_id];

        // Additional validation for 2x2 picture
        if ($requirementId === 2) {
            $imageInfo = getimagesize($file->getRealPath());
            if ($imageInfo === false) {
                return response()->json(['error' => 'Invalid image file'], 422);
            }

            $width = $imageInfo[0];
            $height = $imageInfo[1];

            // 2x2 inches at 300 DPI = 600x600 pixels
            // Allow tolerance of ±50 pixels (570-630)
            if ($width < 570 || $width > 630 || $height < 570 || $height > 630) {
                return response()->json([
                    'error' => 'Image dimensions must be 2x2 inches (approximately 600x600 pixels). Current size: ' . $width . 'x' . $height . ' pixels'
                ], 422);
            }

            // Check if image is square (within 5% tolerance)
            $aspectRatio = abs($width - $height) / max($width, $height);
            if ($aspectRatio > 0.05) {
                return response()->json([
                    'error' => 'Image must be square (2x2). Current aspect ratio: ' . round($width / $height, 2)
                ], 422);
            }
        }

        // Delete old file if exists
        $existingRequirement = StudentRequirement::where('user_id', $user->id)
            ->where('requirement_type', $requirementType)
            ->first();

        if ($existingRequirement && $existingRequirement->file_path) {
            Storage::disk('public')->delete($existingRequirement->file_path);
        }

        // Store new file
        $path = $file->store('requirements/' . $user->id, 'public');

        // Update or create requirement record
        StudentRequirement::updateOrCreate(
            [
                'user_id' => $user->id,
                'requirement_type' => $requirementType,
            ],
            [
                'file_path' => $path,
                'original_filename' => $file->getClientOriginalName(),
                'status' => 'submitted',
            ]
        );

        return back()->with('success', 'Document uploaded successfully!');
    }

    public function removeRequirement(Request $request)
    {
        $request->validate([
            'requirement_id' => 'required|integer|in:1,2,3,4',
        ]);

        $user = Auth::user();

        // Define requirement types
        $requirementTypes = [
            1 => 'form_137',
            2 => 'picture_2x2',
            3 => 'birth_certificate',
            4 => 'good_moral_certificate'
        ];

        $requirementType = $requirementTypes[$request->requirement_id];

        $requirement = StudentRequirement::where('user_id', $user->id)
            ->where('requirement_type', $requirementType)
            ->first();

        if ($requirement) {
            if ($requirement->file_path) {
                Storage::disk('public')->delete($requirement->file_path);
            }
            $requirement->update(['status' => 'pending', 'file_path' => null, 'original_filename' => null]);
        }

        return back()->with('success', 'Document removed successfully!');
    }
}
