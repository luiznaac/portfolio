<?php

namespace Tests\Model\Crawler;

use Goutte\Client;
use Tests\TestCase;

class InvestingDotComCrawlerTest extends TestCase
{
    public function testBasicTest()
    {
        $url = "https://www.investing.com/etfs/fundo-de-invest-ishares-sp-500-historical-data";
        $client = new Client();
        $crawler = $client->request('GET', $url);

        $elements = $crawler->filter('.historicalTbl')->each(function($node){
            return $node->filter('tr')->each(function($node){
                $daily_stock[] = $node->filter('td')->each(function($node){
                    return $node->text();
                });

                return $daily_stock;
            });
        });

        dd($elements);
    }
}
