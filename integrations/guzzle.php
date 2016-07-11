<?php

use Illuminated\Console\RuntimeException;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LoggerInterface;

if (!function_exists('iclogger_guzzle_middleware')) {
    function iclogger_guzzle_middleware(LoggerInterface $log, $type = 'raw')
    {
        return function (callable $handler) use ($log, $type) {
            return function (RequestInterface $request, array $options) use ($handler, $log, $type) {
                $method = (string) $request->getMethod();
                $uri = (string) $request->getUri();
                $body = (string) $request->getBody();

                if (empty($body)) {
                    $message = "[{$method}] Calling `{$uri}`.";
                    $context = [];
                } else {
                    $message = "[{$method}] Calling `{$uri}` with body:";
                    switch ($type) {
                        case 'json':
                            $context = json_decode($body, true);
                            break;

                        case 'raw':
                        default:
                            $message .= "\n{$body}";
                            $context = [];
                            break;
                    }
                }
                $log->info($message, $context);

                return $handler($request, $options)->then(
                    function ($response) use ($log, $type) {
                        $body = (string) $response->getBody();
                        $code = $response->getStatusCode();

                        $message = "[{$code}] Response:";
                        switch ($type) {
                            case 'json':
                                $context = is_json($body, true);
                                if (empty($context)) {
                                    throw new RuntimeException('Bad response, json expected.', ['response' => $body]);
                                }
                                break;

                            case 'raw':
                            default:
                                $message .= "\n{$body}";
                                $context = [];
                                break;
                        }
                        if (!empty($context)) {
                            $response->iclParsedBody = $context;
                        }

                        $log->info($message, $context);

                        return $response;
                    },
                    function ($reason) {
                        return \GuzzleHttp\Promise\rejection_for($reason);
                    }
                );
            };
        };
    }
}
