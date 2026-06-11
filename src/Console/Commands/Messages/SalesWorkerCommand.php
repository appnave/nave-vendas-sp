<?php

namespace BildVitta\SpVendas\Console\Commands\Messages;

use BildVitta\SpVendas\Console\Commands\Messages\Resources\MessageSale;
use Illuminate\Console\Command;
use PhpAmqpLib\Connection\AMQPSSLConnection;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exception\AMQPExceptionInterface;
use PhpAmqpLib\Channel\AMQPChannel;
use Throwable;

class SalesWorkerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rabbitmqworker:sales';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gets and processes messages';

    /**
     * @var AMQPStreamConnection
     */
    private $connection;

    /**
     * @var AMQPChannel
     */
    private $channel;

    /**
     * @var MessageSale
     */
    private MessageSale $messageSale;

    /**
     * @param MessageSale $messageSale
     */
    public function __construct(MessageSale $messageSale)
    {
        parent::__construct();
        $this->messageSale = $messageSale;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        while (true) {
            try {
                $this->process();
            } catch (AMQPExceptionInterface $exception) {
                $this->closeChannel();
                $this->closeConnection();
                sleep(5);
            }
        }

        return 0;
    }

    /**
     * @return void
     */
    private function process(): void
    {
        $this->connect();
        $this->channel = $this->connection->channel();

        $queueName = config('sp-vendas.rabbitmq.queue.sales');
        $callback = [$this->messageSale, 'process'];
        $this->channel->basic_consume(
            queue: $queueName,
            callback: $callback
        );

        $this->channel->consume();

        $this->closeChannel();
        $this->closeConnection();
    }

    /**
     * @return void
     */
    private function closeChannel(): void
    {
        try {
            if ($this->channel) {
                $this->channel->close();
                $this->channel = null;
            }
        } catch (Throwable $exception) {
        }
    }

    /**
     * @return void
     */
    private function closeConnection(): void
    {
        try {
            if ($this->connection) {
                $this->connection->close();
                $this->connection = null;
            }
        } catch (Throwable $exception) {
        }
    }

    /**
     * @return void
     */
    private function connect(): void
    {
        $host = config('sp-vendas.rabbitmq.host');
        $port = config('sp-vendas.rabbitmq.port');
        $user = config('sp-vendas.rabbitmq.user');
        $password = config('sp-vendas.rabbitmq.password');
        $virtualhost = config('sp-vendas.rabbitmq.virtualhost');
        $heartbeat = 20;
        $sslOptions = [
            'verify_peer' => false
        ];
        $options = [
            'heartbeat' => $heartbeat
        ];

        if (app()->isLocal()) {
            $this->connection = new AMQPStreamConnection(
                host: $host,
                port: $port,
                user: $user,
                password: $password,
                vhost: $virtualhost,
                heartbeat: $heartbeat
            );
        } else {
            $this->connection = new AMQPSSLConnection(
                host: $host,
                port: $port,
                user: $user,
                password: $password,
                vhost: $virtualhost,
                ssl_options: $sslOptions,
                options: $options
            );
        }
    }
}
