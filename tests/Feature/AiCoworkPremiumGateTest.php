<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Ai\Cowork\AiCoworkService;
use App\Services\Ai\ZAiProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiCoworkPremiumGateTest extends TestCase
{
    use RefreshDatabase;

    public function test_local_environment_can_disable_premium_requirement(): void
    {
        config()->set('services.cowork.require_premium', false);

        $user = User::factory()->create(['role' => 'student']);
        $service = new AiCoworkService();

        \App\Services\Ai\AiServiceFactory::registerProvider('zai', ZAiProvider::class);

        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('hasPremiumAccess');
        $method->setAccessible(true);

        $this->assertTrue($method->invoke($service, $user));
    }

    public function test_admin_bypasses_premium_requirement(): void
    {
        config()->set('services.cowork.require_premium', true);

        $user = User::factory()->create(['role' => 'admin']);
        $service = new AiCoworkService();

        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('hasPremiumAccess');
        $method->setAccessible(true);

        $this->assertTrue($method->invoke($service, $user));
    }
}
