<?

require_once(__DIR__."/../src/Tvst/Client.php");

$client = new Tvst\Client(null, null);

// $res = $client->getAccessToken('');
$client->authenticateUser(null);

$res = $client->getAuthenticatedUser();
print_r($res->json());

$res = $client->getEpisodeByTvdbId('4063481');
print_r($res->json());

$res = $client->setWatchedByTvdbId('4063481');
print_r($res->json());

?>