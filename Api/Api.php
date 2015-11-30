<?php

namespace Adanfm\BeOnPopApiBundle\Api;

use Symfony\Component\DependencyInjection\ContainerInterface;

class Api
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var int
     */
    private $clientId;

    /**
     * @var string
     */
    private $secret;

    /**
     * @var string
     */
    private $authorization;

    /**
     * @var string
     */
    private $version;

    /**
     * @var string
     */
    private $urlBase = 'https://beonpop.com/api';

    /**
     * Api constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $configs = $this->container->getParameter('adanfm_be_on_pop_api');

        $this->secret        = $configs['secret'];
        $this->clientId      = $configs['client_id'];
        $this->authorization = $configs['authorization'];

    }

    private function baseUrlApi()
    {
        return $this->urlBase . '/v1/';
    }

    public function api($endpoint,$method = 'GET')
    {
        $response = $this->request($endpoint,$method);
        if ($response->code === 200) {
            if (isset($response->data))
                return $response->data->items;
        }

        throw new \Exception('Verifique a configuração da API!');
    }

    /**
     * @param $url
     * @param $method
     * @return string
     */
    private function request($url,$method)
    {
        ini_set('user_agent', 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322)');
        $curl = curl_init();

        if ($method === 'POST')
            curl_setopt($curl,CURLOPT_POST,true);
        else
            curl_setopt($curl,CURLOPT_HTTPGET,true);

        curl_setopt($curl, CURLOPT_URL, $this->baseUrlApi() . $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_HEADER,[
            'Authorization: Basic '.$this->authorization
        ]);

        curl_setopt($curl,CURLOPT_USERPWD,$this->clientId. ':'.$this->secret);

        $response       = curl_exec($curl);
        $curl_info      = curl_getinfo($curl);
        curl_close($curl);

        $header_size    =   $curl_info['header_size'];
        $header         =   substr($response, 0, $header_size);

        $body           =   substr($response, $header_size);

        return json_decode($body);
    }
}