<?php
declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Models\Student;
use App\DTOs\CreateStudentDTO;
use Tests\TestCase;

class StudentRegistrationTest extends TestCase
{
    /**
     * Test: Student can register with valid data
     */
    public function test_student_can_register_with_valid_data(): void
    {
        // Arrange: Prepare registration data
        $registrationData = [
            'name' => 'Jean Dupont',
            'email' => 'jean.dupont@example.com',
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
            'date_of_birth' => '2008-05-15',
            'gender' => 'M',
        ];

        // Act: Submit registration form
        $response = $this->post(route('register.post'), $registrationData);

        // Assert: User should be created
        $this->assertDatabaseHas('users', [
            'email' => 'jean.dupont@example.com',
            'name' => 'Jean Dupont',
            'role' => 'student',
        ]);

        // Assert: Student record should exist
        $user = User::where('email', 'jean.dupont@example.com')->first();
        expect($user)->not->toBeNull();
        $this->assertDatabaseHas('students', [
            'user_id' => $user->id,
        ]);

        // Assert: Should redirect to login
        $response->assertRedirect(route('login'));
        $response->assertSessionHas('success');
    }

    /**
     * Test: Student registration requires email
     */
    public function test_student_registration_requires_email(): void
    {
        // Arrange: Data without email
        $registrationData = [
            'name' => 'Jean Dupont',
            'email' => '',
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
            'date_of_birth' => '2008-05-15',
            'gender' => 'M',
        ];

        // Act & Assert: Should fail validation
        $response = $this->post(route('register.post'), $registrationData);
        $response->assertSessionHasErrors('email');
    }

    /**
     * Test: Student registration requires unique email
     */
    public function test_student_registration_requires_unique_email(): void
    {
        // Arrange: Create existing user
        User::create([
            'name' => 'Existing User',
            'email' => 'existing@example.com',
            'password' => bcrypt('password'),
            'role' => 'student',
            'is_active' => true,
        ]);

        $registrationData = [
            'name' => 'New User',
            'email' => 'existing@example.com', // Same email
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
            'date_of_birth' => '2008-05-15',
            'gender' => 'M',
        ];

        // Act & Assert: Should fail unique validation
        $response = $this->post(route('register.post'), $registrationData);
        $response->assertSessionHasErrors('email');
    }

    /**
     * Test: Student registration requires password confirmation match
     */
    public function test_student_registration_requires_password_confirmation(): void
    {
        // Arrange: Non-matching passwords
        $registrationData = [
            'name' => 'Jean Dupont',
            'email' => 'jean.dupont@example.com',
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'DifferentPassword456!',
            'date_of_birth' => '2008-05-15',
            'gender' => 'M',
        ];

        // Act & Assert: Should fail validation
        $response = $this->post(route('register.post'), $registrationData);
        $response->assertSessionHasErrors('password');
    }

    /**
     * Test: CreateStudentDTO creation from array
     */
    public function test_create_student_dto_from_array(): void
    {
        // Arrange: Create DTO from array
        $data = [
            'name' => 'Jean Dupont',
            'email' => 'jean@example.com',
            'password' => 'SecurePassword123!',
            'date_of_birth' => '2008-05-15',
            'gender' => 'M',
        ];

        // Act: Create DTO
        $dto = CreateStudentDTO::fromArray($data);

        // Assert: DTO contains correct values
        expect($dto->name)->toBe('Jean Dupont');
        expect($dto->email)->toBe('jean@example.com');
        expect($dto->password)->toBe('SecurePassword123!');
        expect($dto->date_of_birth)->toBe('2008-05-15');
        expect($dto->gender)->toBe('M');
    }

    /**
     * Test: Registered user can login
     */
    public function test_registered_student_can_login(): void
    {
        // Arrange: Register a student
        $registrationData = [
            'name' => 'Login Test User',
            'email' => 'logintest@example.com',
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
            'date_of_birth' => '2008-05-15',
            'gender' => 'F',
        ];

        $this->post(route('register.post'), $registrationData);

        // Act: Try to login with registered credentials
        $loginResponse = $this->post(route('login.post'), [
            'email' => 'logintest@example.com',
            'password' => 'SecurePassword123!',
        ]);

        // Assert: Should be authenticated and redirected to student dashboard
        $this->assertAuthenticatedAs(User::where('email', 'logintest@example.com')->first());
        $loginResponse->assertRedirect(route('student.dashboard'));
    }

    /**
     * Test: Student registration creates proper matricule
     */
    public function test_student_registration_creates_matricule(): void
    {
        // Arrange: Register a student
        $registrationData = [
            'name' => 'Matricule Test User',
            'email' => 'matricule@example.com',
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
            'date_of_birth' => '2008-05-15',
            'gender' => 'M',
        ];

        // Act: Submit registration
        $this->post(route('register.post'), $registrationData);

        // Assert: User and Student records created
        $user = User::where('email', 'matricule@example.com')->first();
        expect($user)->not->toBeNull();

        $student = Student::where('user_id', $user->id)->first();
        expect($student)->not->toBeNull();
        expect($student->matricule)->toMatch('/^STU-\d{6}$/');
    }

    /**
     * Test: Unauthenticated user can access registration page
     */
    public function test_unauthenticated_user_can_access_registration_page(): void
    {
        // Act: Access registration page
        $response = $this->get(route('register'));

        // Assert: Should return 200 and show registration form
        $response->assertStatus(200);
        $response->assertViewIs('auth.register');
    }

    /**
     * Test: Authenticated user cannot access registration page
     */
    public function test_authenticated_user_cannot_access_registration_page(): void
    {
        // Arrange: Create and authenticate a user
        $user = User::factory()->create(['role' => 'student']);
        $this->actingAs($user);

        // Act: Try to access registration page
        $response = $this->get(route('register'));

        // Assert: Should be redirected (guest middleware)
        $response->assertRedirect(route('home'));
    }

    /**
     * Test: Student registration requires valid date of birth
     */
    public function test_student_registration_requires_valid_date_of_birth(): void
    {
        // Arrange: Data with invalid date format
        $registrationData = [
            'name' => 'Jean Dupont',
            'email' => 'jean.dupont@example.com',
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
            'date_of_birth' => 'invalid-date',
            'gender' => 'M',
        ];

        // Act & Assert: Should fail validation
        $response = $this->post(route('register.post'), $registrationData);
        $response->assertSessionHasErrors('date_of_birth');
    }

    /**
     * Test: Student registration requires gender
     */
    public function test_student_registration_requires_gender(): void
    {
        // Arrange: Data without gender
        $registrationData = [
            'name' => 'Jean Dupont',
            'email' => 'jean.dupont@example.com',
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
            'date_of_birth' => '2008-05-15',
            'gender' => '',
        ];

        // Act & Assert: Should fail validation
        $response = $this->post(route('register.post'), $registrationData);
        $response->assertSessionHasErrors('gender');
    }
}
