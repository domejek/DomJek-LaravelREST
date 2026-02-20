<?php

namespace Tests\Unit;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskFormRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_task_request_authorize_gibt_true_zurueck(): void
    {
        $request = new StoreTaskRequest;

        $this->assertTrue($request->authorize());
    }

    public function test_store_task_request_rules_enthaelt_pflichtfelder(): void
    {
        $request = new StoreTaskRequest;
        $rules = $request->rules();

        $this->assertArrayHasKey('title', $rules);
        $this->assertArrayHasKey('description', $rules);
        $this->assertArrayHasKey('status', $rules);
        $this->assertArrayHasKey('deadline', $rules);
        $this->assertArrayHasKey('project_id', $rules);
    }

    public function test_store_task_request_title_validation(): void
    {
        $request = new StoreTaskRequest;
        $rules = $request->rules();

        $this->assertStringContainsString('required', $rules['title']);
        $this->assertStringContainsString('string', $rules['title']);
        $this->assertStringContainsString('max:255', $rules['title']);
    }

    public function test_store_task_request_status_validation(): void
    {
        $request = new StoreTaskRequest;
        $rules = $request->rules();

        $this->assertStringContainsString('required', $rules['status']);
        $this->assertStringContainsString('in:todo,in_progress,done', $rules['status']);
    }

    public function test_store_task_request_deadline_validation(): void
    {
        $request = new StoreTaskRequest;
        $rules = $request->rules();

        $this->assertStringContainsString('required', $rules['deadline']);
        $this->assertStringContainsString('date', $rules['deadline']);
        $this->assertStringContainsString('after:now', $rules['deadline']);
    }

    public function test_store_task_request_project_id_validation(): void
    {
        $request = new StoreTaskRequest;
        $rules = $request->rules();

        $this->assertStringContainsString('nullable', $rules['project_id']);
        $this->assertStringContainsString('exists:projects,id', $rules['project_id']);
    }

    public function test_update_task_request_authorize_gibt_true_zurueck(): void
    {
        $request = new UpdateTaskRequest;

        $this->assertTrue($request->authorize());
    }

    public function test_update_task_request_rules_verwendet_sometimes(): void
    {
        $request = new UpdateTaskRequest;
        $rules = $request->rules();

        $this->assertStringContainsString('sometimes', $rules['title']);
        $this->assertStringContainsString('sometimes', $rules['description']);
        $this->assertStringContainsString('sometimes', $rules['status']);
        $this->assertStringContainsString('sometimes', $rules['deadline']);
    }

    public function test_update_task_request_status_validation(): void
    {
        $request = new UpdateTaskRequest;
        $rules = $request->rules();

        $this->assertStringContainsString('in:todo,in_progress,done', $rules['status']);
    }

    public function test_update_task_request_deadline_validation(): void
    {
        $request = new UpdateTaskRequest;
        $rules = $request->rules();

        $this->assertStringContainsString('after:now', $rules['deadline']);
    }
}
