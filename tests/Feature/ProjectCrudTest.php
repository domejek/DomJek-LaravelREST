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

    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['role' => 'user']);
        $this->token = $this->user->createToken('auth-token')->plainTextToken;
    }

    public function test_user_can_create_project(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token,
        ])->postJson('/api/projects', [
            'name' => 'Test Projekt',
            'description' => 'Test Beschreibung',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'name',
                'description',
                'created_at',
                'updated_at',
            ]);

        $this->assertDatabaseHas('projects', [
            'name' => 'Test Projekt',
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

    public function test_project_creation_validates_name_max_length(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token,
        ])->postJson('/api/projects', [
            'name' => str_repeat('a', 256),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_user_can_view_all_projects(): void
    {
        Project::factory()->count(5)->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token,
        ])->getJson('/api/projects');

        $response->assertStatus(200)
            ->assertJsonCount(5);
    }

    public function test_user_can_view_single_project(): void
    {
        $project = Project::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token,
        ])->getJson('/api/projects/'.$project->id);

        $response->assertStatus(200)
            ->assertJson(['id' => $project->id]);
    }

    public function test_user_can_update_project(): void
    {
        $project = Project::factory()->create(['name' => 'Alter Name']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token,
        ])->putJson('/api/projects/'.$project->id, [
            'name' => 'Neuer Name',
        ]);

        $response->assertStatus(200)
            ->assertJson(['name' => 'Neuer Name']);

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'name' => 'Neuer Name',
        ]);
    }

    public function test_user_can_delete_project(): void
    {
        $project = Project::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token,
        ])->deleteJson('/api/projects/'.$project->id);

        $response->assertStatus(204);

        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
    }

    public function test_project_show_includes_tasks(): void
    {
        $project = Project::factory()->create();
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

    public function test_view_non_existent_project_returns_404(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token,
        ])->getJson('/api/projects/9999');

        $response->assertStatus(404);
    }

    public function test_update_non_existent_project_returns_404(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token,
        ])->putJson('/api/projects/9999', [
            'name' => 'Neuer Name',
        ]);

        $response->assertStatus(404);
    }

    public function test_delete_non_existent_project_returns_404(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token,
        ])->deleteJson('/api/projects/9999');

        $response->assertStatus(404);
    }

    public function test_unauthenticated_user_cannot_create_project(): void
    {
        $response = $this->postJson('/api/projects', [
            'name' => 'Test Projekt',
            'description' => 'Test Beschreibung',
        ]);

        $response->assertStatus(401);
    }

    public function test_project_creation_with_nullable_description(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token,
        ])->postJson('/api/projects', [
            'name' => 'Projekt ohne Beschreibung',
        ]);

        $response->assertStatus(201)
            ->assertJson(['name' => 'Projekt ohne Beschreibung']);
    }

    public function test_project_update_with_description(): void
    {
        $project = Project::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token,
        ])->putJson('/api/projects/'.$project->id, [
            'description' => 'Neue Beschreibung',
        ]);

        $response->assertStatus(200)
            ->assertJson(['description' => 'Neue Beschreibung']);
    }
}
