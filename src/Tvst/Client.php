<?php
/**
 * Copyright 2010-2013 tvshowtime.com or its affiliates. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License").
 * You may not use this file except in compliance with the License.
 * A copy of the License is located at
 *
 * http://aws.amazon.com/apache2.0
 *
 * or in the "license" file accompanying this file. This file is distributed
 * on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either
 * express or implied. See the License for the specific language governing
 * permissions and limitations under the License.
 */

namespace Tvst;

require __DIR__ . "/../../vendor/autoload.php";

class Client extends \GuzzleHttp\Client
{
    static $defaultAgent = 'tvst-php-sdk/v1';

    protected $clientId = null;
    protected $clientSecret = null;

    protected $accessToken = null;

    public function __construct($clientId = null, $clientSecret = null)
    {
        if (is_null($clientId) || is_null($clientSecret)) {
            throw new Exception("You must provide a client id and a client secret for using TVShow Time's API.
                                 See https://api.tvshowtime.com/doc.", 1);
        }

        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;

        $config = ['base_url' => 'https://api.tvshowtime.com/v1'];
        parent::__construct($config);

        return $this;
    }

    public function getAccessToken($code = null) {
        if (is_null($this->clientId) || is_null($this->clientSecret)) {
            throw new \Exception("'code' cannot be null.", 1);
        }

        $res = $this->post("/oauth/access_token", [
                                'body' => [
                                    'client_id' => $this->clientId,
                                    'client_secret' => $this->clientSecret,
                                    'code' => $code
                                ]
                            ]);

        $json = $res->json();
        if (!isset($json['access_token'])) {
            throw new \Exception("Could not create access token", 1);
        }

        $this->authenticateUser($res->json()['access_token']);

        return $res;
    }

    public function authenticateUser($accessToken = null) {
        if (is_null($accessToken)) {
            throw new \Exception("Access Token cannot be null", 1);
        }

        $this->setDefaultOption('headers', ['Authorization' => "token " . $accessToken]);
        $this->accessToken = $accessToken;
    }

    public function isAuthenticated() {
        return (!is_null($this->accessToken));
    }

    public function getAuthenticatedUser() {
        if (!$this->isAuthenticated()) {
            throw new \Exception("You need to authentify the user first", 1);
        }

        $res = $this->get('/user');

        return $res;
    }

    public function getEpisodeByFilename($filename = null) {
        if (is_null($filename)) {
            throw new \Exception("filename cannot be null", 1);
        }

        return $this->getEpisode(['filename' => $filename]);
    }

    public function getEpisodeByTvdbId($tvdbId = null) {
        if (is_null($tvdbId)) {
            throw new \Exception("tvdbId cannot be null", 1);
        }

        return $this->getEpisode(['episode_id' => $tvdbId]);
    }


    public function getEpisodeByImdbId($imdbId = null) {
        if (is_null($imdbId)) {
            throw new \Exception("imdbId cannot be null", 1);
        }

        return $this->getEpisode(['imdb_id' => $imdbId]);
    }


    private function getEpisode(array $file = null) {
        $params = [];
        $params['query'] = $file;

        $res = $this->get('/episode', $params);

        return $res;
    }

    public function setWatchedByFilename($filename = null, $tickerOn = false, $twitterOn = false) {

        if (is_null($filename)) {
            throw new \Exception("filename cannot be null", 1);
        }

        return $this->setWatched(['filename' => $filename], $tickerOn, $twitterOn);
    }

    public function setWatchedByTvdbId($tvdbId = null, $tickerOn = false, $twitterOn = false) {

        if (is_null($tvdbId)) {
            throw new \Exception("tvdbId cannot be null", 1);
        }

        return $this->setWatched(['episode_id' => $tvdbId], $tickerOn, $twitterOn);
    }

    public function setWatchedByImdbId($imdbId = null, $tickerOn = false, $twitterOn = false) {

        if (is_null($imdbId)) {
            throw new \Exception("imdbId cannot be null", 1);
        }

        return $this->setWatched(['imdb_id' => $imdbId], $tickerOn, $twitterOn);
    }

    private function setWatched(array $episode, $tickerOn = false, $twitterOn = false) {

        $params['body'] = $episode;
        $params['body']['publish_on_ticker'] = $tickerOn;
        $params['body']['publish_on_twitter'] = $twitterOn;

        $res = $this->post('/checkin', $params);

        return $res;
    }



    public function setUnwatchedByFilename($filename = null) {

        if (is_null($filename)) {
            throw new \Exception("filename cannot be null", 1);
        }

        return $this->setUnwatched(['filename' => $filename]);
    }

    public function setUnwatchedByTvdbId($tvdbId = null) {

        if (is_null($tvdbId)) {
            throw new \Exception("tvdbId cannot be null", 1);
        }

        return $this->setUnwatched(['episode_id' => $tvdbId]);
    }

    public function setUnwatchedByImdbId($imdbId = null) {

        if (is_null($imdbId)) {
            throw new \Exception("imdbId cannot be null", 1);
        }

        return $this->setUnwatched(['imdb_id' => $imdbId]);
    }

    private function setUnwatched(array $episode) {

        $params['body'] = $episode;

        $res = $this->delete('/checkin', $params);

        return $res;
    }

    public function isWatchedByFilename($filename = null) {
        if (is_null($filename)) {
            throw new \Exception("filename cannot be null", 1);
        }

        return $this->get('/checkin', ['query' => ['filename' => $filename]]);
    }

    public function isWatchedByTvdbId($tvdbId = null) {
        if (is_null($tvdbId)) {
            throw new \Exception("tvdbId cannot be null", 1);
        }

        return $this->get('/checkin', ['query' => ['episode_id' => $tvdbId]]);
    }

    public function isWatchedByImdbId($imdbId = null) {
        if (is_null($imdbId)) {
            throw new \Exception("tvdbId cannot be null", 1);
        }

        return $this->get('/checkin', ['query' => ['imdb_id' => $imdbId]]);
    }
}
