<?php

namespace Xuchunyang\Ydcv;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\RetryableHttpClient;

class Ydcv
{
    public static function query($word): void
    {
        $client = new RetryableHttpClient(HttpClient::create());
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

        if ($response->getStatusCode() !== 200) {
            fprintf(STDERR, "HTTP Status Code: %d" . PHP_EOL, $response->getStatusCode());
            fprintf(STDERR, "Headers:" . PHP_EOL);
            fwrite(STDERR, print_r($response->getHeaders(), return: true) . PHP_EOL);
            if ($response->getContent() !== '') {
                fprintf(STDERR, "Body:" . PHP_EOL);
                fwrite(STDERR, $response->getContent() . PHP_EOL);
            }

            exit(1);
        }

        $data = $response->toArray();

        $colors = [
            'reset' => "\u{001b}[0m",
            'blue' => "\u{001b}[34m",
            'bold' => "\u{001b}[1m",
        ];

        if (array_key_exists('basic', $data)) {
            if (array_key_exists('phonetic', $data['basic'])) {
                printf("%s [%s]" . PHP_EOL, $colors['bold'] . $data['query'] . $colors['reset'], $data['basic']['phonetic']);
            } else {
                printf("%s" . PHP_EOL, $colors['bold'] . $data['query'] . $colors['reset']);
            }
            echo PHP_EOL;

            printf($colors['blue'] . $colors['bold'] . "* Basic Explains" . $colors['reset'] . PHP_EOL);
            foreach ($data['basic']['explains'] as $explain) {
                printf('- %s' . PHP_EOL, $explain);
            }
        } else {
            printf("%s" . PHP_EOL, $colors['bold'] . $data['query'] . $colors['reset']);
        }
        echo PHP_EOL;

        if (array_key_exists('web', $data)) {

            printf($colors['blue'] . $colors['bold'] . "* Web References" . $colors['reset'] . PHP_EOL);
            foreach ($data['web'] as $web) {
                printf("- %s %s::%s %s" . PHP_EOL, $colors['bold'], $web['key'], $colors['reset'], implode('; ', $web['value']));
            }
            echo PHP_EOL;
        }
    }
}

