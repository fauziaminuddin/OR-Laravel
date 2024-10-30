<?php

namespace App\Console\Commands\Consumers;

use Illuminate\Console\Command;
use Junges\Kafka\Facades\Kafka;
use Junges\Kafka\Contracts\ConsumerMessage;
use Junges\Kafka\Contracts\MessageConsumer;

class AssignConsumer extends Command
{
    protected $signature = "consume:assign_updates";

    protected $description = "Consume Kafka messages related to assign operations.";

    public function handle()
    {
        Kafka::consumer(['assign_updates'])
            ->withBrokers('localhost:9092')
            ->withAutoCommit()
            ->withHandler(function (ConsumerMessage $message, MessageConsumer $consumer) {
                // $data = json_decode($message->getBody(), true); // Ensure you decode the message
                $data = $message->getBody();

                if (isset($data['assign_created'])) {
                    $this->info('assign created: ' . json_encode($data['assign_created']));
                } elseif (isset($data['assign_updated'])) {
                    $this->info('assign updated: ' . json_encode($data['assign_updated']));
                } elseif (isset($data['assign_deleted'])) {
                    $this->info('assign deleted: ' . json_encode($data['assign_deleted']));
                }
            })
            ->build()
            ->consume();
    }
}