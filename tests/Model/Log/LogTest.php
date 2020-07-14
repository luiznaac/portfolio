<?php

namespace Tests\Model\Log;

use App\Model\Log\Log;
use Tests\TestCase;

class LogTest extends TestCase {

    public function testLogShouldFillCreatedAndUpdatedAt(): void {
        Log::log(Log::EXCEPTION_TYPE, 'test', 'testing');

        /** @var Log $log */
        $log = Log::query()->first();

        $this->assertEquals(Log::EXCEPTION_TYPE, $log->type);
        $this->assertEquals('test', $log->source);
        $this->assertEquals('testing', $log->message);
        $this->assertNotNull($log->created_at);
        $this->assertNotNull($log->updated_at);
    }
}
