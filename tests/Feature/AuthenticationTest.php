<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Classe;
use Database\Seeders\AuthTestSeeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthenticationTest extends TestCase
{
    /**
     * Setup the test environment
     */
    protected function setUp(): void
    {
        parent::setUp();
        // Run auth test seeder
        $this->seed(AuthTestSeeder::class);
    }

    /**
     * Test login with admin user
     *
     * @return void
     */
    public function test_admin_login_successful()
    {
        $response = $this->post('/login', [
            'email' => 'admin@millenniaire.test',
            'password' => 'Admin@12345',
        ]);

        $response->assertRedirect();
        $this->assertAuthenticatedAs(User::where('email', 'admin@millenniaire.test')->first());
    }

    /**
     * Test login with teacher user
     *
     * @return void
     */
    public function test_teacher_login_successful()
    {
        $response = $this->post('/login', [
            'email' => 'prof.mathematiques@millenniaire.test',
            'password' => 'Prof@12345',
        ]);

        $response->assertRedirect();
        $this->assertAuthenticatedAs(User::where('email', 'prof.mathematiques@millenniaire.test')->first());
    }

    /**
     * Test login with parent user
     *
     * @return void
     */
    public function test_parent_login_successful()
    {
        $response = $this->post('/login', [
            'email' => 'parent1@millenniaire.test',
            'password' => 'Parent@12345',
        ]);

        $response->assertRedirect();
        $this->assertAuthenticatedAs(User::where('email', 'parent1@millenniaire.test')->first());
    }

    /**
     * Test login with student user
     *
     * @return void
     */
    public function test_student_login_successful()
    {
        $response = $this->post('/login', [
            'email' => 'student1@millenniaire.test',
            'password' => 'Student@12345',
        ]);

        $response->assertRedirect();
        $this->assertAuthenticatedAs(User::where('email', 'student1@millenniaire.test')->first());
    }

    /**
     * Test login with invalid credentials
     *
     * @return void
     */
    public function test_login_with_invalid_credentials()
    {
        $response = $this->post('/login', [
            'email' => 'admin@millenniaire.test',
            'password' => 'WrongPassword',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /**
     * Test login with inactive user
     *
     * @return void
     */
    public function test_login_with_inactive_user()
    {
        // Create an inactive user
        $inactiveUser = User::create([
            'name' => 'Inactive User',
            'email' => 'inactive@millenniaire.test',
            'password' => Hash::make('Password@12345'),
            'role' => 'student',
            'is_active' => false,
        ]);

        $response = $this->post('/login', [
            'email' => 'inactive@millenniaire.test',
            'password' => 'Password@12345',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /**
     * Test student registration
     *
     * @return void
     */
    public function test_student_registration_successful()
    {
        $response = $this->post('/register', [
            'name' => 'New Student',
            'email' => 'newstudent@millenniaire.test',
            'password' => 'NewPass@12345',
            'password_confirmation' => 'NewPass@12345',
            'date_of_birth' => '2010-05-15',
            'gender' => 'M',
        ]);

        $response->assertRedirect('/login');
        
        $user = User::where('email', 'newstudent@millenniaire.test')->first();
        $this->assertNotNull($user);
        $this->assertEquals('student', $user->role);
        $this->assertTrue($user->is_active);
        
        // Check if student record was created
        $student = Student::where('user_id', $user->id)->first();
        $this->assertNotNull($student);
    }

    /**
     * Test registration with duplicate email
     *
     * @return void
     */
    public function test_registration_with_duplicate_email()
    {
        $response = $this->post('/register', [
            'name' => 'Another Student',
            'email' => 'student1@millenniaire.test', // Already exists
            'password' => 'NewPass@12345',
            'password_confirmation' => 'NewPass@12345',
            'date_of_birth' => '2010-05-15',
            'gender' => 'M',
        ]);

        $response->assertSessionHasErrors('email');
    }

    /**
     * Test logout
     *
     * @return void
     */
    public function test_logout()
    {
        $user = User::where('email', 'admin@millenniaire.test')->first();
        
        $response = $this->actingAs($user)->post('/logout');
        
        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    /**
     * Test user data integrity after login
     *
     * @return void
     */
    public function test_user_data_after_login()
    {
        $user = User::where('email', 'student1@millenniaire.test')->first();
        
        $response = $this->post('/login', [
            'email' => 'student1@millenniaire.test',
            'password' => 'Student@12345',
        ]);

        $response->assertRedirect();
        
        // Verify user data
        $authenticatedUser = Auth::user();
        $this->assertEquals($user->id, $authenticatedUser->id);
        $this->assertEquals($user->email, $authenticatedUser->email);
        $this->assertEquals('student', $authenticatedUser->role);

        // Verify student relationship
        $student = $authenticatedUser->student;
        $this->assertNotNull($student);
        $this->assertEquals('STU-000001', $student->matricule);
    }

    /**
     * Test teacher data integrity
     *
     * @return void
     */
    public function test_teacher_data_after_login()
    {
        $user = User::where('email', 'prof.mathematiques@millenniaire.test')->first();
        
        $response = $this->post('/login', [
            'email' => 'prof.mathematiques@millenniaire.test',
            'password' => 'Prof@12345',
        ]);

        $response->assertRedirect();
        
        // Verify teacher relationship
        $teacher = Auth::user()->teacher;
        $this->assertNotNull($teacher);
        $this->assertEquals('PROF-001', $teacher->matricule);
        $this->assertFalse($teacher->is_prof_principal);
    }

    /**
     * Test principal teacher attributes
     *
     * @return void
     */
    public function test_principal_teacher_attributes()
    {
        $user = User::where('email', 'prof.francais@millenniaire.test')->first();
        
        $response = $this->post('/login', [
            'email' => 'prof.francais@millenniaire.test',
            'password' => 'Prof@12345',
        ]);

        $response->assertRedirect();
        
        $teacher = Auth::user()->teacher;
        $this->assertTrue($teacher->is_prof_principal);
        $this->assertEquals('prof_principal', Auth::user()->role);
    }
}
