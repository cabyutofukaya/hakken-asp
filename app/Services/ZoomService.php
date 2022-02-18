<?php

namespace App\Services;

use GuzzleHttp\Client;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Builder;
use App\Repositories\ZoomApiKey\ZoomApiKeyRepository;

class ZoomService
{
    private const BASE_URI = 'https://api.zoom.us/v2/';

    private $api_key;
    private $api_secret;

    public function __construct(
        ZoomApiKeyRepository $zoomApiKeyRepository
    ) {
        $this->zoomApiKeyRepository = $zoomApiKeyRepository;
    }

    // APIキー情報をランダムに取得
    public function getRandomApiInfo()
    {
        return $this->zoomApiKeyRepository->findRandom();
    }

    private function createJwtToken()
    {
        $signer = new Sha256;
        $key = new Key($this->api_secret);
        $time = time();
        $jwt_token = (new Builder())->setIssuer($this->api_key)
          ->expiresAt($time + 3600)
          ->sign($signer, $key)
          ->getToken();
        return $jwt_token;
    }

    private function fetchUserId()
    {
        $method = 'GET';
        $path = 'users';
        $client_params = [
          'base_uri' => self::BASE_URI,
        ];
        $result = $this->sendRequest($method, $path, $client_params);
        $user_id = $result['users'][0]['id'];
        return $user_id;
    }

    /**
     * ミーティング作成
     *
     * @param string $startTime 開始時間
     */
    public function createMeeting(string $api_key, string $api_secret, string $start_time)
    {
        $this->api_key = $api_key;
        $this->api_secret = $api_secret;

        $user_id = $this->fetchUserId();
        $params = [
          'topic' => '',
          'type' => 2, // 開始時間の指定
          'time_zone' => 'Asia/Tokyo',
          'start_time' => $start_time,
          'duration' => 60, // ミーティング時間
          'agenda' => 'HAKKENミーティング',
          'settings' => [
            'host_video' => true,
            'participant_video' => true,
            'approval_type' => 0,
            'audio' => 'both',
            'enforce_login' => false,
            'waiting_room' => false,
          ]
        ];
        $method = 'POST';
        $path = 'users/'. $user_id .'/meetings';
        $client_params = [
          'base_uri' => self::BASE_URI,
          'json' => $params
        ];
        $result = $this->sendRequest($method, $path, $client_params);
        return $result;
    }

    /**
     * ミーティング削除
     *
     * @param int $meeting_id ミーティングID
     */
    public function deleteMeeting(string $api_key, string $api_secret, $meeting_id)
    {
        $this->api_key = $api_key;
        $this->api_secret = $api_secret;

        $client = new Client([
          'base_uri' => self::BASE_URI,
        ]);

        $response = $client->request(
            'DELETE',
            'meetings/' . $meeting_id,
            [
              'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->createJwtToken(),
                ]
            ]
        );

        return 204 == $response->getStatusCode();
    }

    private function sendRequest($method, $path, $client_params)
    {
        $client = new Client($client_params);
        $jwt_token = $this->createJwtToken();
        $response = $client->request(
            $method,
            $path,
            [
              'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $jwt_token,
                ]
            ]
        );
        $result_json = $response->getBody()->getContents();
        $result = json_decode($result_json, true);
        return $result;
    }
}
