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

                if (isset($data['classroom_created'])) {
                    $this->info('Classroom created: ' . json_encode($data['classroom_created']));
                } elseif (isset($data['classroom_updated'])) {
                    $this->info('Classroom updated: ' . json_encode($data['classroom_updated']));
                }elseif (isset($data['classroom_updated'])) {
                    $this->info('Classroom deleted: ' . json_encode($data['classroom_deleted']));
                };
            })
            ->build()
            ->consume();
    }

}