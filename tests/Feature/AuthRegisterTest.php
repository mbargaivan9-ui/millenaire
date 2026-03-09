<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Student;
use Illuminate\Support\Str;
use Database\Seeders\AuthTestSeeder;

class AuthRegisterTest extends TestCase
{
    /**
     * Setup the test environment
     */
    protected function setUp(): void
    {
        parent::setUp();
        // Run auth test seeder to ensure clean state
        $this->seed(AuthTestSeeder::class);
    }

    /**
     * Test allows student registration happy path
     *
     * @return void
     */
    public function test_allows_student_registration_happy_path()
    {
        $email = 'student+' . Str::random(6) . '@millenniaire.test';

        $response = $this->post('/register', [
            'name' => 'Test Student',
            'email' => $email,
            'password' => 'Password@123456',
            'password_confirmation' => 'Password@123456',
            'date_of_birth' => '2008-01-01',
            'gender' => 'M',
        ]);

        $response->assertRedirect(route('login'));
        $this->assertDatabaseHas('users', [
            'email' => $email,
            'role' => 'student',
            'is_active' => true,
        ]);

        // Verify student record was created
        $user = User::where('email', $email)->first();
        $student = Student::where('user_id', $user->id)->first();
        $this->assertNotNull($student);
    }

    /**
     * Test student registration with invalid email
     *
     * @return void
     */
    public function test_registration_with_invalid_email()
    {
        $response = $this->post('/register', [
            'name' => 'Test Student',
            'email' => 'invalid-email',
            'password' => 'Password@123456',
            'password_confirmation' => 'Password@123456',
            'date_of_birth' => '2008-01-01',
            'gender' => 'M',
        ]);

        $response->assertSessionHasErrors('email');
    }

    /**
     * Test student registration with weak password
     *
     * @return void
     */
    public function test_registration_with_weak_password()
    {
        $response = $this->post('/register', [
            'name' => 'Test Student',
            'email' => 'weak@millenniaire.test',
            'password' => 'weak',
            'password_confirmation' => 'weak',
            'date_of_birth' => '2008-01-01',
            'gender' => 'M',
        ]);

        $response->assertSessionHasErrors('password');
    }

    /**
     * Test student registration with mismatched passwords
     *
     * @return void
     */
    public function test_registration_with_mismatched_passwords()
    {
        $response = $this->post('/register', [
            'name' => 'Test Student',
            'email' => 'mismatch@millenniaire.test',
            'password' => 'Password@123456',
            'password_confirmation' => 'Different@123456',
            'date_of_birth' => '2008-01-01',
            'gender' => 'M',
        ]);

        $response->assertSessionHasErrors('password');
    }

    /**
     * Test student registration with invalid date of birth
     *
     * @return void
     */
    public function test_registration_with_invalid_dob()
    {
        $response = $this->post('/register', [
            'name' => 'Test Student',
            'email' => 'invaliddob@millenniaire.test',
            'password' => 'Password@123456',
            'password_confirmation' => 'Password@123456',
            'date_of_birth' => 'invalid-date',
            'gender' => 'M',
        ]);

        $response->assertSessionHasErrors('date_of_birth');
    }

    /**
     * Test student registration with missing required fields
     *
     * @return void
     */
    public function test_registration_with_missing_fields()
    {
        $response = $this->post('/register', [
            'name' => 'Test Student',
            // email missing
            'password' => 'Password@123456',
            'password_confirmation' => 'Password@123456',
            'date_of_birth' => '2008-01-01',
            'gender' => 'M',
        ]);

        $response->assertSessionHasErrors('email');
    }

    /**
     * Test registered user can login immediately
     *
     * @return void
     */
    public function test_registered_user_can_login_immediately()
    {
        $email = 'newuser+' . Str::random(6) . '@millenniaire.test';
        $password = 'Password@123456';

        // Register new user
        $this->post('/register', [
            'name' => 'New User',
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $password,
            'date_of_birth' => '2008-01-01',
            'gender' => 'F',
        ]);

        // Try to login with registered credentials
        $response = $this->post('/login', [
            'email' => $email,
            'password' => $password,
        ]);

        $response->assertRedirect();
        $user = User::where('email', $email)->first();
        $this->assertAuthenticatedAs($user);
    }
}

