<?php

namespace Xuchunyang\Ydcv;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\RetryableHttpClient;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class Ydcv
{
    public static function query(string $word): array
    {
        // TODO PHP 里究竟怎么使用 Exception 还是不懂，找机会看看 Modern PHP
        if (!$word) {
            throw new \Exception("Word cannot be empty");
        }

        $client = new RetryableHttpClient(HttpClient::create());
        try {
            $response = $client->request(
                method: 'GET',
                url: 'http://fanyi.youdao.com/openapi.do',
                options: [
                    'timeout' => 2.0,
                    'query' => [
                        'keyfrom' => 'YouDaoCV',
                        'key' => '659600698',
                        'type' => 'data',
                        'doctype' => 'json',
                        'version' => '1.1',
                        'q' => $word,
                    ],
                ],
            );
        } catch (TransportExceptionInterface $e) {
            throw new \Exception($e->getMessage());
        }

        if ($response->getStatusCode() !== 200) {
            throw new \Exception('HTTP is not ok, the status code is ' . $response->getStatusCode());
        }

        $data = $response->toArray();

        $result = [
            'query' => $data['query'],
            'phonetic' => null,
            'explains' => null,
            'webs' => null,
        ];

        if (array_key_exists('basic', $data)) {
            if (array_key_exists('phonetic', $data['basic'])) {
                $result['phonetic'] = $data['basic']['phonetic'];
            }

            if (array_key_exists('explains', $data['basic'])) {
                $result['explains'] = $data['basic']['explains'];
            }
        }

        if (array_key_exists('web', $data)) {
            $result['webs'] = array_map(function ($web) {
                return [
                    'term' => $web['key'],
                    'definitions' => $web['value'],
                ];
            }, $data['web']);
        }

        return $result;
    }

    public static function print($word): void
    {
        $result = self::query($word);

        $colors = [
            'reset' => "\u{001b}[0m",
            'blue' => "\u{001b}[34m",
            'bold' => "\u{001b}[1m",
        ];

        if ($result['phonetic']) {
            printf("%s [%s]\n\n", $result['query'], $result['phonetic']);
        } else {
            printf("%s\n\n", $result['query']);
        }

        if ($result['explains']) {
            printf("%s\n", $colors['blue'] . $colors['bold'] . "* Basic Explains" . $colors['reset']);
            foreach ($result['explains'] as $explain) {
                printf("- %s\n", $explain);
            }
            printf("\n");
        }

        if ($result['webs']) {
            printf("%s\n", $colors['blue'] . $colors['bold'] . "* Web References" . $colors['reset']);
            foreach ($result['webs'] as $web) {
                printf("- %s%s\n",
                    $colors['bold'] . $web['term'] . ' :: ' . $colors['reset'],
                    implode('; ', $web['definitions']));
            }
            printf("\n");
        }
    }
}

