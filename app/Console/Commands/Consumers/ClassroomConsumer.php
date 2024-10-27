<?php

namespace App\Console\Commands\Consumers;

use Illuminate\Console\Command;
use Junges\Kafka\Facades\Kafka;
use Junges\Kafka\Contracts\ConsumerMessage;
use Junges\Kafka\Contracts\MessageConsumer;
use App\Models\Classroom; // Import your Classroom model

class ClassroomConsumer extends Command
{
    protected $signature = "consume:classroom_updates";
    protected $description = "Consume classroom updates from Kafka.";

    public function handle()
    {
        Kafka::consumer(['classroom_updates'])
            ->withBrokers('localhost:9092')
            ->withAutoCommit()
            ->withHandler(function (ConsumerMessage $message, MessageConsumer $consumer) {
                $data = $message->getBody();

                // Log the message for debugging
                $this->info('Received message: ' . json_encode($data));
            })
            ->build()
            ->consume();
    }

}