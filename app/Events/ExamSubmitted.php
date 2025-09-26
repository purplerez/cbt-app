<?php

namespace App\Events;

use App\Models\ExamSession;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ExamSubmitted
{
     use Dispatchable, InteractsWithSockets, SerializesModels;

     public $examSession;
     public $scoreDetails;
     public $submissionData;

     /**
      * Create a new event instance.
      */
     public function __construct(ExamSession $examSession, array $scoreDetails, array $submissionData = [])
     {
          $this->examSession = $examSession;
          $this->scoreDetails = $scoreDetails;
          $this->submissionData = $submissionData;
     }

     /**
      * Get the channels the event should broadcast on.
      *
      * @return array<int, \Illuminate\Broadcasting\Channel>
      */
     public function broadcastOn(): array
     {
          return [
               new PrivateChannel('exam-session.' . $this->examSession->id),
          ];
     }
}
