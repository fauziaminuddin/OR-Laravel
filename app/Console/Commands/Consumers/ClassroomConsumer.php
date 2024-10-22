<?php

namespace App\Console\Commands\Consumers;

use Illuminate\Console\Command;
use Junges\Kafka\Facades\Kafka;
use Junges\Kafka\Contracts\ConsumerMessage;
use Junges\Kafka\Contracts\MessageConsumer;

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
                $data = json_decode($message->getBody(), true);
                // Handle the message based on its type
                switch ($message->getKey()) {
                    case 'classroom_created':
                        // Logic to handle classroom creation
                        break;
                    case 'classroom_updated':
                        // Logic to handle classroom update
                        break;
                    case 'classroom_deleted':
                        // Logic to handle classroom deletion
                        break;
                }
            })
            ->build()
            ->consume();
    }
}
