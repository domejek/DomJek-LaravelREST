<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectCrudTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected User $otherUser;

    protected User $admin;

    protected string $token;

    protected string $otherToken;

    protected string $adminToken;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['role' => 'user']);
        $this->otherUser = User::factory()->create(['role' => 'user']);
        $this->admin = User::factory()->create(['role' => 'admin']);

        $this->token = $this->user->createToken('auth-token')->plainTextToken;
        $this->otherToken = $this->otherUser->createToken('auth-token')->plainTextToken;
        $this->adminToken = $this->admin->createToken('auth-token')->plainTextToken;
    }

    public function test_user_can_create_project(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token,
        ])->postJson('/api/projects', [
            'name' => 'Test Project',
            'description' => 'Test Description',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'user_id',
                'name',
                'description',
                'created_at',
                'updated_at',
            ]);

        $this->assertDatabaseHas('projects', [
            'name' => 'Test Project',
            'user_id' => $this->user->id,
        ]);
    }

    public function test_project_creation_validates_required_fields(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token,
        ])->postJson('/api/projects', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_project_creation_validates_title_max_length(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token,
        ])->postJson('/api/projects', [
            'name' => str_repeat('a', 256),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_user_can_view_all_own_projects(): void
    {
        Project::factory()->count(3)->create(['user_id' => $this->user->id]);
        Project::factory()->count(2)->create(['user_id' => $this->otherUser->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token,
        ])->getJson('/api/projects');

        $response->assertStatus(200)
            ->assertJsonCount(3);
    }

    public function test_admin_can_view_all_projects(): void
    {
        Project::factory()->count(3)->create(['user_id' => $this->user->id]);
        Project::factory()->count(2)->create(['user_id' => $this->otherUser->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->adminToken,
        ])->getJson('/api/projects');

        $response->assertStatus(200)
            ->assertJsonCount(5);
    }

    public function test_user_can_view_single_project(): void
    {
        $project = Project::factory()->create(['user_id' => $this->user->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token,
        ])->getJson('/api/projects/'.$project->id);

        $response->assertStatus(200)
            ->assertJson(['id' => $project->id]);
    }

    public function test_user_cannot_view_other_users_project(): void
    {
        $project = Project::factory()->create(['user_id' => $this->otherUser->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token,
        ])->getJson('/api/projects/'.$project->id);

        $response->assertStatus(403);
    }

    public function test_admin_can_view_any_project(): void
    {
        $project = Project::factory()->create(['user_id' => $this->user->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->adminToken,
        ])->getJson('/api/projects/'.$project->id);

        $response->assertStatus(200)
            ->assertJson(['id' => $project->id]);
    }

    public function test_user_can_update_own_project(): void
    {
        $project = Project::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Old Name',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token,
        ])->putJson('/api/projects/'.$project->id, [
            'name' => 'New Name',
        ]);

        $response->assertStatus(200)
            ->assertJson(['name' => 'New Name']);

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'name' => 'New Name',
        ]);
    }

    public function test_user_cannot_update_other_users_project(): void
    {
        $project = Project::factory()->create(['user_id' => $this->otherUser->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token,
        ])->putJson('/api/projects/'.$project->id, [
            'name' => 'Hacked Name',
        ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_update_any_project(): void
    {
        $project = Project::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Old Name',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->adminToken,
        ])->putJson('/api/projects/'.$project->id, [
            'name' => 'Admin Updated',
        ]);

        $response->assertStatus(200)
            ->assertJson(['name' => 'Admin Updated']);
    }

    public function test_user_can_delete_own_project(): void
    {
        $project = Project::factory()->create(['user_id' => $this->user->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token,
        ])->deleteJson('/api/projects/'.$project->id);

        $response->assertStatus(204);

        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
    }

    public function test_user_cannot_delete_other_users_project(): void
    {
        $project = Project::factory()->create(['user_id' => $this->otherUser->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token,
        ])->deleteJson('/api/projects/'.$project->id);

        $response->assertStatus(403);
    }

    public function test_admin_can_delete_any_project(): void
    {
        $project = Project::factory()->create(['user_id' => $this->user->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->adminToken,
        ])->deleteJson('/api/projects/'.$project->id);

        $response->assertStatus(204);
    }

    public function test_project_show_includes_tasks(): void
    {
        $project = Project::factory()->create(['user_id' => $this->user->id]);
        Task::factory()->count(3)->create(['project_id' => $project->id, 'user_id' => $this->user->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token,
        ])->getJson('/api/projects/'.$project->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'tasks',
            ]);
    }
}
