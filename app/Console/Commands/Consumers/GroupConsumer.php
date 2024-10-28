<?php

namespace App\Console\Commands\Consumers;

use Illuminate\Console\Command;
use Junges\Kafka\Facades\Kafka;
use Junges\Kafka\Contracts\ConsumerMessage;
use Junges\Kafka\Contracts\MessageConsumer;

class GroupConsumer extends Command
{
    protected $signature = "consume:group_updates";

    protected $description = "Consume Kafka messages related to group operations.";

    public function handle()
    {
        Kafka::consumer(['group_updates'])
            ->withBrokers('localhost:9092')
            ->withAutoCommit()
            ->withHandler(function (ConsumerMessage $message, MessageConsumer $consumer) {
                // $data = json_decode($message->getBody(), true); // Ensure you decode the message
                $data = $message->getBody();

                if (isset($data['group_created'])) {
                    $this->info('Group created: ' . json_encode($data['group_created']));
                } elseif (isset($data['group_updated'])) {
                    $this->info('Group updated: ' . json_encode($data['group_updated']));
                } elseif (isset($data['group_deleted'])) {
                    $this->info('Group deleted: ' . json_encode($data['group_deleted']));
                }
            })
            ->build()
            ->consume();
    }
}