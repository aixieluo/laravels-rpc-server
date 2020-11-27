<?php

namespace Aixieluo\Rpc\Server;

use Hhxsv5\LaravelS\Swoole\Socket\TcpInterface;
use Hhxsv5\LaravelS\Swoole\Socket\TcpSocket as Tcp;
use Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Swoole\Server;

class TcpSocket extends Tcp implements TcpInterface
{
    public function onConnect(Server $server, $fd, $reactorId)
    {
        Log::info('New TCP connection', [$fd]);
        $server->send($fd, 'Welcome to LaravelS.');
    }

    public function onClose(Server $server, $fd, $reactorId)
    {
        Log::info($fd . 'is close.');
        parent::onClose($server, $fd, $reactorId); // TODO: Change the autogenerated stub
    }

    public function onReceive(Server $server, $fd, $reactorId, $data)
    {
        try {
            $protocol = $this->protocol($data);
            $class = "\\App\\Services\\{$protocol['class']}Service";
            if (! class_exists($class)) {
                $server->send($fd, $this->pack([
                    'code' => 404,
                    'msg'  => 'Not Found Class',
                ]));
            }
            if (! method_exists(app($class), $protocol['method'])) {
                $server->send($fd, $this->pack([
                    'code' => 404,
                    'msg'  => 'Not Found Method',
                ]));
            }
            $data = app($class)->{$protocol['method']}(Request::merge($protocol['params']));
            if ($data instanceof JsonResource) {
                $data = $data->response()->getData(true);
            }
            $data['code'] = 200;
            $server->send($fd, $this->pack($data));
        } catch (\Throwable $exception) {
            $message = $exception->getMessage();
            if ($exception instanceof ValidationException) {
                $message = collect($exception->errors())->collapse()->implode('; ');
            }
            Log::error($message, [
                'code' => $exception->getCode(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString()
            ]);
            $server->send($fd, $this->pack([
                'message' => $message,
                'code'    => $exception->getCode(),
            ]));
        }
    }

    protected function protocol($data)
    {
        $protocol = $this->unpack($data);
        $protocol['class'] = Str::studly($protocol['class']);
        return $protocol;
    }

    protected function pack($data)
    {
        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    protected function unpack($data)
    {
        return json_decode($data, true);
    }
}
