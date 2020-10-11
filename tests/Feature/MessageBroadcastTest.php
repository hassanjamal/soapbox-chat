<?php

namespace Tests\Feature;

use App\Events\MessageSentToChannel;
use App\Models\Channel;
use App\Models\User;
use App\Models\Workspace;
use App\Notifications\NewMessageToChannel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class MessageBroadcastTest extends TestCase
{
    use RefreshDatabase;

    protected $firstUser;

    protected $secondUser;

    protected $workspace;

    protected $channel;

    public function setUp(): void
    {
        parent::setUp();
        $this->workspace = Workspace::factory(['name' => 'ABC Org'])
                                    ->has(Channel::factory())
                                    ->has(User::factory()->count(2))
                                    ->create();
        $this->firstUser = collect($this->workspace->users)->first();
        $this->secondUser = collect($this->workspace->users)->last();
        $this->channel = collect($this->workspace->channels)->first();
    }

    /** @test  */
    function a_user_should_receive_a_message_sent_in_a_group()
    {
        $this->channel->users()->attach([$this->firstUser->id]);

        $this->actingAs($this->firstUser)->json('POST', route('message.create'),
            [
                'content' => 'Some Message for you',
                'workspace_id' => $this->workspace->id,
                'channel_id' => $this->channel->id
            ])
             ->assertOk();

        $this->markTestIncomplete('Need to complete the test case ');
        // TODO This needs to be completed 

    }

    /** @test  */
    function a_user_should_receive_a_notification_when_a_message_is_sent_in_a_group()
    {

        Notification::fake();

        $this->channel->users()->attach([$this->firstUser->id, $this->secondUser->id]);

        $this->actingAs($this->firstUser)->json('POST', route('message.create'),
            [
                'content' => 'Some Message for you',
                'workspace_id' => $this->workspace->id,
                'channel_id' => $this->channel->id
            ])
             ->assertOk();

        Notification::assertSentTo($this->secondUser, NewMessageToChannel::class);
        Notification::assertNotSentTo($this->firstUser, NewMessageToChannel::class);

    }

}
