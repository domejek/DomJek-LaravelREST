<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class TaskCrudTest extends TestCase
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

    public function test_user_can_create_task(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token,
        ])->postJson('/api/tasks', [
            'title' => 'Test Task',
            'description' => 'Test Description',
            'status' => 'todo',
            'deadline' => now()->addDays(7)->format('Y-m-d H:i:s'),
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'user_id',
                'title',
                'description',
                'status',
                'deadline',
            ]);

        $this->assertDatabaseHas('tasks', [
            'title' => 'Test Task',
            'user_id' => $this->user->id,
        ]);
    }

    public function test_task_creation_validates_required_fields(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token,
        ])->postJson('/api/tasks', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'description', 'status', 'deadline']);
    }

    public function test_task_creation_validates_title_max_length(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token,
        ])->postJson('/api/tasks', [
            'title' => Str::random(256),
            'description' => 'Test',
            'status' => 'todo',
            'deadline' => now()->addDays(7)->format('Y-m-d H:i:s'),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    public function test_task_creation_validates_status_enum(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token,
        ])->postJson('/api/tasks', [
            'title' => 'Test',
            'description' => 'Test',
            'status' => 'invalid_status',
            'deadline' => now()->addDays(7)->format('Y-m-d H:i:s'),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    public function test_task_creation_validates_deadline_must_be_future(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token,
        ])->postJson('/api/tasks', [
            'title' => 'Test',
            'description' => 'Test',
            'status' => 'todo',
            'deadline' => now()->subDay()->format('Y-m-d H:i:s'),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['deadline']);
    }

    public function test_user_can_view_all_own_tasks(): void
    {
        Task::factory()->count(3)->create(['user_id' => $this->user->id]);
        Task::factory()->count(2)->create(['user_id' => $this->otherUser->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token,
        ])->getJson('/api/tasks');

        $response->assertStatus(200)
            ->assertJsonCount(3);
    }

    public function test_admin_can_view_all_tasks(): void
    {
        Task::factory()->count(3)->create(['user_id' => $this->user->id]);
        Task::factory()->count(2)->create(['user_id' => $this->otherUser->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->adminToken,
        ])->getJson('/api/tasks');

        $response->assertStatus(200)
            ->assertJsonCount(5);
    }

    public function test_user_can_view_single_task(): void
    {
        $task = Task::factory()->create(['user_id' => $this->user->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token,
        ])->getJson('/api/tasks/'.$task->id);

        $response->assertStatus(200)
            ->assertJson(['id' => $task->id]);
    }

    public function test_user_cannot_view_other_users_task(): void
    {
        $task = Task::factory()->create(['user_id' => $this->otherUser->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token,
        ])->getJson('/api/tasks/'.$task->id);

        $response->assertStatus(403);
    }

    public function test_admin_can_view_any_task(): void
    {
        $task = Task::factory()->create(['user_id' => $this->user->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->adminToken,
        ])->getJson('/api/tasks/'.$task->id);

        $response->assertStatus(200)
            ->assertJson(['id' => $task->id]);
    }

    public function test_user_can_update_own_task(): void
    {
        $task = Task::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Old Title',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token,
        ])->putJson('/api/tasks/'.$task->id, [
            'title' => 'New Title',
            'status' => 'in_progress',
        ]);

        $response->assertStatus(200)
            ->assertJson(['title' => 'New Title', 'status' => 'in_progress']);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'New Title',
        ]);
    }

    public function test_user_cannot_update_other_users_task(): void
    {
        $task = Task::factory()->create(['user_id' => $this->otherUser->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token,
        ])->putJson('/api/tasks/'.$task->id, [
            'title' => 'Hacked Title',
        ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_update_any_task(): void
    {
        $task = Task::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Old Title',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->adminToken,
        ])->putJson('/api/tasks/'.$task->id, [
            'title' => 'Admin Updated',
        ]);

        $response->assertStatus(200)
            ->assertJson(['title' => 'Admin Updated']);
    }

    public function test_user_cannot_update_overdue_task(): void
    {
        $task = Task::factory()->create([
            'user_id' => $this->user->id,
            'deadline' => now()->subDay(),
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token,
        ])->putJson('/api/tasks/'.$task->id, [
            'title' => 'New Title',
        ]);

        $response->assertStatus(403)
            ->assertJson(['message' => 'Überfällige Aufgaben können nicht bearbeitet werden']);
    }

    public function test_admin_can_update_overdue_task(): void
    {
        $task = Task::factory()->create([
            'user_id' => $this->user->id,
            'deadline' => now()->subDay(),
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->adminToken,
        ])->putJson('/api/tasks/'.$task->id, [
            'title' => 'Admin Updated',
        ]);

        $response->assertStatus(200);
    }

    public function test_user_can_delete_own_task(): void
    {
        $task = Task::factory()->create(['user_id' => $this->user->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token,
        ])->deleteJson('/api/tasks/'.$task->id);

        $response->assertStatus(204);

        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    public function test_user_cannot_delete_other_users_task(): void
    {
        $task = Task::factory()->create(['user_id' => $this->otherUser->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token,
        ])->deleteJson('/api/tasks/'.$task->id);

        $response->assertStatus(403);
    }

    public function test_admin_can_delete_any_task(): void
    {
        $task = Task::factory()->create(['user_id' => $this->user->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->adminToken,
        ])->deleteJson('/api/tasks/'.$task->id);

        $response->assertStatus(204);
    }

    public function test_user_can_view_overdue_tasks(): void
    {
        Task::factory()->create([
            'user_id' => $this->user->id,
            'deadline' => now()->subDay(),
        ]);
        Task::factory()->create([
            'user_id' => $this->user->id,
            'deadline' => now()->addDay(),
        ]);
        Task::factory()->create([
            'user_id' => $this->otherUser->id,
            'deadline' => now()->subDay(),
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token,
        ])->getJson('/api/tasks/overdue');

        $response->assertStatus(200)
            ->assertJsonCount(1);
    }

    public function test_user_can_view_own_tasks_by_user_id(): void
    {
        Task::factory()->count(3)->create(['user_id' => $this->user->id]);
        Task::factory()->count(2)->create(['user_id' => $this->otherUser->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token,
        ])->getJson('/api/users/'.$this->user->id.'/tasks');

        $response->assertStatus(200)
            ->assertJsonCount(3);
    }

    public function test_user_cannot_view_other_users_tasks_by_id(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token,
        ])->getJson('/api/users/'.$this->otherUser->id.'/tasks');

        $response->assertStatus(403);
    }

    public function test_admin_can_view_any_users_tasks(): void
    {
        Task::factory()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->adminToken,
        ])->getJson('/api/users/'.$this->user->id.'/tasks');

        $response->assertStatus(200)
            ->assertJsonCount(3);
    }

    public function test_user_can_view_tasks_by_project(): void
    {
        $project = Project::factory()->create();
        Task::factory()->count(3)->create(['project_id' => $project->id, 'user_id' => $this->user->id]);
        Task::factory()->count(2)->create(['project_id' => null]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token,
        ])->getJson('/api/projects/'.$project->id.'/tasks');

        $response->assertStatus(200)
            ->assertJsonCount(3);
    }

    public function test_update_validates_status_enum(): void
    {
        $task = Task::factory()->create(['user_id' => $this->user->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token,
        ])->putJson('/api/tasks/'.$task->id, [
            'status' => 'invalid',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    public function test_update_validates_deadline_must_be_future(): void
    {
        $task = Task::factory()->create(['user_id' => $this->user->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token,
        ])->putJson('/api/tasks/'.$task->id, [
            'deadline' => now()->subDay()->format('Y-m-d H:i:s'),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['deadline']);
    }

    public function test_task_creation_with_project_id(): void
    {
        $project = Project::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token,
        ])->postJson('/api/tasks', [
            'title' => 'Task with Project',
            'description' => 'Description',
            'status' => 'todo',
            'deadline' => now()->addDays(7)->format('Y-m-d H:i:s'),
            'project_id' => $project->id,
        ]);

        $response->assertStatus(201)
            ->assertJson(['project_id' => $project->id]);

        $this->assertDatabaseHas('tasks', [
            'title' => 'Task with Project',
            'project_id' => $project->id,
        ]);
    }

    public function test_task_creation_validates_project_id_exists(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token,
        ])->postJson('/api/tasks', [
            'title' => 'Task',
            'description' => 'Description',
            'status' => 'todo',
            'deadline' => now()->addDays(7)->format('Y-m-d H:i:s'),
            'project_id' => 9999,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['project_id']);
    }

    public function test_view_non_existent_task_returns_404(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token,
        ])->getJson('/api/tasks/9999');

        $response->assertStatus(404);
    }

    public function test_update_non_existent_task_returns_404(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token,
        ])->putJson('/api/tasks/9999', [
            'title' => 'New Title',
        ]);

        $response->assertStatus(404);
    }

    public function test_delete_non_existent_task_returns_404(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token,
        ])->deleteJson('/api/tasks/9999');

        $response->assertStatus(404);
    }

    public function test_unauthenticated_user_cannot_create_task(): void
    {
        $response = $this->postJson('/api/tasks', [
            'title' => 'Test Task',
            'description' => 'Test Description',
            'status' => 'todo',
            'deadline' => now()->addDays(7)->format('Y-m-d H:i:s'),
        ]);

        $response->assertStatus(401);
    }

    public function test_admin_overdue_tasks_shows_all(): void
    {
        Task::factory()->create([
            'user_id' => $this->user->id,
            'deadline' => now()->subDay(),
        ]);
        Task::factory()->create([
            'user_id' => $this->otherUser->id,
            'deadline' => now()->subDay(),
        ]);
        Task::factory()->create([
            'user_id' => $this->user->id,
            'deadline' => now()->addDay(),
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->adminToken,
        ])->getJson('/api/tasks/overdue');

        $response->assertStatus(200)
            ->assertJsonCount(2);
    }

    public function test_task_response_includes_user_and_project(): void
    {
        $project = Project::factory()->create();
        $task = Task::factory()->create([
            'user_id' => $this->user->id,
            'project_id' => $project->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token,
        ])->getJson('/api/tasks/'.$task->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'user' => ['id', 'name', 'email'],
                'project' => ['id', 'name'],
            ]);
    }
}
