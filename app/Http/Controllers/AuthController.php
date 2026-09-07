<?php

namespace App\Http\Controllers;

use App\Models\SchoolSetting;
use App\Models\StudentRequirement;
use App\Models\User;
use App\Support\AdmissionDocuments;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class AuthController extends Controller
{
    public function showLogin($role)
    {
        $pages = [
            'administrator' => 'Auth/AdministratorLogin',
            'registrar' => 'Auth/RegistrarLogin',
            'teacher' => 'Auth/TeacherLogin',
            'student' => 'Auth/StudentLogin',
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
                'role' => 'required|in:student',
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
                'role' => 'required|in:administrator,teacher,registrar',
            ]);

            if (Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password'], 'role' => $credentials['role']], $request->remember)) {
                $request->session()->regenerate();

                return match ($credentials['role']) {
                    'administrator' => redirect()->intended('/dashboard/administrator'),
                    'registrar' => redirect()->intended('/dashboard/registrar'),
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
            'lrn' => [
                'required',
                'digits:12',
                Rule::unique('users', 'lrn'),
            ],
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
            'form_137' => 'nullable|file|mimes:pdf|max:5120',
            'birth_certificate' => 'nullable|file|mimes:pdf|max:5120',
            'good_moral_certificate' => 'nullable|file|mimes:pdf|max:5120',
            'accomplishment_credentials' => 'nullable|file|mimes:pdf|max:5120',
        ], [
            'lrn.required' => 'LRN is required.',
            'lrn.digits' => 'LRN must be exactly 12 digits.',
            'lrn.unique' => 'This LRN is already registered. Each learner must have a unique LRN.',
        ]);

        $gender = $validated['gender'] ?? null;
        if (is_string($gender)) {
            $gender = ucfirst(strtolower($gender));
        }

        $user = new User;
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
            'lrn' => $validated['lrn'],
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
        $documentKeys = AdmissionDocuments::typesForYearLevel($user->year_level_applying);

        foreach ($documentKeys as $key) {
            if (! $request->hasFile($key)) {
                continue;
            }

            $file = $request->file($key);

            if (! $file->isValid()) {
                continue;
            }

            $path = $file->store('requirements/'.$user->id, 'public');

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
        $user = $this->authenticatedUser();

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'suffix' => 'nullable|string|max:10',
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'phone_no' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:Male,Female',
            'lrn' => [
                Rule::requiredIf($user->role === 'student'),
                'nullable',
                'digits:12',
                Rule::unique('users', 'lrn')->ignore($user->id),
            ],
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

        $user = $this->authenticatedUser();

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
        $user = $this->authenticatedUser();

        if ($user->profile_photo) {
            Storage::disk('public')->delete($user->profile_photo);
            $user->update(['profile_photo' => null]);
        }

        return back()->with('success', 'Profile photo removed successfully!');
    }

    public function uploadRequirement(Request $request)
    {
        $allowedTypes = AdmissionDocuments::allowedTypes();

        $request->validate([
            'requirement_type' => 'required|string|in:'.implode(',', $allowedTypes),
            'file' => 'required|file|mimes:pdf|max:5120',
        ], [
            'file.mimes' => 'Invalid file type for this requirement. Please upload a PDF.',
        ]);

        $user = $this->authenticatedUser();
        $requirementType = $request->string('requirement_type')->toString();
        $requiredTypes = AdmissionDocuments::typesForYearLevel($user->year_level_applying);

        if (! in_array($requirementType, $requiredTypes, true)) {
            return back()->withErrors([
                'file' => 'This document is not required for your year level.',
            ]);
        }

        $file = $request->file('file');

        $existingRequirement = StudentRequirement::where('user_id', $user->id)
            ->where('requirement_type', $requirementType)
            ->first();

        if ($existingRequirement && $existingRequirement->file_path) {
            Storage::disk('public')->delete($existingRequirement->file_path);
        }

        $path = $file->store('requirements/'.$user->id, 'public');

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
        $allowedTypes = AdmissionDocuments::allowedTypes();

        $request->validate([
            'requirement_type' => 'required|string|in:'.implode(',', $allowedTypes),
        ]);

        $user = $this->authenticatedUser();
        $requirementType = $request->string('requirement_type')->toString();

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

    private function authenticatedUser(): User
    {
        $user = Auth::user();
        abort_unless($user instanceof User, 401);

        return $user;
    }
}
